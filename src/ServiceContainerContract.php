<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer;

/**
 * Interface ServiceContainerContract
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
interface ServiceContainerContract
{
    /**
     * makes the service instance of the given interface.
     *
     * @param string $interface
     * @param array $arguments
     * @return object
     */
    public function make(string $interface, array $arguments = []);

    /**
     * calls an callable and fulfills all dependencies of the callable signature.
     *
     * @param callable $callback
     * @param array $arguments
     * @param FactoryContract|null $origin
     * @return mixed
     */
    public function call(callable $callback, array $arguments = [], FactoryContract $origin = null);

    /**
     * registers a service to the container
     *
     * @param ServiceContract $service
     * @return ServiceContainerContract
     */
    public function register(ServiceContract $service): ServiceContainerContract;

    /**
     * binds an interface to a concrete, if a services is already bound to the interface
     * in this or any upper container, a cloned instance of the registered service will be
     * used as its base.
     *
     * @param string $interface
     * @param string|null $concrete
     * @return ServiceContract
     */
    public function bind(string $interface, string $concrete = null): ServiceContract;

    /**
     * binds an interface to a concrete as a singleton. behaves exactly like the bind() method.
     * Does call singleton(true) at the service instance.
     *
     * @param string $interface
     * @param string|null $concrete
     * @return ServiceContract
     */
    public function singleton(string $interface, string $concrete = null): ServiceContract;

    /**
     * connects the current container to the given container. The given container will be used
     * as the child container.
     *
     * @param ServiceContainerContract $parentContainer
     * @return ServiceContainerContract
     */
    public function connect(ServiceContainerContract $parentContainer): ServiceContainerContract;

    /**
     * returns the parent container ( if no container is connected, the current container will be returned ).
     *
     * @return ServiceContainerContract
     */
    public function getParent(): ServiceContainerContract;

    /**
     * checks whether the current container has a parent container or not.
     *
     * @return bool
     */
    public function hasParent(): bool;

    /**
     * fetches a service hierarchically.
     *
     * @param string $interface
     * @return ServiceContract
     */
    public function fetchService(string $interface): ServiceContract;
}