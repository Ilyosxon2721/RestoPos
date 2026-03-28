<?php

declare(strict_types=1);

namespace App\Application\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCategoryRequest extends FormRequest
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
            'parent_id' => ['nullable', 'exists:categories,id'],
            'color' => ['nullable', 'string', 'max:7'],
            'sort_order' => ['nullable', 'integer'],
            'is_visible' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название категории обязательно для заполнения.',
            'name.string' => 'Название категории должно быть строкой.',
            'name.max' => 'Название категории не должно превышать :max символов.',
            'parent_id.exists' => 'Выбранная родительская категория не существует.',
            'color.string' => 'Цвет должен быть строкой.',
            'color.max' => 'Цвет не должен превышать :max символов.',
            'sort_order.integer' => 'Порядок сортировки должен быть целым числом.',
            'is_visible.boolean' => 'Видимость должна быть логическим значением.',
        ];
    }
}
