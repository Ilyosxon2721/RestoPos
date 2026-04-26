<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Payment\Models\Payment;
use App\Support\Enums\OrderStatus;
use App\Support\Traits\ResolvesLayout;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    use ResolvesLayout;

    public string $period = 'today';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->applyPeriod();
    }

    public function updatedPeriod(): void
    {
        $this->applyPeriod();
    }

    private function applyPeriod(): void
    {
        $now = Carbon::now();

        match ($this->period) {
            'today' => $this->setDates($now->copy()->startOfDay(), $now->copy()->endOfDay()),
            'week' => $this->setDates($now->copy()->startOfWeek(), $now->copy()->endOfDay()),
            'month' => $this->setDates($now->copy()->startOfMonth(), $now->copy()->endOfDay()),
            'custom' => null,
        };
    }

    private function setDates(Carbon $from, Carbon $to): void
    {
        $this->dateFrom = $from->toDateString();
        $this->dateTo = $to->toDateString();
    }

    public function applyCustomDates(): void
    {
        $this->period = 'custom';
    }

    #[Computed]
    public function salesData(): array
    {
        $query = Order::query()
            ->where('status', OrderStatus::COMPLETED)
            ->whereBetween('created_at', [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59']);

        $totalSales = (float) $query->sum('total_amount');
        $orderCount = (int) $query->count();
        $avgCheck = $orderCount > 0 ? round($totalSales / $orderCount, 2) : 0;

        return [
            'total_sales' => $totalSales,
            'order_count' => $orderCount,
            'avg_check' => $avgCheck,
        ];
    }

    #[Computed]
    public function topProducts(): Collection
    {
        return OrderItem::query()
            ->selectRaw('name, SUM(quantity) as total_quantity, SUM(quantity * unit_price) as total_revenue')
            ->whereHas('order', function ($query) {
                $query->where('status', OrderStatus::COMPLETED)
                    ->whereBetween('created_at', [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59']);
            })
            ->groupBy('name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function revenueByPaymentMethod(): Collection
    {
        return Payment::query()
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->where('payments.status', 'completed')
            ->whereBetween('payments.paid_at', [$this->dateFrom, $this->dateTo])
            ->selectRaw('payment_methods.name as method_name, payment_methods.type as method_type, SUM(payments.amount) as total_amount, COUNT(*) as payment_count')
            ->groupBy('payment_methods.name', 'payment_methods.type')
            ->orderByDesc('total_amount')
            ->get();
    }

    #[Computed]
    public function ordersByHour(): Collection
    {
        return Order::query()
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as order_count')
            ->where('status', OrderStatus::COMPLETED)
            ->whereBetween('created_at', [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59'])
            ->groupByRaw('HOUR(created_at)')
            ->orderBy('hour')
            ->get();
    }

    public function render()
    {
        return view('livewire.reports.dashboard')
            ->layout($this->resolveLayout());
    }
}
