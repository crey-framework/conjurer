<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer;

/**
 * Interface FactoryContract
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
interface FactoryContract extends ServiceContract
{
    /**
     * registers a service to the factory
     *
     * @param ServiceContract $service
     * @return FactoryContract
     */
    public function register(ServiceContract $service): FactoryContract;

    /**
     * fetches a service hierarchically.
     *
     * @param string $interface
     * @return ServiceContract
     */
    public function fetchService(string $interface): ServiceContract;
}