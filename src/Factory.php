<?php

namespace Cloud;

class Factory
{

    public static function make($name, array $config)
    {
        $application = "\\Cloud\\" . ucwords($name) . "\\Application";
        return new $application($config);
    }

    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }

}