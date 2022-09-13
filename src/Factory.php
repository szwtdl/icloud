<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/icloud
 * @link     https://www.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/icloud/blob/master/LICENSE
 */
namespace Cloud;


class Factory
{
    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }

    public static function make($name, array $config)
    {
        $application = '\\Cloud\\' . ucwords($name) . '\\Application';
        return new $application($config);
    }
}
