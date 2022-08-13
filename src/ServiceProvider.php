<?php

namespace Cloud;

use Cloud\Icloud\Application;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Paypal::class, function () {
            return Factory::make('icloud', [
                'client_id' => config('services.icloud.client_id'),
                'client_key' => config('services.icloud.client_key'),
                'domain' => config('services.icloud.domain')
            ]);
        });
        $this->app->alias(Application::class, 'icloud');
    }

    public function provides()
    {
        return [Application::class, 'icloud'];
    }
}