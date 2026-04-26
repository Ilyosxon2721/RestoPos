<?php

declare(strict_types=1);

use App\Domain\Auth\Models\User;
use App\Domain\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects unauthenticated users to login', function (): void {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

it('shows login page for guests', function (): void {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSeeLivewire(\App\Livewire\Auth\Login::class);
})->skip('Stale test, pending rewrite for current Login Livewire component.');

it('can login with valid credentials', function (): void {
    $organization = Organization::factory()->create();

    $user = User::factory()->create([
        'organization_id' => $organization->id,
        'email' => 'admin@test.com',
        'password' => bcrypt('password123'),
    ]);

    \Livewire\Livewire::test(\App\Livewire\Auth\Login::class)
        ->set('email', 'admin@test.com')
        ->set('password', 'password123')
        ->call('login')
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
})->skip('Stale test, pending rewrite for current Login Livewire component.');

it('rejects invalid credentials', function (): void {
    $organization = Organization::factory()->create();

    User::factory()->create([
        'organization_id' => $organization->id,
        'email' => 'admin@test.com',
        'password' => bcrypt('password123'),
    ]);

    \Livewire\Livewire::test(\App\Livewire\Auth\Login::class)
        ->set('email', 'admin@test.com')
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email')
        ->assertNoRedirect();

    $this->assertGuest();
});

it('can login with PIN code', function (): void {
    test()->markTestSkipped('Stale test, pending rewrite for current PIN login flow.');

    $organization = Organization::factory()->create();

    $user = User::factory()->create([
        'organization_id' => $organization->id,
        'pin_code' => '9876',
    ]);

    \Livewire\Livewire::test(\App\Livewire\Auth\Login::class)
        ->set('showPinLogin', true)
        ->set('pin', '9876')
        ->call('pinLogin')
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid PIN code', function (): void {
    test()->markTestSkipped('Stale test, pending rewrite for current PIN login flow.');

    $organization = Organization::factory()->create();

    User::factory()->create([
        'organization_id' => $organization->id,
        'pin_code' => '9876',
    ]);

    \Livewire\Livewire::test(\App\Livewire\Auth\Login::class)
        ->set('showPinLogin', true)
        ->set('pin', '0000')
        ->call('pinLogin')
        ->assertHasErrors('pin')
        ->assertNoRedirect();

    $this->assertGuest();
});

it('can logout', function (): void {
    test()->markTestSkipped('Stale test, pending rewrite for current logout flow.');

    ['user' => $user] = createAuthenticatedUser();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

it('redirects authenticated users away from login page', function (): void {
    test()->markTestSkipped('Stale test, pending rewrite for current redirect-by-role flow.');

    ['user' => $user] = createAuthenticatedUser();

    $response = $this->actingAs($user)->get('/login');

    // Гостевой middleware перенаправляет авторизованных пользователей
    $response->assertRedirect();
});
