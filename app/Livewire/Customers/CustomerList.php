<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Domain\Customer\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CustomerList extends Component
{
    use WithPagination;

    #[Url]
    public string $searchQuery = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|string|max:20')]
    public string $phone = '';

    #[Rule('nullable|email|max:255')]
    public string $email = '';

    #[Rule('nullable|numeric|min:0')]
    public string $bonusBalance = '0';

    #[Rule('nullable|numeric|min:0|max:100')]
    public string $discount = '0';

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $customer = Customer::findOrFail($id);

        $this->editingId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone ?? '';
        $this->email = $customer->email ?? '';
        $this->bonusBalance = (string) ($customer->bonus_balance ?? 0);
        $this->discount = (string) ($customer->discount ?? 0);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email ?: null,
            'bonus_balance' => (float) $this->bonusBalance,
            'discount' => (float) $this->discount,
        ];

        if ($this->editingId) {
            $customer = Customer::findOrFail($this->editingId);
            $customer->update($data);
            session()->flash('success', 'Клиент успешно обновлён.');
        } else {
            Customer::create($data);
            session()->flash('success', 'Клиент успешно добавлен.');
        }

        $this->closeModal();
    }

    public function deleteCustomer(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        session()->flash('success', 'Клиент удалён.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->bonusBalance = '0';
        $this->discount = '0';
        $this->editingId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Customer::query()->latest();

        if ($this->searchQuery !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('phone', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
            });
        }

        return view('livewire.customers.customer-list', [
            'customers' => $query->paginate(15),
        ]);
    }
}
