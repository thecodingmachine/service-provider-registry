<?php

namespace TheCodingMachine\ServiceProvider\Fixtures;

use Assembly\ParameterDefinition;
use Assembly\Reference;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class TestServiceProvider implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'serviceA' => function () {
                new \stdClass();
            },
            'param' => function () {
                return 42;
            },
        ];
    }
}
