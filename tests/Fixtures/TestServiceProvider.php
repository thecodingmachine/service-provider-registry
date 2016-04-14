<?php

namespace TheCodingMachine\ServiceProvider\Fixtures;

use Interop\Container\ServiceProvider;

class TestServiceProvider implements ServiceProvider
{
    public function getServices()
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
