<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer\Service;


use Crey\Conjurer\AbstractFactory;
use Crey\Conjurer\NotificationRepository;
use Crey\Conjurer\Service;
use Crey\Conjurer\ServiceContainerContract;
use Crey\Conjurer\ServiceContract;

/**
 * Trait ContainerTrait
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
trait ContainerTrait
{
    private $parent = null;

    /**
     * fetches a service hierarchically.
     *
     * @param string $interface
     * @return ServiceContract
     */
    public abstract function fetchService(string $interface): ServiceContract;

    /**
     * Stores a service by the given interface name.
     *
     * @param string $interface
     * @param ServiceContract $service
     * @return void
     */
    protected abstract function storeService(string $interface, ServiceContract $service);

    /**
     * getter for the notification repository
     *
     * @return NotificationRepository
     */
    public abstract function getNotificationRepository(): NotificationRepository;

    /**
     * registers a service to the container
     *
     * @param ServiceContract $service
     * @return ServiceContainerContract
     */
    public function register(ServiceContract $service): ServiceContainerContract
    {
        $registrableService = clone $service;

        if ( $registrableService instanceof AbstractFactory ) {
            $registrableService->initialize();
        }

        $this->storeService($registrableService->getInterface(), $registrableService);

        $this->getNotificationRepository()->register($registrableService, $this);

        return $this;
    }

    /**
     * connects the current container to the given container. The given container will be used
     * as the child container.
     *
     * @param ServiceContainerContract $parentContainer
     * @return ServiceContainerContract
     */
    public function connect(ServiceContainerContract $parentContainer): ServiceContainerContract
    {
        $this->parent = $parentContainer;

        return $this;
    }

    /**
     * returns the parent container ( if no container is connected, the current container will be returned ).
     *
     * @return ServiceContainerContract
     */
    public function getParent(): ServiceContainerContract
    {
        return $this->parent ?? $this;
    }

    /**
     * checks whether the current container has a parent container or not.
     *
     * @return bool
     */
    public function hasParent(): bool
    {
        return $this->parent instanceof ServiceContainerContract;
    }
}