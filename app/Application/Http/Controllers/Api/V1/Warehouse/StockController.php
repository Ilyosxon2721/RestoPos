<?php

namespace App\Application\Http\Controllers\Api\V1\Warehouse;

use App\Application\Http\Controllers\Controller;
use App\Domain\Warehouse\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $stock = Stock::where('branch_id', $branchId)
            ->when($request->has('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->input('warehouse_id')))
            ->with(['ingredient', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json(['data' => $stock]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $stock = Stock::where('branch_id', $branchId)
            ->lowStock()
            ->with(['ingredient', 'warehouse'])
            ->get();

        return response()->json(['data' => $stock]);
    }

    public function adjust(Request $request): JsonResponse
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'quantity' => 'required|numeric',
            'reason' => 'required|string|max:255',
        ]);

        $stock = Stock::findOrFail($request->input('stock_id'));
        $adjustment = $request->input('quantity') - $stock->quantity;

        $stock->movements()->create([
            'type' => 'inventory',
            'quantity' => $adjustment,
            'notes' => $request->input('reason'),
        ]);

        $stock->update(['quantity' => $request->input('quantity')]);

        return response()->json(['message' => 'Остаток скорректирован.', 'data' => $stock]);
    }
}
