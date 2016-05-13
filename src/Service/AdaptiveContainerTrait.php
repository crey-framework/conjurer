<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer\Service;


use Crey\Conjurer\ServiceContract;

/**
 * Trait AdaptiveContainerTrait
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
trait AdaptiveContainerTrait
{
    /**
     * fetches a service hierarchically.
     *
     * @param string $interface
     * @return ServiceContract
     */
    public abstract function fetchService(string $interface): ServiceContract;

    protected abstract function storeService(string $interface, ServiceContract $service);

    /**
     * binds an interface to a concrete, if a services is already bound to the interface
     * in this or any upper container, a cloned instance of the registered service will be
     * used as its base.
     *
     * @param string $interface
     * @param string|null $concrete
     * @return ServiceContract
     */
    public function bind(string $interface, string $concrete = null): ServiceContract
    {
        $service = $this->fetchService($interface);

        if ( is_string($concrete) ) {
            $service->withConcrete($concrete);
        }

        $this->storeService($service->getInterface(), $service);

        return $service;
    }

    /**
     * binds an interface to a concrete as a singleton. behaves exactly like the bind() method.
     * Does call singleton(true) at the service instance.
     *
     * @param string $interface
     * @param string|null $concrete
     * @return ServiceContract
     */
    public function singleton(string $interface, string $concrete = null): ServiceContract
    {
        return $this->bind($interface, $concrete)->singleton(true);
    }
}