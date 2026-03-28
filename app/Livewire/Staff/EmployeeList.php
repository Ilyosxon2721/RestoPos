<?php

declare(strict_types=1);

namespace App\Livewire\Staff;

use App\Domain\Staff\Models\Employee;
use App\Domain\Auth\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class EmployeeList extends Component
{
    use WithPagination;

    #[Url]
    public string $searchQuery = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|string|max:255')]
    public string $position = '';

    #[Rule('required|string|max:50')]
    public string $role = '';

    #[Rule('nullable|string|max:20')]
    public string $phone = '';

    #[Rule('nullable|date')]
    public string $hireDate = '';

    #[Rule('nullable|numeric|min:0')]
    public string $salary = '0';

    #[Rule('required|string|in:active,inactive')]
    public string $status = 'active';

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
        $employee = Employee::findOrFail($id);

        $this->editingId = $employee->id;
        $this->name = $employee->name;
        $this->position = $employee->position ?? '';
        $this->role = $employee->role ?? '';
        $this->phone = $employee->phone ?? '';
        $this->hireDate = $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '';
        $this->salary = (string) ($employee->salary ?? 0);
        $this->status = $employee->status ?? 'active';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'position' => $this->position,
            'role' => $this->role,
            'phone' => $this->phone ?: null,
            'hire_date' => $this->hireDate ?: null,
            'salary' => (float) $this->salary,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            $employee = Employee::findOrFail($this->editingId);
            $employee->update($data);
            session()->flash('success', 'Сотрудник успешно обновлён.');
        } else {
            Employee::create($data);
            session()->flash('success', 'Сотрудник успешно добавлен.');
        }

        $this->closeModal();
    }

    public function deleteEmployee(int $id): void
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        session()->flash('success', 'Сотрудник удалён.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->position = '';
        $this->role = '';
        $this->phone = '';
        $this->hireDate = '';
        $this->salary = '0';
        $this->status = 'active';
        $this->editingId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Employee::query()->latest();

        if ($this->searchQuery !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('position', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('phone', 'like', '%' . $this->searchQuery . '%');
            });
        }

        return view('livewire.staff.employee-list', [
            'employees' => $query->paginate(15),
        ]);
    }
}
