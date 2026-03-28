<?php

namespace App\Application\Http\Controllers\Api\V1\Warehouse;

use App\Application\Http\Controllers\Controller;
use App\Domain\Warehouse\Models\Supply;
use App\Domain\Warehouse\Models\Stock;
use App\Domain\Warehouse\Models\StockBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $supplies = Supply::whereHas('warehouse', fn($q) => $q->where('branch_id', $branchId))
            ->when($request->has('status'), fn($q) => $q->where('status', $request->input('status')))
            ->with(['supplier', 'warehouse'])
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json(['data' => $supplies]);
    }

    public function show(Supply $supply): JsonResponse
    {
        $supply->load(['supplier', 'warehouse', 'items.ingredient']);

        return response()->json(['data' => $supply]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        $supply = DB::transaction(function () use ($request) {
            $supply = Supply::create([
                'warehouse_id' => $request->input('warehouse_id'),
                'supplier_id' => $request->input('supplier_id'),
                'user_id' => $request->user()->id,
                'number' => 'SUP-' . now()->format('YmdHis'),
                'status' => 'draft',
                'notes' => $request->input('notes'),
            ]);

            foreach ($request->input('items') as $item) {
                $supply->items()->create([
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);
            }

            $supply->calculateTotal();

            return $supply;
        });

        return response()->json(['message' => 'Поставка создана.', 'data' => $supply->load('items')], 201);
    }

    public function receive(Supply $supply): JsonResponse
    {
        if ($supply->status !== 'draft') {
            return response()->json(['message' => 'Поставка уже принята.'], 422);
        }

        DB::transaction(function () use ($supply) {
            foreach ($supply->items as $item) {
                // Find or create stock record
                $stock = Stock::firstOrCreate([
                    'organization_id' => $supply->warehouse->organization_id ?? $request->user()->organization_id,
                    'branch_id' => $supply->warehouse->branch_id,
                    'warehouse_id' => $supply->warehouse_id,
                    'ingredient_id' => $item->ingredient_id,
                ], ['quantity' => 0]);

                // Create batch for FIFO
                StockBatch::create([
                    'stock_id' => $stock->id,
                    'supply_item_id' => $item->id,
                    'quantity' => $item->quantity,
                    'remaining_quantity' => $item->quantity,
                    'cost_price' => $item->unit_price,
                    'expiry_date' => $item->expiry_date,
                ]);

                // Update stock quantity
                $stock->increment('quantity', $item->quantity);

                // Create movement record
                $stock->movements()->create([
                    'type' => 'supply',
                    'quantity' => $item->quantity,
                    'cost_price' => $item->unit_price,
                    'reference_type' => Supply::class,
                    'reference_id' => $supply->id,
                ]);
            }

            $supply->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Поставка принята.', 'data' => $supply->fresh()]);
    }

    public function destroy(Supply $supply): JsonResponse
    {
        if ($supply->status !== 'draft') {
            return response()->json(['message' => 'Нельзя удалить принятую поставку.'], 422);
        }

        $supply->items()->delete();
        $supply->delete();

        return response()->json(['message' => 'Поставка удалена.']);
    }
}
