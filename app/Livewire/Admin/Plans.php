<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domain\Platform\Models\Plan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Plans extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public string $price = '0';

    public string $billing_period = 'monthly';

    public int $max_branches = 1;

    public int $max_users = 3;

    public int $max_products = 100;

    public bool $is_active = true;

    public int $sort_order = 0;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'max_branches' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'max_products' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'slug', 'description', 'price', 'billing_period', 'max_branches', 'max_users', 'max_products', 'is_active', 'sort_order']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $plan = Plan::findOrFail($id);
        $this->editingId = $plan->id;
        $this->name = $plan->name;
        $this->slug = $plan->slug;
        $this->description = $plan->description ?? '';
        $this->price = (string) $plan->price;
        $this->billing_period = $plan->billing_period;
        $this->max_branches = $plan->max_branches;
        $this->max_users = $plan->max_users;
        $this->max_products = $plan->max_products;
        $this->is_active = $plan->is_active;
        $this->sort_order = $plan->sort_order ?? 0;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Plan::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description ?: null,
                'price' => $this->price,
                'billing_period' => $this->billing_period,
                'max_branches' => $this->max_branches,
                'max_users' => $this->max_users,
                'max_products' => $this->max_products,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ]
        );

        $this->showModal = false;
        $this->reset(['editingId']);
    }

    public function delete(int $id): void
    {
        Plan::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.plans', [
            'plans' => Plan::ordered()->get(),
        ]);
    }
}
