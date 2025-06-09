<?php

namespace App\Import\Controllers;

use App\Http\Controllers\Controller;
use App\Import\Services\ImportService;
use App\Import\Parsers\XlsxParser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;

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

        $parser = new XlsxParser(Storage::path($path));

        $progressKey = 'import_progress:' . Str::uuid();

        $importService = new ImportService($parser);
        $importService->import($progressKey);

        return response()->json([
            'status' => 'Импорт запущен.',
            'progress_key' => $progressKey,
        ]);
    }
}
