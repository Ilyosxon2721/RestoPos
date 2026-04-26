<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Auth\Models\User;
use App\Domain\Menu\Models\Category;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuApiTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private Branch $branch;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::create([
            'name' => 'Test Org',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'organization_id' => $this->organization->id,
            'name' => 'Main Branch',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'organization_id' => $this->organization->id,
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Test',
            'last_name' => 'User',
            'is_active' => true,
        ]);

        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    /** @test */
    public function can_list_categories(): void
    {
        Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Салаты',
            'is_visible' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'X-Branch-Id' => (string) $this->branch->id,
        ])->getJson('/api/v1/menu/categories');

        $response->assertStatus(200);
    }

    /** @test */
    public function can_create_category(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'X-Branch-Id' => (string) $this->branch->id,
        ])->postJson('/api/v1/menu/categories', [
            'name' => 'Супы',
            'color' => '#FF9800',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function can_list_products(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'X-Branch-Id' => (string) $this->branch->id,
        ])->getJson('/api/v1/menu/products');

        $response->assertStatus(200);
    }
}
