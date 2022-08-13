<?php

declare(strict_types=1);

/*
 * This file is part of the szwtdl/icloud.
 *
 * (c) pengjian <szpengjian@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cloud;

use Cloud\Icloud\Application;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Application::class, function () {
            return Factory::make('icloud', [
                'client_id' => config('services.icloud.client_id'),
                'client_key' => config('services.icloud.client_key'),
                'domain' => config('services.icloud.domain'),
            ]);
        });
        $this->app->alias(Application::class, 'icloud');
    }

    public function provides()
    {
        return [Application::class, 'icloud'];
    }
}
