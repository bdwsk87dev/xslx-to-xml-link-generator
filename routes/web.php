<?php

use App\Http\Controllers\XmlFileController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;

ini_set('upload_max_filesize', '100M');

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('welcome');

Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        return Inertia::render('home');
    })->name('home');

    Route::post('/api/upload', [XmlFileController::class, 'upload'])->name('xml.upload');
});

Route::middleware(['guest'])->group(function () {

    Route::get('/login', function () {
        return Inertia::render('login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', function () {
        return Inertia::render('register');
    })->name('register');

    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/list', [XmlFileController::class, 'index'])->name('xml.list')->middleware('web');

Route::get('/api/show/{id}', [XmlFileController::class, 'show'])->name('xml.show')->middleware('api');

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

