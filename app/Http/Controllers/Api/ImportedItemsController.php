<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImportItem;
use Illuminate\Http\Request;

class ImportedItemsController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date');
        $perPage = 100;

        $query = ImportItem::query();

        if ($date) {
            $query->whereDate('date', $date);
        }

        $items = $query->orderBy('date')
            ->orderBy('id')
            ->paginate($perPage);

        // Группируем записи по дате в результирующем json
        $grouped = $items->groupBy(fn($item) => $item->date);

        $response = $grouped->map(fn($items, $date) => [
            'date' => $date,
            'items' => $items->values(),
        ])->values();

        return response()->json([
            'data' => $response,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }
}
