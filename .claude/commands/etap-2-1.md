# Этап 2.1: Модуль Menu

Создай полный модуль меню — категории, блюда, модификаторы.

## Структура

```
app/Domain/Menu/
├── Actions/
│   ├── CreateCategoryAction.php
│   ├── CreateMenuItemAction.php
│   ├── UpdateMenuItemAction.php
│   ├── ToggleStopListAction.php
│   └── SortCategoriesAction.php
├── DTOs/
│   ├── CategoryDTO.php
│   └── MenuItemDTO.php
└── Services/
    └── MenuService.php
```

## Задачи:

### 1. Модель MenuCategory

Создай `app/Models/MenuCategory.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Organization\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MenuCategory extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'color',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuCategory::class, 'parent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'category_id');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true)->where('is_stop_list', false);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
```

### 2. Модель MenuItem

Создай `app/Models/MenuItem.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Organization\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'price',
        'cost_price',
        'sku',
        'barcode',
        'unit',
        'cook_time_minutes',
        'sort_order',
        'is_active',
        'is_stop_list',
        'nutrition',
        'allergens',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_stop_list' => 'boolean',
            'nutrition' => 'array',
            'allergens' => 'array',
            'sort_order' => 'integer',
            'cook_time_minutes' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name) . '-' . Str::random(5);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function modifiers(): BelongsToMany
    {
        return $this->belongsToMany(Modifier::class, 'menu_item_modifiers')
            ->withPivot('is_required', 'sort_order')
            ->withTimestamps();
    }

    public function techCard(): HasMany
    {
        return $this->hasMany(TechCardIngredient::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->active()->where('is_stop_list', false);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getMarginAttribute(): ?float
    {
        if (!$this->cost_price || $this->cost_price == 0) {
            return null;
        }
        return (($this->price - $this->cost_price) / $this->price) * 100;
    }

    public function isAvailable(): bool
    {
        return $this->is_active && !$this->is_stop_list;
    }

    public function toggleStopList(): void
    {
        $this->update(['is_stop_list' => !$this->is_stop_list]);
    }
}
```

### 3. Модель Modifier

