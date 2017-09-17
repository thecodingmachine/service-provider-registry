<?php

namespace TheCodingMachine\ServiceProvider\Fixtures;

use Interop\Container\ServiceProvider;

class TestStatefulServiceProvider implements ServiceProvider
{
    public $foo;

    public function __construct($foo = null)
    {
        $this->foo = $foo;
    }

    public function getServices()
    {
        return [
        ];
    }
}
