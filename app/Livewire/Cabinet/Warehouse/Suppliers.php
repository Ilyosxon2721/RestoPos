<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Warehouse;

use App\Domain\Warehouse\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
final class Suppliers extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $legalName = '';

    public string $inn = '';

    public string $contactPerson = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public int $paymentTerms = 0;

    public string $notes = '';

    public bool $isActive = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'legalName', 'inn', 'contactPerson', 'phone', 'email', 'address', 'paymentTerms', 'notes', 'isActive']);
        $this->isActive = true;
        $this->paymentTerms = 0;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $supplier = Supplier::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $this->editingId = $supplier->id;
        $this->name = $supplier->name;
        $this->legalName = $supplier->legal_name ?? '';
        $this->inn = $supplier->inn ?? '';
        $this->contactPerson = $supplier->contact_person ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->email = $supplier->email ?? '';
        $this->address = $supplier->address ?? '';
        $this->paymentTerms = $supplier->payment_terms;
        $this->notes = $supplier->notes ?? '';
        $this->isActive = $supplier->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'legalName' => 'nullable|string|max:255',
            'inn' => 'nullable|string|max:20',
            'contactPerson' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'paymentTerms' => 'integer|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $orgId = auth()->user()->organization_id;

        $data = [
            'organization_id' => $orgId,
            'name' => $this->name,
            'legal_name' => $this->legalName ?: null,
            'inn' => $this->inn ?: null,
            'contact_person' => $this->contactPerson ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'address' => $this->address ?: null,
            'payment_terms' => $this->paymentTerms,
            'notes' => $this->notes ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            Supplier::where('organization_id', $orgId)->findOrFail($this->editingId)->update($data);
        } else {
            Supplier::create($data);
        }

        $this->showModal = false;
    }

    public function toggleActive(int $id): void
    {
        $supplier = Supplier::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $supplier->update(['is_active' => !$supplier->is_active]);
    }

    public function deleteSupplier(int $id): void
    {
        Supplier::where('organization_id', auth()->user()->organization_id)->findOrFail($id)->delete();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $suppliers = Supplier::where('organization_id', $orgId)
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('contact_person', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
            ))
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.cabinet.warehouse.suppliers', compact('suppliers'));
    }
}
