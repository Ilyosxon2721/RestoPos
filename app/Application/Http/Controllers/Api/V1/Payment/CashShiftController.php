<?php

namespace App\Application\Http\Controllers\Api\V1\Payment;

use App\Application\Http\Controllers\Controller;
use App\Domain\Payment\Models\CashShift;
use App\Support\Enums\CashShiftStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashShiftController extends Controller
{
    /**
     * List cash shifts.
     */
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $query = CashShift::query()
            ->where('branch_id', $branchId)
            ->when($request->boolean('today'), fn ($q) => $q->today())
            ->when($request->has('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->with(['openedByUser', 'closedByUser', 'terminal'])
            ->latest('opened_at');

        $shifts = $request->boolean('paginate', true)
            ? $query->paginate($request->input('per_page', 20))
            : $query->get();

        return response()->json([
            'data' => $shifts,
        ]);
    }

    /**
     * Get current open shift.
     */
    public function current(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $shift = CashShift::getCurrentForBranch($branchId);

        if (!$shift) {
            return response()->json([
                'message' => 'Нет открытой смены.',
                'data' => null,
            ]);
        }

        $shift->load(['openedByUser', 'terminal', 'cashOperations']);

        return response()->json([
            'data' => $shift,
        ]);
    }

    /**
     * Get single shift with details.
     */
    public function show(CashShift $cashShift): JsonResponse
    {
        $cashShift->load(['openedByUser', 'closedByUser', 'terminal', 'cashOperations', 'orders']);

        // Calculate stats
        $cashShift->calculateTotals();

        return response()->json([
            'data' => $cashShift,
        ]);
    }

    /**
     * Open new cash shift.
     */
    public function open(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'terminal_id' => 'nullable|exists:terminals,id',
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $branchId = $request->input('branch_id');

        // Check if there's already an open shift
        if (CashShift::getCurrentForBranch($branchId)) {
            return response()->json([
                'message' => 'Уже есть открытая смена для этого филиала.',
            ], 422);
        }

        $shift = CashShift::create([
            'branch_id' => $branchId,
            'terminal_id' => $request->input('terminal_id'),
            'opened_by' => $request->user()->id,
            'status' => CashShiftStatus::OPEN,
            'opened_at' => now(),
            'opening_cash' => $request->input('opening_cash'),
        ]);

        $shift->load(['openedByUser', 'terminal']);

        return response()->json([
            'message' => 'Смена открыта.',
            'data' => $shift,
        ], 201);
    }

    /**
     * Close cash shift.
     */
    public function close(Request $request, CashShift $cashShift): JsonResponse
    {
        $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if (!$cashShift->isOpen()) {
            return response()->json([
                'message' => 'Смена уже закрыта.',
            ], 422);
        }

        // Check for open orders
        $openOrders = $cashShift->orders()->open()->count();
        if ($openOrders > 0) {
            return response()->json([
                'message' => "Есть открытые заказы ({$openOrders}). Закройте их перед закрытием смены.",
            ], 422);
        }

        $cashShift->close(
            $request->input('actual_cash'),
            $request->user()->id,
            $request->input('notes')
        );

        return response()->json([
            'message' => 'Смена закрыта.',
            'data' => $cashShift->fresh(['openedByUser', 'closedByUser']),
        ]);
    }

    /**
     * Add cash operation (cash in/out).
     */
    public function addCashOperation(Request $request, CashShift $cashShift): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (!$cashShift->isOpen()) {
            return response()->json([
                'message' => 'Смена закрыта.',
            ], 422);
        }

        $operation = $cashShift->cashOperations()->create([
            'user_id' => $request->user()->id,
            'type' => $request->input('type'),
            'amount' => $request->input('amount'),
            'reason' => $request->input('reason'),
            'notes' => $request->input('notes'),
        ]);

        return response()->json([
            'message' => $request->input('type') === 'in' ? 'Внесение добавлено.' : 'Изъятие добавлено.',
            'data' => $operation,
        ]);
    }

    /**
     * Get shift report.
     */
    public function report(CashShift $cashShift): JsonResponse
    {
        $cashShift->calculateTotals();

        $report = [
            'shift' => [
                'opened_at' => $cashShift->opened_at,
                'closed_at' => $cashShift->closed_at,
                'cashier' => $cashShift->openedByUser->full_name,
                'closed_by' => $cashShift->closedByUser?->full_name,
            ],
            'summary' => [
                'orders_count' => $cashShift->total_orders,
                'total_sales' => $cashShift->total_sales,
                'total_refunds' => $cashShift->total_refunds,
                'net_sales' => $cashShift->total_sales - $cashShift->total_refunds,
            ],
            'payments' => [
                'cash' => $cashShift->total_cash_payments,
                'card' => $cashShift->total_card_payments,
            ],
            'cash_drawer' => [
                'opening_cash' => $cashShift->opening_cash,
                'expected_cash' => $cashShift->expected_cash,
                'closing_cash' => $cashShift->closing_cash,
                'cash_difference' => $cashShift->cash_difference,
            ],
            'operations' => $cashShift->cashOperations->map(fn ($op) => [
                'type' => $op->type,
                'amount' => $op->amount,
                'reason' => $op->reason,
                'created_at' => $op->created_at,
            ]),
        ];

        return response()->json([
            'data' => $report,
        ]);
    }
}
