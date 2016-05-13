<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer\Service;


use Crey\Conjurer\Exceptions\NotFoundException;
use Crey\Conjurer\FactoryContract;
use Crey\Conjurer\ServiceContract;
use \ReflectionParameter as Parameter;

/**
 * Trait CallerTrait
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
trait CallerTrait
{
    /**
     * makes the service instance of the given interface.
     *
     * @param string $interface
     * @param array $arguments
     * @return object
     */
    abstract public function make(string $interface, array $arguments = []);

    /**
     * inspects a callable, returns a iterator with the given parameters
     *
     * @param callable $variant
     * @return \Iterator
     * @throws NotFoundException
     */
    abstract protected function inspectCallable(callable $variant): \Iterator;

    /**
     * builds the instance of a given service.
     *
     * @param ServiceContract $service
     * @param array $arguments
     * @return object
     */
    abstract protected function build(ServiceContract $service, array $arguments = []);

    /**
     * Aggregates the dependency for a parameter.
     *
     * @param Parameter $parameter
     * @param array $arguments
     * @param ServiceContract|null $service
     * @return mixed|object
     */
    abstract protected function aggregateParameter(Parameter $parameter, array $arguments, ServiceContract $service = null);

    /**
     * calls an callable and fulfills all dependencies of the callable signature.
     *
     * @param callable $callback
     * @param array $arguments
     * @param FactoryContract|null $origin
     * @return mixed
     */
    public function call(callable $callback, array $arguments = [], FactoryContract $origin = null)
    {
        $callableParameters = [];

        foreach ( $this->inspectCallable($callback) as $parameter ) {
            /** @var \ReflectionParameter $parameter */
            $callableParameters[$parameter->getPosition()] = $this->aggregateParameter($parameter, $arguments, $origin);
        }

        return call_user_func_array($callback, $callableParameters);
    }
}