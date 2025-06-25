<?php

namespace TNM\USSD;

use TNM\USSD\Models\Session;
use TNM\USSD\Commands\Update;
use TNM\USSD\Commands\CleanUp;
use TNM\USSD\Commands\Install;
use TNM\USSD\Commands\MakeUssd;
use TNM\USSD\Commands\AuditSession;
use Illuminate\Support\Facades\Route;
use TNM\USSD\Commands\MonitorPayload;
use Illuminate\Support\ServiceProvider;
use TNM\USSD\Observers\SessionObserver;
use TNM\USSD\Commands\MakeScreenFactory;
use TNM\USSD\Commands\ListUserTransactions;

class UssdServiceProvider extends ServiceProvider
{
    private array $migrations = [];

    public function boot()
    {

        if ($this->app->runningInConsole())
            $this->publishes($this->getMigrations(), 'migrations');

        $this->registerRoutes();

        if ($this->app->environment('testing'))
            $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->loadTranslationsFrom(__DIR__ . '/translations', 'ussd');

        $this->publishes([__DIR__ . '/translations' => resource_path('lang/vendor/ussd'),]);

        $this->publishes([__DIR__ . '/config/ussd.php' => config_path('ussd.php'),]);

        Session::observe(SessionObserver::class);
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), fn() => $this->loadRoutesFrom(__DIR__ . '/routes/api.php'));
    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('ussd.routing.prefix'),
            'middleware' => config('ussd.routing.middleware'),
        ];
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/ussd.php', 'ussd');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeUssd::class,
                Install::class,
                MakeScreenFactory::class,
                CleanUp::class,
                AuditSession::class,
                ListUserTransactions::class,
                MonitorPayload::class,
                Update::class,
            ]);
        }
    }

    private function getMigrations(): array
    {
        collect([
            'create_sessions_table.php',
            'create_session_numbers_table.php',
            'create_transaction_trails_table.php',
            'create_payloads_table.php',
            'create_historical_sessions_table.php',
            'create_historical_session_numbers_table.php',
            'create_historical_payloads_table.php',
            'create_historical_transaction_trails_table.php',
        ])->each(fn($migration) => $this->migrations[__DIR__ . sprintf('/database/migrations/%s', $migration)] =
                database_path(sprintf('migrations/%s_%s', date('Y_m_d_His', time()), $migration)));

        return $this->migrations;
    }
}
