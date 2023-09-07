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

    Route::post('/delete/{id}', [XmlFileController::class, 'delete'])->name('xml.delete');

    Route::get('/home', function () {
        return Inertia::render('home');
    })->name('home');

    Route::post('/api/upload', [XmlFileController::class, 'upload'])->name('xml.upload');

    Route::post('/api/edit', [XmlFileController::class, 'edit'])->name('xml.edit');

    Route::get('/api/show/{id}', [XmlFileController::class, 'show'])->name('xml.show');

    Route::get('/list', [XmlFileController::class, 'index'])->name('xml.list');


    Route::get('/get-completion-percentage', [XmlFileController::class, 'getCompletionPercentage']);
});

Route::middleware(['guest'])->group(function () {

    Route::get('/login', function () {
        return Inertia::render('login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login']);

//    Route::get('/register', function () {
//        return Inertia::render('register');
//    })->name('register');

    //Route::post('/register', [AuthController::class, 'register']);
});

//Route::get('/list', [XmlFileController::class, 'index'])->name('xml.list')->middleware('web');


Route::get('/downloadApk', function () {

    $filePath = storage_path('app/public/app-prod-debug_5.142_shopping.apk');

    // Проверяем, существует ли файл
    if (!Storage::disk('public')->exists('app-prod-debug_5.142_shopping.apk')) {
        abort(404);
    }

    // Возвращаем файл для скачивания
    return Response::download($filePath, 'app-prod-debug_5.142_shopping.apk');

})->name('xml.download')->middleware('web');

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');



