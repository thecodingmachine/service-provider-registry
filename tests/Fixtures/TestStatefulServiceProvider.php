<?php

namespace TheCodingMachine\ServiceProvider\Fixtures;

use Interop\Container\ServiceProvider;

class TestStatefulServiceProvider implements ServiceProvider
{
    public $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    public static function getServices()
    {
        return [
        ];
    }
}
