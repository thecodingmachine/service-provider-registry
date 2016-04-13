<?php
namespace TheCodingMachine\ServiceProvider\Fixtures;

use Assembly\ParameterDefinition;
use Assembly\Reference;
use Interop\Container\ContainerInterface;
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
