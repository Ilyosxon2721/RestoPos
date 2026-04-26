<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Auth\Models\User;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private Branch $branch;

    private User $user;

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
            'pin_code' => '1234',
            'first_name' => 'Test',
            'last_name' => 'User',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function user_can_login_via_api(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_get_profile(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Branch-Id' => (string) $this->branch->id,
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }
}
