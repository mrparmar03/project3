<?php

use Illuminate\Support\Facades\Route;
use App\Mail\myTestmail;
use Illuminate\Support\Facades\Mail;

Route::get('/mail-test', function () {
    $name = "rohan";

    // The email sending is done using the to method on the Mail facade
    Mail::to('rohanparmar0302@gmail.com')->send(new myTestmail($name));
});

Route::get('/home', function () {
    return view('welcome');
});
    