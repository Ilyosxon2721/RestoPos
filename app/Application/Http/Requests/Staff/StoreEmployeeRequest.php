<?php

declare(strict_types=1);

namespace App\Application\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEmployeeRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'position' => ['required', 'string', 'max:255'],
            'hire_date' => ['required', 'date'],
            'salary_type' => ['required', 'in:fixed,hourly,mixed'],
            'monthly_salary' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Пользователь обязателен для заполнения.',
            'user_id.exists' => 'Выбранный пользователь не существует.',
            'branch_id.required' => 'Филиал обязателен для заполнения.',
            'branch_id.exists' => 'Выбранный филиал не существует.',
            'position.required' => 'Должность обязательна для заполнения.',
            'position.string' => 'Должность должна быть строкой.',
            'position.max' => 'Должность не должна превышать :max символов.',
            'hire_date.required' => 'Дата найма обязательна для заполнения.',
            'hire_date.date' => 'Дата найма должна быть корректной датой.',
            'salary_type.required' => 'Тип оплаты обязателен для заполнения.',
            'salary_type.in' => 'Недопустимый тип оплаты.',
            'monthly_salary.numeric' => 'Месячная зарплата должна быть числом.',
            'monthly_salary.min' => 'Месячная зарплата не может быть отрицательной.',
            'hourly_rate.numeric' => 'Почасовая ставка должна быть числом.',
            'hourly_rate.min' => 'Почасовая ставка не может быть отрицательной.',
        ];
    }
}
