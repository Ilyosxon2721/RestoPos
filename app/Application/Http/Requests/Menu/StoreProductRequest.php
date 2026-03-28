<?php

declare(strict_types=1);

namespace App\Application\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['nullable', 'in:dish,drink,product,service,semi_finished'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'cooking_time' => ['nullable', 'integer', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название продукта обязательно для заполнения.',
            'name.string' => 'Название продукта должно быть строкой.',
            'name.max' => 'Название продукта не должно превышать :max символов.',
            'category_id.required' => 'Категория обязательна для заполнения.',
            'category_id.exists' => 'Выбранная категория не существует.',
            'type.in' => 'Недопустимый тип продукта.',
            'price.required' => 'Цена обязательна для заполнения.',
            'price.numeric' => 'Цена должна быть числом.',
            'price.min' => 'Цена не может быть отрицательной.',
            'cost_price.numeric' => 'Себестоимость должна быть числом.',
            'cost_price.min' => 'Себестоимость не может быть отрицательной.',
            'cooking_time.integer' => 'Время приготовления должно быть целым числом.',
            'cooking_time.min' => 'Время приготовления не может быть отрицательным.',
            'is_available.boolean' => 'Доступность должна быть логическим значением.',
        ];
    }
}
