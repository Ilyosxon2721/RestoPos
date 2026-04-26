<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use App\Domain\Auth\Models\Role;
use App\Domain\Auth\Models\User;
use App\Domain\Organization\Models\Branch;
use App\Domain\Staff\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
class Staff extends Component
{
    use WithPagination;

    public string $search = '';

    // Модалка создания/редактирования
    public bool $showModal = false;

    public ?int $editingUserId = null;

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $pinCode = '';

    public ?int $roleId = null;

    public ?int $branchId = null;

    public string $position = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingUserId', 'firstName', 'lastName', 'email', 'phone', 'password', 'pinCode', 'roleId', 'branchId', 'position']);
        $this->showModal = true;
    }

    public function edit(int $userId): void
    {
        $user = User::where('organization_id', auth()->user()->organization_id)
            ->with(['roles', 'employee'])
            ->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->firstName = $user->first_name ?? '';
        $this->lastName = $user->last_name ?? '';
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->password = '';
        $this->pinCode = $user->pin_code ?? '';
        $this->roleId = $user->roles->first()?->id;
        $this->branchId = $user->employee?->branch_id;
        $this->position = $user->employee?->position ?? '';

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'firstName' => 'required|string|max:100',
            'lastName' => 'nullable|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'pinCode' => 'nullable|digits:4',
            'roleId' => 'required|exists:roles,id',
            'branchId' => 'nullable|exists:branches,id',
            'position' => 'nullable|string|max:255',
        ];

        if (!$this->editingUserId) {
            $rules['password'] = 'required|min:6';
        } else {
            $rules['password'] = 'nullable|min:6';
        }

        $this->validate($rules);

        $orgId = auth()->user()->organization_id;

        // Проверяем уникальность PIN в организации
        if ($this->pinCode) {
            $pinExists = User::where('organization_id', $orgId)
                ->where('pin_code', $this->pinCode)
                ->when($this->editingUserId, fn ($q) => $q->where('id', '!=', $this->editingUserId))
                ->exists();

            if ($pinExists) {
                $this->addError('pinCode', 'Этот PIN-код уже используется другим сотрудником.');

                return;
            }
        }

        DB::transaction(function () use ($orgId) {
            $userData = [
                'organization_id' => $orgId,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName ?: null,
                'email' => $this->email,
                'phone' => $this->phone ?: null,
                'pin_code' => $this->pinCode ?: null,
            ];

            if ($this->editingUserId) {
                $user = User::findOrFail($this->editingUserId);
                if ($this->password) {
                    $userData['password'] = Hash::make($this->password);
                }
                $user->update($userData);
            } else {
                $userData['uuid'] = Str::uuid();
                $userData['password'] = Hash::make($this->password);
                $userData['is_active'] = true;
                $user = User::create($userData);
            }

            // Обновляем роль
            DB::table('user_roles')->where('user_id', $user->id)->delete();
            if ($this->roleId) {
                DB::table('user_roles')->insert([
                    'user_id' => $user->id,
                    'role_id' => $this->roleId,
                    'branch_id' => $this->branchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Обновляем запись сотрудника
            if ($this->branchId) {
                Employee::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'branch_id' => $this->branchId,
                        'position' => $this->position ?: (Role::find($this->roleId)?->name ?? 'Сотрудник'),
                        'hire_date' => now(),
                    ]
                );
            }
        });

        $this->showModal = false;
    }

    public function toggleActive(int $userId): void
    {
        $user = User::where('organization_id', auth()->user()->organization_id)->findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);
    }

    public function deleteUser(int $userId): void
    {
        $user = User::where('organization_id', auth()->user()->organization_id)->findOrFail($userId);

        // Нельзя удалить самого себя
        if ($user->id === auth()->id()) {
            return;
        }

        $user->employee()?->delete();
        DB::table('user_roles')->where('user_id', $user->id)->delete();
        $user->delete();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $users = User::where('organization_id', $orgId)
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            ))
            ->with(['roles', 'employee.branch'])
            ->latest()
            ->paginate(20);

        $roles = Role::withoutGlobalScopes()
            ->where(fn ($q) => $q->where('organization_id', $orgId)->orWhere('is_system', true))
            ->orderBy('name')
            ->get();

        $branches = Branch::where('organization_id', $orgId)->get();

        return view('livewire.cabinet.staff', compact('users', 'roles', 'branches'));
    }
}
