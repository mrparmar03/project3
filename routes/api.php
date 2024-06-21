<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Mail\myTestmail;
use Illuminate\Support\Facades\Mail;
//open routes 
Route::post('create',[ApiController::class,'Create']);
Route::post('login',[ApiController::class,'Login']);
Route::post('update/{id}',[ApiController::class,'Update']);
Route::get('fatch_all',[ApiController::class,'fatch_all']);
Route::post('student',[ApiController::class,'student']);
Route::get('delete/{id}',[ApiController::class,'Delete']);
//protected routes
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
