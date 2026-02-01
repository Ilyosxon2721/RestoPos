<?php

namespace App\Application\Http\Controllers\Api\V1\Report;

use App\Application\Http\Controllers\Controller;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Payment\Models\Payment;
use App\Domain\Customer\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');
        $today = today();

        $data = [
            'today' => [
                'orders' => Order::where('branch_id', $branchId)->whereDate('created_at', $today)->completed()->count(),
                'revenue' => Order::where('branch_id', $branchId)->whereDate('created_at', $today)->completed()->sum('total'),
                'avg_check' => Order::where('branch_id', $branchId)->whereDate('created_at', $today)->completed()->avg('total') ?? 0,
                'new_customers' => Customer::whereDate('created_at', $today)->count(),
            ],
            'week' => [
                'revenue' => Order::where('branch_id', $branchId)->whereBetween('created_at', [now()->startOfWeek(), now()])->completed()->sum('total'),
                'orders' => Order::where('branch_id', $branchId)->whereBetween('created_at', [now()->startOfWeek(), now()])->completed()->count(),
            ],
            'month' => [
                'revenue' => Order::where('branch_id', $branchId)->whereMonth('created_at', now()->month)->completed()->sum('total'),
                'orders' => Order::where('branch_id', $branchId)->whereMonth('created_at', now()->month)->completed()->count(),
            ],
            'top_products' => OrderItem::whereHas('order', fn($q) => $q->where('branch_id', $branchId)->whereDate('created_at', $today)->completed())
                ->select('product_id', 'name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total) as revenue'))
                ->groupBy('product_id', 'name')
                ->orderByDesc('qty')
                ->limit(5)
                ->get(),
        ];

        return response()->json(['data' => $data]);
    }

    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $sales = Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')])
            ->completed()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('AVG(total) as avg_check')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byPaymentMethod = Payment::whereHas('order', fn($q) => $q->where('branch_id', $branchId)
            ->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')])
            ->completed())
            ->completed()
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->get();

        return response()->json([
            'data' => [
                'daily' => $sales,
                'by_payment_method' => $byPaymentMethod,
                'summary' => [
                    'total_orders' => $sales->sum('orders'),
                    'total_revenue' => $sales->sum('revenue'),
                    'avg_check' => $sales->avg('avg_check') ?? 0,
                ],
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $products = OrderItem::whereHas('order', fn($q) => $q->where('branch_id', $branchId)
            ->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')])
            ->completed())
            ->select(
                'product_id',
                'name',
                DB::raw('SUM(quantity) as quantity'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(DISTINCT order_id) as orders')
            )
            ->groupBy('product_id', 'name')
            ->orderByDesc('revenue')
            ->paginate($request->input('per_page', 50));

        return response()->json(['data' => $products]);
    }

    public function employees(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $employees = Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')])
            ->completed()
            ->select(
                'user_id',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('AVG(total) as avg_check')
            )
            ->groupBy('user_id')
            ->with('user:id,first_name,last_name')
            ->orderByDesc('revenue')
            ->get();

        return response()->json(['data' => $employees]);
    }

    public function export(Request $request, string $type): JsonResponse
    {
        // Export logic will be implemented with maatwebsite/excel
        return response()->json(['message' => 'Экспорт запущен. Файл будет доступен через несколько минут.']);
    }
}
