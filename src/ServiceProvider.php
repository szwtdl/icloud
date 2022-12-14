<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/icloud
 * @link     https://www.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/icloud/blob/master/LICENSE
 */
namespace Cloud;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Application::class, function () {
            return new Application([
                'client_id' => config('services.icloud.client_id'),
                'client_key' => config('services.icloud.client_key'),
                'options' => [
                    'base_uri' => config('services.icloud.domain'),
                ],
            ]);
        });
        $this->app->alias(Application::class, 'icloud');
    }

    public function provides()
    {
        return [Application::class, 'icloud'];
    }
}
