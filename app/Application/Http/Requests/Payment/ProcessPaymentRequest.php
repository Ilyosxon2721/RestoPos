<?php

declare(strict_types=1);

namespace App\Application\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

final class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id'],
            'method' => ['required', 'string', 'in:cash,card,transfer,bonus'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_id.required' => 'Заказ обязателен для заполнения.',
            'order_id.exists' => 'Выбранный заказ не существует.',
            'method.required' => 'Способ оплаты обязателен для заполнения.',
            'method.string' => 'Способ оплаты должен быть строкой.',
            'method.in' => 'Недопустимый способ оплаты.',
            'amount.required' => 'Сумма обязательна для заполнения.',
            'amount.numeric' => 'Сумма должна быть числом.',
            'amount.min' => 'Сумма должна быть не менее :min.',
        ];
    }
}
