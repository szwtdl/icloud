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

class Factory
{
    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }

    public static function make($name, array $config)
    {
        $application = '\\Cloud\\'.ucwords($name).'\\Application';

        return new $application($config);
    }
}
