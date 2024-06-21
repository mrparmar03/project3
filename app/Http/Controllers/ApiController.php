<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use App\Mail\myTestmail;

class ApiController extends Controller
{
    // register api POST[name,password,email,image,mobile]
    public function Create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4|confirmed',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'mobile' => 'required|min:10'
        ]);
        $imagePath = $request->file('image');
        if ($imagePath) {
            $imageName = now()->timestamp . '.' . $imagePath->getClientOriginalExtension();
            $destinationPath = 'uploads/image/users/';


            $imagePath->move(public_path($destinationPath), $imageName);
        } else {
            return response()->json(['error' => 'Image not found in the request.']);
        }

       $user= User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'image' => $imageName,
            'mobile' => $request->mobile
        ]);
        Mail::to($user->email)->send(new MyTestMail($user->name));

        
        return response()->json([
            'status' => 'true',
            'message' => 'User Registered successfully.',
            'data' => []
        ]);
    }

    // login  POST[email,password]
    public function Login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!empty($user)) {
            if (Hash::check($request->password, $user->password)) {

                $token = $user->createToken("mytoken")->plainTextToken;

                return response()->json([
                    'status' => 'true',
                    'message' => 'Login successfully',
                    'token' => $token,
                    'data' => []
                ]);
            } else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Please enter valid password',
                    'data' => []
                ]);
            }
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Please enter email and password',
                'data' => []
            ]);
        }
    }

    public function fatch_all()
    {
        $users = User::all();

        return response()->json([
            'status' => 'true',
            'message' => 'Users data',
            'data' => $users
        ]);
    }
    //profile(fatch) GET[name, email,password,mobile,image]
    public function Profile()
    {
        $userdata = auth()->user();

        return response()->json([
            'status' => 'true',
            'message' => 'user profile',
            'data' => $userdata,
            'id' => auth()->user()->id
        ]);
    }

    //Update PUT[name,password,email,image,mobile]
    public function Update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4|confirmed',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'mobile' => 'required|min:10'
        ]);
        $imagePath = $request->file('image');
        if ($imagePath) {
            $imageName = now()->timestamp . '.' . $imagePath->getClientOriginalExtension();
            $destinationPath = 'uploads/image/users/';


            $imagePath->move(public_path($destinationPath), $imageName);
        } else {
            return response()->json(['error' => 'Image not found in the request.']);
        }

        User::where('id', $id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'image' => $imageName,
            'mobile' => $request->mobile
        ]);

        return response()->json([
            'status' => 'true',
            'message' => 'User Update successfully.',
            'data' => []
        ]);
    }
    //Delete delete
    public function Delete($id)
    {
        user::where('id',$id)->delete();
        return response()->json([
            'status' => 'true',
            'message' => 'User Delete successfully.',
            'data' => []
        ]);
    }
    // logout 
    public function logout()
    {
        $user = auth()->user();
        if ($user) {
            $user->tokens()->delete();
        }
        return response()->json(['message' => 'Logged out successfully'], 200);
    }


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
            'message' => 'User Registered successfully.'
        ]);
    }
}
