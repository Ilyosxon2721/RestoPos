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
    public string $position = '';

    #[Rule('nullable|date')]
    public string $hireDate = '';

    #[Rule('required|string|in:hourly,monthly,percent,mixed')]
    public string $salaryType = 'monthly';

    #[Rule('nullable|numeric|min:0')]
    public string $monthlySalary = '0';

    #[Rule('nullable|numeric|min:0')]
    public string $hourlyRate = '0';

    #[Rule('nullable|numeric|min:0|max:100')]
    public string $salesPercent = '0';

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
        $employee = Employee::with('user')->findOrFail($id);

        $this->editingId = $employee->id;
        $this->position = $employee->position ?? '';
        $this->hireDate = $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '';
        $this->salaryType = $employee->salary_type?->value ?? 'monthly';
        $this->monthlySalary = (string) ($employee->monthly_salary ?? 0);
        $this->hourlyRate = (string) ($employee->hourly_rate ?? 0);
        $this->salesPercent = (string) ($employee->sales_percent ?? 0);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'position' => $this->position,
            'hire_date' => $this->hireDate ?: null,
            'salary_type' => $this->salaryType,
            'monthly_salary' => (float) $this->monthlySalary,
            'hourly_rate' => (float) $this->hourlyRate,
            'sales_percent' => (float) $this->salesPercent,
        ];

        if ($this->editingId) {
            $employee = Employee::findOrFail($this->editingId);
            $employee->update($data);
            session()->flash('success', 'Сотрудник успешно обновлён.');
        } else {
            // При создании нового сотрудника необходимо указать user_id и branch_id
            $data['branch_id'] = auth()->user()->branch_id ?? 1;
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
        $this->position = '';
        $this->hireDate = '';
        $this->salaryType = 'monthly';
        $this->monthlySalary = '0';
        $this->hourlyRate = '0';
        $this->salesPercent = '0';
        $this->editingId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Employee::query()
            ->with('user')
            ->latest();

        if ($this->searchQuery !== '') {
            $query->where(function ($q) {
                $q->where('position', 'like', '%' . $this->searchQuery . '%')
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->searchQuery . '%');
                    });
            });
        }

        return view('livewire.staff.employee-list', [
            'employees' => $query->paginate(15),
        ]);
    }
}
