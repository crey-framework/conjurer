<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer;

/**
 * Class AbstractFactory
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
abstract class AbstractFactory extends AbstractService implements FactoryContract
{
    protected $interface = "stdClass";
    protected $concrete = "stdClass";

    /**
     * @var ServiceContract[]|FactoryContract[]
     */
    private $services = [];

    /**
     * Hookable initialization for a factory.
     *
     * Should not return anything or depend on any dependency.
     */
    public function initialize()
    {

    }

    /**
     * registers a service to the container
     *
     * @param ServiceContract $service
     * @return FactoryContract
     */
    public function register(ServiceContract $service): FactoryContract
    {
        $registrableService = clone $service;

        if ( $registrableService instanceof AbstractFactory ) {
            $registrableService->initialize();
        }

        $this->storeService($registrableService->getInterface(), $registrableService);

        return $this;
    }

    /**
     * fetches a service hierarchically.
     *
     * @param string $interface
     * @return ServiceContract
     */
    public function fetchService(string $interface): ServiceContract
    {
        return $this->services[$interface] ?? new Service($interface);
    }

    /**
     * Stores a service by the given interface name.
     *
     * @param string $interface
     * @param ServiceContract $service
     * @return void
     */
    protected function storeService(string $interface, ServiceContract $service)
    {
        $this->services[$interface] = $service;
    }

}