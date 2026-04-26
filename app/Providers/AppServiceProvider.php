<?php

namespace App\Providers;

use App\Domain\Infrastructure\Sms\LogSmsSender;
use App\Domain\Infrastructure\Sms\PlayMobileSmsSender;
use App\Domain\Infrastructure\Sms\SmsSender;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsSender::class, function ($app) {
            $config = $app['config']->get('services.sms');

            return match ($config['driver']) {
                'playmobile' => new PlayMobileSmsSender(
                    endpoint: $config['playmobile']['endpoint'],
                    username: (string) $config['playmobile']['username'],
                    password: (string) $config['playmobile']['password'],
                    sender: $config['sender'],
                ),
                default => new LogSmsSender,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
