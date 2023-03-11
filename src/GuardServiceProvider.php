<?php

namespace GateGuardian;

use GateGuardian\Creator\GuardLoader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class GuardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes(
            [
                __DIR__ . '/config/gate_guardian.php' => config_path('gate_guardian.php'),
            ],
            'gate-guardian-config'
        );

        $this->mergeConfigFrom(__DIR__ . '/config/gate_guardian.php', 'gate_guardian');
    }

    public function register()
    {
        Auth::extend('gate_guardian', function ($app, $name, array $config) {
            return GuardLoader::load($config);
        });
    }
}
