<?php

namespace App\Application\Http\Controllers\Api\V1\Customer;

use App\Application\Http\Controllers\Controller;
use App\Domain\Customer\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->when($request->has('search'), fn ($q) => $q->search($request->input('search')))
            ->when($request->has('group_id'), fn ($q) => $q->where('customer_group_id', $request->input('group_id')))
            ->with('group')
            ->orderBy('first_name')
            ->paginate($request->input('per_page', 20));

        return response()->json(['data' => $customers]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);

        $customers = Customer::search($request->input('q'))
            ->with('group')
            ->limit(10)
            ->get();

        return response()->json(['data' => $customers]);
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->load(['group', 'bonusTransactions' => fn ($q) => $q->latest()->limit(10)]);

        return response()->json(['data' => $customer]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $customer = Customer::create([
            'organization_id' => $request->user()->organization_id,
            ...$request->only(['first_name', 'last_name', 'phone', 'email', 'birth_date', 'gender', 'customer_group_id']),
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Клиент создан.', 'data' => $customer], 201);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:customers,phone,'.$customer->id,
            'email' => 'nullable|email|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $customer->update($request->only(['first_name', 'last_name', 'phone', 'email', 'birth_date', 'gender', 'customer_group_id']));

        return response()->json(['message' => 'Клиент обновлён.', 'data' => $customer]);
    }

    public function history(Customer $customer): JsonResponse
    {
        $orders = $customer->orders()
            ->with(['items.product', 'payments'])
            ->latest()
            ->paginate(10);

        return response()->json(['data' => $orders]);
    }

    public function addBonus(Request $request, Customer $customer): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|string|in:accrual,adjustment',
            'description' => 'nullable|string|max:255',
        ]);

        $transaction = $customer->addBonus(
            $request->input('amount'),
            $request->input('type'),
            $request->input('description')
        );

        return response()->json(['message' => 'Бонусы начислены.', 'data' => $transaction]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(['message' => 'Клиент удалён.']);
    }
}
