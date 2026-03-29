<?php

declare(strict_types=1);

use App\Domain\Floor\Models\Hall;
use App\Domain\Floor\Models\Table;
use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Product;
use App\Domain\Organization\Models\Branch;
use App\Livewire\Pos\Terminal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    ['user' => $this->user, 'organization' => $this->organization, 'branch' => $this->branch] = createAuthenticatedUser();

    $this->hall = Hall::factory()->create(['branch_id' => $this->branch->id]);
    $this->table = Table::factory()->create([
        'hall_id' => $this->hall->id,
        'status' => 'free',
    ]);

    $this->category = Category::factory()->create([
        'organization_id' => $this->organization->id,
        'is_visible' => true,
    ]);

    $this->product = Product::factory()->create([
        'organization_id' => $this->organization->id,
        'category_id' => $this->category->id,
        'name' => 'Цезарь',
        'price' => 45000,
        'is_available' => true,
    ]);
});

it('renders successfully', function (): void {
    $this->actingAs($this->user);

    $response = $this->get('/pos');

    $response->assertOk();
    $response->assertSeeLivewire(Terminal::class);
});

it('can select a table', function (): void {
    $this->actingAs($this->user);

    $component = \Livewire\Livewire::test(Terminal::class)
        ->call('selectTable', $this->table->id);

    $component->assertSet('selectedTable', $this->table->id);
    $component->assertSet('cart', []);
});

it('can add product to cart', function (): void {
    $this->actingAs($this->user);

    $component = \Livewire\Livewire::test(Terminal::class)
        ->call('selectTable', $this->table->id)
        ->call('addToCart', $this->product->id);

    $cart = $component->get('cart');

    expect($cart)->toHaveCount(1);
    expect($cart[0]['product_id'])->toBe($this->product->id);
    expect($cart[0]['name'])->toBe('Цезарь');
    expect($cart[0]['quantity'])->toBe(1);
});

it('increments quantity when adding same product', function (): void {
    $this->actingAs($this->user);

    $component = \Livewire\Livewire::test(Terminal::class)
        ->call('selectTable', $this->table->id)
        ->call('addToCart', $this->product->id)
        ->call('addToCart', $this->product->id);

    $cart = $component->get('cart');

    expect($cart)->toHaveCount(1);
    expect($cart[0]['quantity'])->toBe(2);
});

it('can remove product from cart', function (): void {
    $this->actingAs($this->user);

    $component = \Livewire\Livewire::test(Terminal::class)
        ->call('selectTable', $this->table->id)
        ->call('addToCart', $this->product->id)
        ->call('removeFromCart', 0);

    $cart = $component->get('cart');

    expect($cart)->toHaveCount(0);
});

it('can select a category to filter products', function (): void {
    $this->actingAs($this->user);

    $component = \Livewire\Livewire::test(Terminal::class)
        ->call('selectCategory', $this->category->id);

    $component->assertSet('selectedCategory', $this->category->id);
});

it('can select a hall', function (): void {
    $this->actingAs($this->user);

    $secondHall = Hall::factory()->create(['branch_id' => $this->branch->id]);

    $component = \Livewire\Livewire::test(Terminal::class)
        ->call('selectHall', $secondHall->id);

    $component->assertSet('selectedHall', $secondHall->id);
    $component->assertSet('selectedTable', null);
    $component->assertSet('cart', []);
});

it('can clear the cart', function (): void {
    $this->actingAs($this->user);

    $component = \Livewire\Livewire::test(Terminal::class)
        ->call('selectTable', $this->table->id)
        ->call('addToCart', $this->product->id)
        ->call('addToCart', $this->product->id)
        ->call('clearCart');

    $cart = $component->get('cart');

    expect($cart)->toHaveCount(0);
});
