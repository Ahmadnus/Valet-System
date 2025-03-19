<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ahmad;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/ee', function () {
    return view('findAgent');
});
