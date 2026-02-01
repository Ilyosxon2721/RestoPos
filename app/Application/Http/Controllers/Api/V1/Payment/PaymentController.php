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
            'tip_amount' => 'nullable|numeric|min:0',
        ]);

        $order = Order::findOrFail($request->input('order_id'));

        if ($order->isPaid()) {
            return response()->json(['message' => 'Заказ уже оплачен.'], 422);
        }

        $payment = DB::transaction(function () use ($request, $order) {
            $payment = Payment::create([
                'organization_id' => $order->organization_id,
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'method' => $request->input('method'),
                'amount' => $request->input('amount'),
                'tip_amount' => $request->input('tip_amount', 0),
                'status' => 'completed',
            ]);

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
                'organization_id' => $payment->organization_id,
                'order_id' => $payment->order_id,
                'user_id' => $request->user()->id,
                'method' => $payment->method,
                'amount' => -$refundAmount,
                'status' => 'completed',
                'notes' => $request->input('reason'),
            ]);

            $payment->order->updatePaymentStatus();
        });

        return response()->json(['message' => 'Возврат оформлен.']);
    }
}
