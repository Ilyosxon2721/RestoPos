<?php

declare(strict_types=1);

namespace App\Application\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

final class StoreOrderRequest extends FormRequest
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
            'branch_id' => ['required', 'exists:branches,id'],
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'type' => ['nullable', 'in:dine_in,takeaway,delivery,preorder'],
            'source' => ['nullable', 'in:pos,website,app,aggregator,phone,qr'],
            'guests_count' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'branch_id.required' => 'Филиал обязателен для заполнения.',
            'branch_id.exists' => 'Выбранный филиал не существует.',
            'table_id.exists' => 'Выбранный стол не существует.',
            'customer_id.exists' => 'Выбранный клиент не существует.',
            'type.in' => 'Недопустимый тип заказа.',
            'source.in' => 'Недопустимый источник заказа.',
            'guests_count.integer' => 'Количество гостей должно быть целым числом.',
            'guests_count.min' => 'Количество гостей должно быть не менее :min.',
        ];
    }
}
