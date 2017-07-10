<?php

namespace TheCodingMachine\ServiceProvider;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Puli\Discovery\Api\Discovery as PuliDiscovery;
use TheCodingMachine\Discovery\DiscoveryInterface as TcmDiscovery;
use Puli\Discovery\Binding\ClassBinding;

/**
 * A class that holds the list of service providers of a project.
 * This class is designed so that service provider do not need to be instantiated each time the registry is filled.
 * They can be lazily instantiated if needed.
 */
class Registry implements RegistryInterface
{
    /**
     * The array with lazy values.
     *
     * @var array
     */
    private $lazyArray;

    /**
     * The array with constructed values.
     *
     * @var array
     */
    private $constructedArray = [];

    /**
     * An array of service factories (the result of the call to 'getServices'),
     * indexed by service provider.
     *
     * @var array An array<key, array<servicename, callable>>
     */
    private $serviceFactories = [];

    private $position = 0;

    /**
     * Initializes the registry from a list of service providers.
     * This list of service providers can be passed as ServiceProvider instances, or simply class name,
     * or an array of [class name, [constructor params]].
     * If a Puli $discovery object is passed, the registry is automatically populated with ServiceProviders from Puli.
     *
     * @param array          $lazyArray The array with lazy values
     * @param PuliDiscovery|null $puliDiscovery
     */
    public function __construct(array $lazyArray = [], PuliDiscovery $puliDiscovery = null, TcmDiscovery $tcmDiscovery = null)
    {
        if ($puliDiscovery !== null) {
            $this->lazyArray = $this->discoverPuli($puliDiscovery);
        } else {
            $this->lazyArray = [];
        }

        if ($tcmDiscovery !== null) {
            $this->lazyArray = array_merge($this->lazyArray, $this->discoverTcm($tcmDiscovery));
        }

        $this->lazyArray = array_merge($this->lazyArray, $lazyArray);
    }

    /**
     * Discovers service provider class names using Puli.
     *
     * @param PuliDiscovery $discovery
     *
     * @return string[] Returns an array of service providers.
     */
    private function discoverPuli(PuliDiscovery $discovery) /*: array*/
    {
        $bindings = $discovery->findBindings('container-interop/service-provider');
        $serviceProviders = [];

        foreach ($bindings as $binding) {
            if ($binding instanceof ClassBinding) {
                $serviceProviders[] = $binding->getClassName();
            }
        }

        return $serviceProviders;
    }

    /**
     * Discovers service provider class names using thecodingmachine/discovery.
     *
     * @param TcmDiscovery $discovery
     *
     * @return string[] Returns an array of service providers.
     */
    private function discoverTcm(TcmDiscovery $discovery) /*: array*/
    {
        return $discovery->get(ServiceProvider::class);
    }

    /**
     * @param string|object $className The FQCN or the instance to put in the array
     * @param array ...$params The parameters passed to the constructor.
     *
     * @return int The key in the array
     *
     * @throws ServiceProviderRegistryInvalidArgumentException
     */
    public function push($className, ...$params)
    {
        if ($className instanceof ServiceProvider) {
            $this->lazyArray[] = $className;
        } elseif (is_string($className)) {
            $this->lazyArray[] = [$className, $params];
        } else {
            throw new InvalidArgumentException('Push expects first parameter to be a fully qualified class name or an instance of Interop\\Container\\ServiceProvider');
        }
        end($this->lazyArray);

        return key($this->lazyArray);
    }

    /**
     * Whether a offset exists.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              </p>
     *              <p>
     *              The return value will be casted to boolean if non-boolean was returned.
     *
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->lazyArray[$offset]);
    }

    /**
     * Offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     *
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if (isset($this->constructedArray[$offset])) {
            return $this->constructedArray[$offset];
        } else {
            $item = $this->lazyArray[$offset];
            if ($item instanceof ServiceProvider) {
                $this->constructedArray[$offset] = $item;

                return $item;
            } elseif (is_array($item)) {
                $className = $item[0];
                $params = isset($item[1]) ? $item[1] : [];
            } elseif (is_string($item)) {
                $className = $item;
                $params = [];
            }
            $this->constructedArray[$offset] = new $className(...$params);

            return $this->constructedArray[$offset];
        }
    }

    /**
     * Offset to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Not implemented');
    }
    /**
     * Offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->lazyArray[$offset]);
        unset($this->constructedArray[$offset]);
    }

    /**
     * Returns the result of the getServices call on service provider whose key in the registry is $offset.
     * The result is cached in the registry so 2 successive calls will trigger `getServices` only once.
     *
     * @param string $offset Key of the service provider in the registry
     *
     * @return array
     */
    public function getServices($offset)
    {
        if (!isset($this->serviceFactories[$offset])) {
            $this->serviceFactories[$offset] = $this->offsetGet($offset)->getServices();
        }

        return $this->serviceFactories[$offset];
    }

    /**
     * @param string             $offset      Key of the service provider in the registry
     * @param string             $serviceName Name of the service to fetch
     * @param ContainerInterface $container
     * @param callable|null      $previous
     *
     * @return mixed
     */
    public function createService($offset, $serviceName, ContainerInterface $container, callable $previous = null)
    {
        return call_user_func($this->getServices($offset)[$serviceName], $container, $previous);
    }

    /**
     * Return the current element.
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return mixed Can return any type.
     *
     * @since 5.0.0
     */
    public function current()
    {
        return $this->offsetGet($this->position);
    }

    /**
     * Move forward to next element.
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @since 5.0.0
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element.
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure.
     *
     * @since 5.0.0
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid.
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     *
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->offsetExists($this->position);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
