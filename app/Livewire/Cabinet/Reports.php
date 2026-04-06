<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Payment\Models\Payment;
use App\Domain\Staff\Models\Employee;
use App\Support\Enums\OrderStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('components.layouts.cabinet')]
class Reports extends Component
{
    // Период: today, week, month
    public string $period = 'today';

    // Активная вкладка: sales, products, staff, payments
    public string $activeTab = 'sales';

    /**
     * Базовый запрос заказов организации с фильтром по периоду.
     */
    private function ordersQuery()
    {
        $query = Order::whereHas('branch', fn($q) => $q->where('organization_id', auth()->user()->organization_id))
            ->where('status', OrderStatus::COMPLETED);

        return match ($this->period) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()]),
            'month' => $query->whereBetween('created_at', [now()->startOfMonth(), now()]),
            default => $query,
        };
    }

    /**
     * Базовый запрос платежей организации с фильтром по периоду.
     */
    private function paymentsQuery()
    {
        $query = Payment::whereHas('order', fn($q) =>
            $q->whereHas('branch', fn($bq) => $bq->where('organization_id', auth()->user()->organization_id))
                ->where('status', OrderStatus::COMPLETED)
        )->where('status', 'completed');

        return match ($this->period) {
            'today' => $query->whereDate('paid_at', today()),
            'week' => $query->whereBetween('paid_at', [now()->startOfWeek(), now()]),
            'month' => $query->whereBetween('paid_at', [now()->startOfMonth(), now()]),
            default => $query,
        };
    }

    // --- Отчёт по продажам ---

    #[Computed]
    public function revenue(): float
    {
        return (float) $this->ordersQuery()->sum('total_amount');
    }

    #[Computed]
    public function ordersCount(): int
    {
        return $this->ordersQuery()->count();
    }

    #[Computed]
    public function averageCheck(): float
    {
        $count = $this->ordersCount;
        return $count > 0 ? $this->revenue / $count : 0;
    }

    #[Computed]
    public function guestsCount(): int
    {
        return (int) $this->ordersQuery()->sum('guests_count');
    }

    // --- Топ продуктов ---

    #[Computed]
    public function topProducts(): Collection
    {
        $orderIds = $this->ordersQuery()->pluck('id');

        return OrderItem::whereIn('order_id', $orderIds)
            ->select(
                'product_id',
                'name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_price) as total_revenue'),
            )
            ->groupBy('product_id', 'name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
    }

    // --- Эффективность сотрудников ---

    #[Computed]
    public function staffPerformance(): Collection
    {
        return $this->ordersQuery()
            ->select(
                'waiter_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as avg_check'),
            )
            ->whereNotNull('waiter_id')
            ->groupBy('waiter_id')
            ->orderByDesc('total_revenue')
            ->with('waiter.user')
            ->get();
    }

    // --- Способы оплаты ---

    #[Computed]
    public function paymentMethods(): Collection
    {
        return $this->paymentsQuery()
            ->select(
                'payment_method_id',
                DB::raw('COUNT(*) as payments_count'),
                DB::raw('SUM(amount) as total_amount'),
            )
            ->groupBy('payment_method_id')
            ->orderByDesc('total_amount')
            ->with('paymentMethod')
            ->get();
    }

    #[Computed]
    public function paymentMethodsTotal(): float
    {
        return (float) $this->paymentMethods->sum('total_amount');
    }

    /**
     * Название текущего периода для отображения.
     */
    public function getPeriodLabelProperty(): string
    {
        return match ($this->period) {
            'today' => 'Сегодня',
            'week' => 'Эта неделя',
            'month' => 'Этот месяц',
            default => 'Сегодня',
        };
    }

    public function render()
    {
        return view('livewire.cabinet.reports');
    }
}
