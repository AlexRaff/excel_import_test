<?php

namespace App\Import\Controllers;

use App\Http\Controllers\Controller;
use App\Import\Jobs\ImportStartJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function showForm()
    {
        return view('import.form');
    }

    public function handleUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ]);

        $path = $request->file('file')->store('imports');
        $absolutePath = Storage::path($path);

        $progressKey = 'import_progress:' . Str::uuid();

        ImportStartJob::dispatch($absolutePath, $progressKey);

        return response()->json([
            'status' => 'Импорт запущен.',
            'progress_key' => $progressKey,
        ]);
    }
}
