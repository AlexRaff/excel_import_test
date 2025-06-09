<?php

use App\Events\ImportItemProcessed;
use App\Import\Controllers\ImportController;
use App\Import\Events\TestImportEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/import-mock', function () {
    return view('import-mock');
});

Route::post('/import-mock/start', function () {
    // Мокируем отправку прогресса с 0 до 100
    for ($i = 0; $i <= 100; $i += 20) {
        event(new TestImportEvent($i, 100));
        sleep(1); // Для имитации задержки (в реальном приложении будет асинхронно)
    }

    return response()->json(['status' => 'Import simulation started']);
});



Route::get('/import', [ImportController::class, 'showForm'])->name('import.form');
Route::post('/import', [ImportController::class, 'handleUpload'])->name('import.upload');

