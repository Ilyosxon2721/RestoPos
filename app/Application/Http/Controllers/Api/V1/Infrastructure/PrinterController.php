<?php

namespace App\Application\Http\Controllers\Api\V1\Infrastructure;

use App\Application\Http\Controllers\Controller;
use App\Domain\Infrastructure\Models\Printer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $printers = Printer::query()
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $printers]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:receipt,kitchen,report',
            'connection_type' => 'required|in:network,usb',
            'ip_address' => 'required_if:connection_type,network|nullable|ip',
            'port' => 'required_if:connection_type,network|nullable|integer|min:1|max:65535',
            'usb_path' => 'required_if:connection_type,usb|nullable|string',
            'paper_width' => 'nullable|integer|in:58,80',
            'is_default' => 'boolean',
            'print_kitchen_tickets' => 'boolean',
            'print_receipts' => 'boolean',
            'print_reports' => 'boolean',
            'workshop_ids' => 'nullable|array',
            'workshop_ids.*' => 'uuid|exists:workshops,id',
            'is_active' => 'boolean',
        ]);

        $validated['branch_id'] = $request->user()->current_branch_id;

        // If this is set as default, unset other defaults of same type
        if ($request->boolean('is_default')) {
            Printer::where('branch_id', $validated['branch_id'])
                ->where('type', $validated['type'])
                ->update(['is_default' => false]);
        }

        $printer = Printer::create($validated);

        return response()->json(['data' => $printer], 201);
    }

    public function show(Printer $printer): JsonResponse
    {
        return response()->json(['data' => $printer]);
    }

    public function update(Request $request, Printer $printer): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:receipt,kitchen,report',
            'connection_type' => 'sometimes|in:network,usb',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'usb_path' => 'nullable|string',
            'paper_width' => 'nullable|integer|in:58,80',
            'is_default' => 'boolean',
            'print_kitchen_tickets' => 'boolean',
            'print_receipts' => 'boolean',
            'print_reports' => 'boolean',
            'workshop_ids' => 'nullable|array',
            'workshop_ids.*' => 'uuid|exists:workshops,id',
            'is_active' => 'boolean',
        ]);

        // If this is set as default, unset other defaults of same type
        if ($request->boolean('is_default')) {
            Printer::where('branch_id', $printer->branch_id)
                ->where('type', $validated['type'] ?? $printer->type)
                ->where('id', '!=', $printer->id)
                ->update(['is_default' => false]);
        }

        $printer->update($validated);

        return response()->json(['data' => $printer]);
    }

    public function destroy(Printer $printer): JsonResponse
    {
        $printer->delete();

        return response()->json(null, 204);
    }

    public function testConnection(Printer $printer): JsonResponse
    {
        // In real implementation, this would attempt to connect to the printer
        $connectionString = $printer->getConnectionString();

        if ($printer->connection_type === 'network') {
            $socket = @fsockopen($printer->ip_address, $printer->port, $errno, $errstr, 5);

            if ($socket) {
                fclose($socket);

                return response()->json([
                    'success' => true,
                    'message' => 'Соединение успешно установлено',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Ошибка подключения: {$errstr} ({$errno})",
            ], 422);
        }

        // For USB, just check if path exists
        if (file_exists($printer->usb_path)) {
            return response()->json([
                'success' => true,
                'message' => 'Устройство найдено',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Устройство не найдено по указанному пути',
        ], 422);
    }
}
