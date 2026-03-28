<?php

namespace App\Application\Http\Controllers\Api\V1\Staff;

use App\Application\Http\Controllers\Controller;
use App\Domain\Staff\Models\Employee;
use App\Domain\Staff\Models\EmployeeShift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $employees = Employee::where('branch_id', $branchId)
            ->when($request->boolean('active_only'), fn($q) => $q->whereNotNull('hire_date'))
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json(['data' => $employees]);
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['user', 'shifts' => fn($q) => $q->latest()->limit(10)]);

        return response()->json(['data' => $employee]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'position' => 'required|string|max:255',
            'salary_type' => 'required|string|in:hourly,monthly,percent,mixed',
            'hourly_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'sales_percent' => 'nullable|numeric|min:0|max:100',
            'hire_date' => 'nullable|date',
        ]);

        $employee = Employee::create(
            $request->only(['branch_id', 'user_id', 'position', 'salary_type', 'hourly_rate', 'monthly_salary', 'sales_percent', 'hire_date']),
        );

        return response()->json(['message' => 'Сотрудник добавлен.', 'data' => $employee], 201);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'position' => 'sometimes|string|max:255',
            'salary_type' => 'sometimes|string|in:hourly,monthly,percent,mixed',
            'hourly_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'sales_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $employee->update($request->only(['position', 'salary_type', 'hourly_rate', 'monthly_salary', 'sales_percent']));

        return response()->json(['message' => 'Сотрудник обновлён.', 'data' => $employee]);
    }

    public function clockIn(Request $request): JsonResponse
    {
        $employee = Employee::where('user_id', $request->user()->id)
            ->whereNotNull('hire_date')
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'Сотрудник не найден.'], 404);
        }

        if ($employee->getCurrentShift()) {
            return response()->json(['message' => 'Смена уже открыта.'], 422);
        }

        $shift = $employee->clockIn();

        return response()->json(['message' => 'Смена начата.', 'data' => $shift]);
    }

    public function clockOut(Request $request): JsonResponse
    {
        $employee = Employee::where('user_id', $request->user()->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Сотрудник не найден.'], 404);
        }

        $shift = $employee->clockOut();

        if (!$shift) {
            return response()->json(['message' => 'Нет открытой смены.'], 422);
        }

        return response()->json(['message' => 'Смена завершена.', 'data' => $shift]);
    }

    public function shifts(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? app('current.branch_id');

        $shifts = EmployeeShift::where('branch_id', $branchId)
            ->when($request->has('employee_id'), fn($q) => $q->where('employee_id', $request->input('employee_id')))
            ->when($request->has('date'), fn($q) => $q->whereDate('clock_in', $request->input('date')))
            ->with('employee.user')
            ->latest('clock_in')
            ->paginate($request->input('per_page', 20));

        return response()->json(['data' => $shifts]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['message' => 'Сотрудник уволен.']);
    }
}