Создай `app/Models/Modifier.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Organization\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modifier extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'type',
        'min_selections',
        'max_selections',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_selections' => 'integer',
            'max_selections' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(ModifierOption::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### 4. Модель ModifierOption

Создай `app/Models/ModifierOption.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModifierOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'modifier_id',
        'name',
        'price',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(Modifier::class);
    }
}
```

### 5. Livewire: MenuCategories

Создай `app/Livewire/Menu/Categories.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Models\MenuCategory;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
final class Categories extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;
    
    public string $name = '';
    public string $description = '';
    public ?int $parentId = null;
    public string $color = '#3B82F6';
    public bool $isActive = true;

    #[Computed]
    public function categories()
    {
        return MenuCategory::query()
            ->root()
            ->with(['children', 'items'])
            ->ordered()
            ->get();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'description', 'parentId', 'color']);
        $this->isActive = true;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $category = MenuCategory::findOrFail($id);
        
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->parentId = $category->parent_id;
        $this->color = $category->color ?? '#3B82F6';
        $this->isActive = $category->is_active;
        
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parentId' => 'nullable|exists:menu_categories,id',
            'color' => 'required|string|max:7',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parentId,
            'color' => $this->color,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            MenuCategory::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Категория обновлена');
        } else {
            $data['sort_order'] = MenuCategory::max('sort_order') + 1;
            MenuCategory::create($data);
            session()->flash('success', 'Категория создана');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'description', 'parentId']);
    }

    public function delete(int $id): void
    {
        $category = MenuCategory::findOrFail($id);
        
        if ($category->items()->count() > 0) {
            session()->flash('error', 'Нельзя удалить категорию с блюдами');
            return;
        }
        
        $category->delete();
        session()->flash('success', 'Категория удалена');
    }

    public function render(): View
    {
        return view('livewire.menu.categories');
    }
}
```

### 6. View: Categories

Создай `resources/views/livewire/menu/categories.blade.php`:

```blade
<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Категории меню</h1>
        <button 
            wire:click="create" 
            class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 flex items-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить категорию
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-200">
            @forelse($this->categories as $category)
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div 
                                class="w-12 h-12 rounded-lg flex items-center justify-center text-white font-bold"
                                style="background-color: {{ $category->color ?? '#3B82F6' }}"
                            >
                                {{ mb_substr($category->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ $category->items->count() }} блюд
                                    @if(!$category->is_active)
                                        <span class="text-red-500">• Неактивна</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                wire:click="edit({{ $category->id }})"
                                class="p-2 text-gray-400 hover:text-gray-600"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button 
                                wire:click="delete({{ $category->id }})"
                                wire:confirm="Удалить категорию {{ $category->name }}?"
                                class="p-2 text-gray-400 hover:text-red-600"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    @if($category->children->count() > 0)
                        <div class="mt-4 ml-16 space-y-2">
                            @foreach($category->children as $child)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-600">{{ $child->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $child->items->count() }} блюд</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    Категорий пока нет. Создайте первую!
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-xl font-bold mb-4">
                    {{ $editingId ? 'Редактировать категорию' : 'Новая категория' }}
                </h2>
                
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                        <input 
                            type="text" 
                            wire:model="name" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            placeholder="Например: Горячие блюда"
                        >
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                        <textarea 
                            wire:model="description" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            rows="2"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Цвет</label>
                        <input 
                            type="color" 
                            wire:model="color" 
                            class="w-full h-10 rounded-lg cursor-pointer"
                        >
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isActive" id="isActive" class="rounded">
                        <label for="isActive" class="text-sm text-gray-700">Активна</label>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button 
                            type="button" 
                            wire:click="$set('showModal', false)"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit" 
                            class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700"
                        >
                            {{ $editingId ? 'Сохранить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
```

### 7. Livewire: MenuItems

Создай `app/Livewire/Menu/Items.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
final class Items extends Component
{
    use WithPagination;

    #[Url]
    public ?int $categoryId = null;
    
    #[Url]
    public string $search = '';

    public bool $showModal = false;
    public ?int $editingId = null;
    
    // Form fields
    public string $name = '';
    public string $description = '';
    public float $price = 0;
    public ?float $costPrice = null;
    public ?int $formCategoryId = null;
    public bool $isActive = true;

    #[Computed]
    public function categories()
    {
        return MenuCategory::active()->ordered()->get();
    }

    #[Computed]
    public function items()
    {
        return MenuItem::query()
            ->with('category')
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->ordered()
            ->paginate(20);
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'description', 'price', 'costPrice']);
        $this->formCategoryId = $this->categoryId;
        $this->isActive = true;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        
        $this->editingId = $item->id;
        $this->name = $item->name;
        $this->description = $item->description ?? '';
        $this->price = (float) $item->price;
        $this->costPrice = $item->cost_price ? (float) $item->cost_price : null;
        $this->formCategoryId = $item->category_id;
        $this->isActive = $item->is_active;
        
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'costPrice' => 'nullable|numeric|min:0',
            'formCategoryId' => 'required|exists:menu_categories,id',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'cost_price' => $this->costPrice,
            'category_id' => $this->formCategoryId,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            MenuItem::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Блюдо обновлено');
        } else {
            $data['sort_order'] = MenuItem::where('category_id', $this->formCategoryId)->max('sort_order') + 1;
            MenuItem::create($data);
            session()->flash('success', 'Блюдо добавлено');
        }

        $this->showModal = false;
    }

    public function toggleStopList(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->toggleStopList();
        
        session()->flash('success', $item->is_stop_list ? 'Блюдо в стоп-листе' : 'Блюдо доступно');
    }

    public function delete(int $id): void
    {
        MenuItem::findOrFail($id)->delete();
        session()->flash('success', 'Блюдо удалено');
    }

    public function render(): View
    {
        return view('livewire.menu.items');
    }
}
```

### 8. Routes

Добавь в `routes/web.php`:

```php
Route::middleware('auth')->group(function () {
    // ... existing routes
    
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/categories', \App\Livewire\Menu\Categories::class)->name('categories');
        Route::get('/items', \App\Livewire\Menu\Items::class)->name('items');
    });
});
```

### 9. Seeder для тестового меню

Создай `database/seeders/MenuSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();

        $categories = [
            ['name' => 'Салаты', 'color' => '#22C55E', 'items' => [
                ['name' => 'Цезарь с курицей', 'price' => 45000],
                ['name' => 'Греческий салат', 'price' => 38000],
                ['name' => 'Оливье', 'price' => 32000],
            ]],
            ['name' => 'Супы', 'color' => '#F59E0B', 'items' => [
                ['name' => 'Борщ', 'price' => 28000],
                ['name' => 'Лагман', 'price' => 35000],
                ['name' => 'Мастава', 'price' => 30000],
            ]],
            ['name' => 'Горячие блюда', 'color' => '#EF4444', 'items' => [
                ['name' => 'Плов', 'price' => 45000],
                ['name' => 'Шашлык (100г)', 'price' => 35000],
                ['name' => 'Манты (3шт)', 'price' => 25000],
                ['name' => 'Самса', 'price' => 12000],
            ]],
            ['name' => 'Напитки', 'color' => '#3B82F6', 'items' => [
                ['name' => 'Чай зелёный', 'price' => 8000],
                ['name' => 'Кофе Американо', 'price' => 15000],
                ['name' => 'Компот', 'price' => 10000],
                ['name' => 'Кока-Кола', 'price' => 12000],
            ]],
            ['name' => 'Десерты', 'color' => '#EC4899', 'items' => [
                ['name' => 'Чизкейк', 'price' => 28000],
                ['name' => 'Тирамису', 'price' => 32000],
                ['name' => 'Мороженое', 'price' => 15000],
            ]],
        ];

        foreach ($categories as $i => $categoryData) {
            $category = MenuCategory::create([
                'branch_id' => $branch->id,
                'name' => $categoryData['name'],
                'color' => $categoryData['color'],
                'sort_order' => $i,
            ]);

            foreach ($categoryData['items'] as $j => $itemData) {
                MenuItem::create([
                    'branch_id' => $branch->id,
                    'category_id' => $category->id,
                    'name' => $itemData['name'],
                    'price' => $itemData['price'],
                    'cost_price' => $itemData['price'] * 0.3, // 30% себестоимость
                    'sort_order' => $j,
                ]);
            }
        }
    }
}
```

## Проверка

```bash
php artisan db:seed --class=MenuSeeder
```

Открой `http://127.0.0.1:8001/menu/categories`

Этап 2.1 завершён!
