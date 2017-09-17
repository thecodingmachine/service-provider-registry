<?php
namespace TheCodingMachine\ServiceProvider;

use Interop\Container\ContainerInterface;


/**
 * A class that holds the list of service providers of a project.
 * This class is designed so that service provider do not need to be instantiated each time the registry is filled.
 * They can be lazily instantiated if needed.
 */
interface RegistryInterface extends \ArrayAccess, \Iterator
{
    /**
     * @param string|object $className The FQCN or the instance to put in the array
     * @param array ...$params The parameters passed to the constructor.
     *
     * @return int The key in the array
     *
     * @throws ServiceProviderRegistryInvalidArgumentException
     */
    public function push($className, ...$params);

    /**
     * Returns the result of the getServices call on service provider whose key in the registry is $offset.
     * The result is cached in the registry so 2 successive calls will trigger `getServices` only once.
     *
     * @param string $offset Key of the service provider in the registry
     *
     * @return array
     */
    public function getFactories($offset);

    /**
     * @param string $offset Key of the service provider in the registry
     * @param string $serviceName Name of the service to fetch
     * @param ContainerInterface $container
     *
     * @return mixed
     */
    public function createService($offset, $serviceName, ContainerInterface $container);

    /**
     * @param string             $offset      Key of the service provider in the registry
     * @param string             $serviceName Name of the service to fetch
     * @param ContainerInterface $container
     * @param mixed              $previous
     *
     * @return mixed
     */
    public function extendService($offset, $serviceName, ContainerInterface $container, $previous);
}