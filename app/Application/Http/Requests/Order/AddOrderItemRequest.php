<?php

declare(strict_types=1);

namespace App\Application\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

final class AddOrderItemRequest extends FormRequest
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
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.001'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'comment' => ['nullable', 'string'],
            'modifiers' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Продукт обязателен для заполнения.',
            'product_id.exists' => 'Выбранный продукт не существует.',
            'quantity.numeric' => 'Количество должно быть числом.',
            'quantity.min' => 'Количество должно быть не менее :min.',
            'unit_price.numeric' => 'Цена должна быть числом.',
            'unit_price.min' => 'Цена не может быть отрицательной.',
            'comment.string' => 'Комментарий должен быть строкой.',
            'modifiers.array' => 'Модификаторы должны быть массивом.',
        ];
    }
}
