<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use App\Mail\MyTestMail;
use Laravel\Socialite\Facades\Socialite;

class ApiController extends Controller
{
    // Register API POST[name,password,email,image,mobile]
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4|confirmed',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'mobile' => 'required|min:10'
        ]);

        $imagePath = $request->file('image');
        $imageName = null;
        if ($imagePath) {
            $imageName = now()->timestamp . '.' . $imagePath->getClientOriginalExtension();
            $destinationPath = 'uploads/image/users/';
            $imagePath->move(public_path($destinationPath), $imageName);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'image' => $imageName,
            'mobile' => $request->mobile
        ]);

        Mail::to($user->email)->send(new MyTestMail($user->name));

        return response()->json([
            'status' => 'true',
            'message' => 'User registered successfully.',
            'data' => []
        ]);
    }

    // Login POST[email,password]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken("mytoken")->plainTextToken;

            return response()->json([
                'status' => 'true',
                'message' => 'Login successful',
                'token' => $token,
                'data' => []
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Invalid email or password',
                'data' => []
            ]);
        }
    }

    // Fetch all users GET
    public function fetch_all()
    {
        $users = User::all();

        return response()->json([
            'status' => 'true',
            'message' => 'Users data',
            'data' => $users
        ]);
    }

    // Profile GET
    public function profile()
    {
        $userdata = auth()->user();

        return response()->json([
            'status' => 'true',
            'message' => 'User profile',
            'data' => $userdata
        ]);
    }

    // Update PUT[name,password,email,image,mobile]
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'false',
                'message' => 'User not found',
                'data' => []
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:4|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'mobile' => 'required|min:10'
        ]);

        $imagePath = $request->file('image');
        $imageName = $user->image;
        if ($imagePath) {
            $imageName = now()->timestamp . '.' . $imagePath->getClientOriginalExtension();
            $destinationPath = 'uploads/image/users/';
            $imagePath->move(public_path($destinationPath), $imageName);
        }

        $user->User::update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'image' => $imageName,
            'mobile' => $request->mobile
        ]);

        return response()->json([
            'status' => 'true',
            'message' => 'User updated successfully.',
            'data' => []
        ]);
    }

    // Delete DELETE
    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'false',
                'message' => 'User not found',
                'data' => []
            ], 404);
        }

        $user->delete();
        return response()->json([
            'status' => 'true',
            'message' => 'User deleted successfully.',
            'data' => []
        ]);
    }

    // Logout
    public function logout()
    {
        $user = auth()->user();
        if ($user) {
            $user->tokens()->delete();
        }
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    // Register Student POST[name,email,password,mobile]
    public function student(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:students',
            'password' => 'required|min:4|confirmed',
            'mobile' => 'required|min:10'
        ]);

        Student::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->mobile
        ]);

        return response()->json([
            'status' => 'true',
            'message' => 'Student registered successfully.'
        ]);
    }

     
    public function redirect()
    {
        $redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $redirectUrl]);
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('provider_id', $googleUser->getId())->first();

            if (!$user) {
                $newUser = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'provider_id' => $googleUser->getId(),
                ]);

                Auth::login($newUser);
                return response()->json(['message' => 'User created and logged in successfully', 'user' => $newUser]);
            } else {
                Auth::login($user);
                return response()->json(['message' => 'User logged in successfully', 'user' => $user]);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Something went wrong: ' . $th->getMessage()], 500);
        }
    }

    public function redirectfacebook()
    {
        $redirectUrl = Socialite::driver('facebook')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $redirectUrl]);
    }

    public function callbackfacebook()
    {
        try {
            $fbUser = Socialite::driver('facebook')->stateless()->user();
            $user = User::where('provider_id', $fbUser->getId())->first();

            if (!$user) {
                $newUser = User::create([
                    'name' => $fbUser->getName(),
                    'email' => $fbUser->getEmail(),
                    'provider_id' => $fbUser->getId(),
                ]);

                Auth::login($newUser);
                return response()->json(['message' => 'User created and logged in successfully', 'user' => $newUser]);
            } else {
                Auth::login($user);
                return response()->json(['message' => 'User logged in successfully', 'user' => $user]);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Something went wrong: ' . $th->getMessage()], 500);
        }
    }
}

