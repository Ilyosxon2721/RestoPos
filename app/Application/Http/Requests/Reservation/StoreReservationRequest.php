<?php

declare(strict_types=1);

namespace App\Application\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

final class StoreReservationRequest extends FormRequest
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
            'table_id' => ['required', 'exists:tables,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'guests_count' => ['required', 'integer', 'min:1'],
            'reservation_date' => ['required', 'date'],
            'reservation_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:15'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'table_id.required' => 'Стол обязателен для заполнения.',
            'table_id.exists' => 'Выбранный стол не существует.',
            'customer_name.required' => 'Имя клиента обязательно для заполнения.',
            'customer_name.string' => 'Имя клиента должно быть строкой.',
            'customer_name.max' => 'Имя клиента не должно превышать :max символов.',
            'customer_phone.required' => 'Телефон клиента обязателен для заполнения.',
            'customer_phone.string' => 'Телефон клиента должен быть строкой.',
            'customer_phone.max' => 'Телефон клиента не должен превышать :max символов.',
            'guests_count.required' => 'Количество гостей обязательно для заполнения.',
            'guests_count.integer' => 'Количество гостей должно быть целым числом.',
            'guests_count.min' => 'Количество гостей должно быть не менее :min.',
            'reservation_date.required' => 'Дата бронирования обязательна для заполнения.',
            'reservation_date.date' => 'Дата бронирования должна быть корректной датой.',
            'reservation_time.required' => 'Время бронирования обязательно для заполнения.',
            'reservation_time.date_format' => 'Время бронирования должно быть в формате ЧЧ:ММ.',
            'duration_minutes.integer' => 'Длительность должна быть целым числом.',
            'duration_minutes.min' => 'Длительность должна быть не менее :min минут.',
        ];
    }
}
