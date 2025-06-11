<?php

use App\Import\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/import', [ImportController::class, 'showForm'])->name('import.form');
Route::post('/import', [ImportController::class, 'handleUpload'])->name('import.upload');

