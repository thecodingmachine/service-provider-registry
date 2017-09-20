<?php

namespace TheCodingMachine\ServiceProvider\Fixtures;

use Interop\Container\ServiceProviderInterface;

class TestServiceProvider implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'serviceA' => function () {
                return new \stdClass();
            },
            'param' => function () {
                return 42;
            },
        ];
    }

    public function getExtensions()
    {
        return [
            'serviceB' => function () {
                return new \stdClass();
            },
        ];
    }
}
