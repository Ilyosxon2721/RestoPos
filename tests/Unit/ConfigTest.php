<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

class ConfigTest extends TestCase
{
    /** @test */
    public function forris_pos_config_exists(): void
    {
        $config = config('forris');

        $this->assertNotNull($config);
        $this->assertIsArray($config);
    }

    /** @test */
    public function forris_pos_config_has_required_keys(): void
    {
        $this->assertNotNull(config('forris.currency'));
        $this->assertNotNull(config('forris.locale'));
        $this->assertNotNull(config('forris.order'));
        $this->assertNotNull(config('forris.features'));
        $this->assertNotNull(config('forris.kds'));
        $this->assertNotNull(config('forris.warehouse'));
        $this->assertNotNull(config('forris.loyalty'));
    }

    /** @test */
    public function currency_defaults_to_uzs(): void
    {
        $this->assertEquals('UZS', config('forris.currency.code'));
    }

    /** @test */
    public function locale_defaults_to_russian(): void
    {
        $this->assertEquals('ru', config('forris.locale.default'));
    }

    /** @test */
    public function features_are_enabled_by_default(): void
    {
        $features = config('forris.features');

        $this->assertTrue($features['delivery']);
        $this->assertTrue($features['reservations']);
        $this->assertTrue($features['kds']);
        $this->assertTrue($features['warehouse']);
    }
}
