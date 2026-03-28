<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

class ConfigTest extends TestCase
{
    /** @test */
    public function restopos_config_exists(): void
    {
        $config = config('restopos');

        $this->assertNotNull($config);
        $this->assertIsArray($config);
    }

    /** @test */
    public function restopos_config_has_required_keys(): void
    {
        $this->assertNotNull(config('restopos.currency'));
        $this->assertNotNull(config('restopos.locale'));
        $this->assertNotNull(config('restopos.order'));
        $this->assertNotNull(config('restopos.features'));
        $this->assertNotNull(config('restopos.kds'));
        $this->assertNotNull(config('restopos.warehouse'));
        $this->assertNotNull(config('restopos.loyalty'));
    }

    /** @test */
    public function currency_defaults_to_uzs(): void
    {
        $this->assertEquals('UZS', config('restopos.currency.code'));
    }

    /** @test */
    public function locale_defaults_to_russian(): void
    {
        $this->assertEquals('ru', config('restopos.locale.default'));
    }

    /** @test */
    public function features_are_enabled_by_default(): void
    {
        $features = config('restopos.features');

        $this->assertTrue($features['delivery']);
        $this->assertTrue($features['reservations']);
        $this->assertTrue($features['kds']);
        $this->assertTrue($features['warehouse']);
    }
}
