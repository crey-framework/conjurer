<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer;


use Crey\Conjurer\Service\AdaptiveContainerTrait;
use Crey\Conjurer\Service\BuilderTrait;
use Crey\Conjurer\Service\ContainerTrait;

/**
 * Class Conjurer
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
class Conjurer implements ServiceContainerContract
{
    use ContainerTrait;
    use AdaptiveContainerTrait;
    use BuilderTrait;

    /**
     * @var ServiceContract[]|FactoryContract[]
     */
    private $services = [];

    private $notifier;

    public function __construct()
    {
        $this->notifier = new NotificationRepository();
    }

    /**
     * fetches a service hierarchically.
     *
     * @param string $interface
     * @return ServiceContract
     */
    public function fetchService(string $interface): ServiceContract
    {
        if ( ! array_key_exists($interface, $this->services) && $this->hasParent() ) {
            return $this->getParent()->fetchService($interface);
        }

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

    /**
     * getter for the notification repository
     *
     * @return NotificationRepository
     */
    public function getNotificationRepository(): NotificationRepository
    {
        return $this->notifier;
    }
}