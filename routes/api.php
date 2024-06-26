<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\api\SocialLoginController;
use App\Mail\myTestmail;
use Illuminate\Support\Facades\Mail;
//open routes 
Route::post('create',[ApiController::class,'Create']);
Route::post('login',[ApiController::class,'Login']);
Route::post('update/{id}',[ApiController::class,'Update']);
Route::get('fatch_all',[ApiController::class,'fatch_all']);
Route::post('student',[ApiController::class,'student']);
Route::get('delete/{id}',[ApiController::class,'Delete']);
 Route::get('social-login',[SocialLoginController::class],'login');
  
 Route::get('auth/google/redirect', [ApiController::class, 'redirect']);
Route::get('auth/google/callback', [ApiController::class, 'callback']);
 
 
Route::get('auth/facebook/redirect', [ApiController::class, 'redirectfacebook']);
Route::get('auth/facebook/callback', [ApiController::class, 'callbackfacebook']);
 

Route::group([
    "middleware"=>["auth:sanctum"]   
], function(){
    Route::get('profile',[ApiController::class,'Profile']);
    Route::get('logout',[ApiController::class,'Logout']);
   
});

Route::get('/mail-test', function () {
    $name = "rohan";

    // The email sending is done using the to method on the Mail facade
    Mail::to('rohanparmar0302@gmail.com')->send(new myTestmail($name));
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
