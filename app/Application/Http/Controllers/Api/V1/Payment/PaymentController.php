<?php

namespace App\Application\Http\Controllers\Api\V1\Payment;

use App\Application\Http\Controllers\Controller;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentMethod;
use App\Domain\Order\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function methods(Request $request): JsonResponse
    {
        $methods = PaymentMethod::active()->orderBy('name')->get();

        return response()->json(['data' => $methods]);
    }

    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'method' => 'required|string|in:cash,card,transfer,bonus',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $order = Order::findOrFail($request->input('order_id'));

        if ($order->isPaid()) {
            return response()->json(['message' => 'Заказ уже оплачен.'], 422);
        }

        $paymentMethod = PaymentMethod::where('type', $request->input('method'))->firstOrFail();

        // Определяем текущую кассовую смену
        $cashShift = \App\Domain\Payment\Models\CashShift::getCurrentForBranch($order->branch_id);

        $payment = DB::transaction(function () use ($request, $order, $paymentMethod, $cashShift) {
            $paymentData = [
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'cash_shift_id' => $cashShift?->id,
                'user_id' => $request->user()->id,
                'amount' => $request->input('amount'),
                'status' => 'completed',
                'paid_at' => now(),
            ];

            // Добавляем сдачу для наличных платежей
            if ($paymentMethod->type->value === 'cash') {
                $changeAmount = $request->input('amount') - $order->getRemainingAmount();
                $paymentData['change_amount'] = max(0, $changeAmount);
            }

            $payment = Payment::create($paymentData);

            $order->updatePaymentStatus();

            return $payment;
        });

        return response()->json([
            'message' => 'Оплата принята.',
            'data' => [
                'payment' => $payment,
                'order' => $order->fresh(),
            ],
        ]);
    }

    public function refund(Request $request, Payment $payment): JsonResponse
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        $refundAmount = $request->input('amount', $payment->amount);

        if ($refundAmount > $payment->amount) {
            return response()->json(['message' => 'Сумма возврата превышает сумму оплаты.'], 422);
        }

        DB::transaction(function () use ($payment, $refundAmount, $request) {
            Payment::create([
                'order_id' => $payment->order_id,
                'payment_method_id' => $payment->payment_method_id,
                'cash_shift_id' => $payment->cash_shift_id,
                'user_id' => $request->user()->id,
                'amount' => -$refundAmount,
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            $payment->order->updatePaymentStatus();
        });

        return response()->json(['message' => 'Возврат оформлен.']);
    }
}
