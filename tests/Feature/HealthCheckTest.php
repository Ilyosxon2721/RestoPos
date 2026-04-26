<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_reports_ok_when_database_works(): void
    {
        // Redis is mocked since CACHE_STORE/SESSION_DRIVER are array in phpunit.xml,
        // but Redis facade still needs a connection — fake it.
        Redis::shouldReceive('connection->ping')->andReturn(true);

        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.ok', true)
            ->assertJsonPath('checks.redis.ok', true);
    }
}
