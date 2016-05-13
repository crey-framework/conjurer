<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer;


use Crey\Conjurer\Exceptions\UnknownParameterException;
use Crey\Conjurer\Exceptions\NotFoundException;

/**
 * Interface ServiceContract
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
interface ServiceContract
{
    /**
     * getter for the interface name of the service
     *
     * @return string
     */
    public function getInterface(): string;

    /**
     * setter for the interface name of the service
     *
     * @param string $concrete
     * @return ServiceContract
     */
    public function withConcrete(string $concrete): ServiceContract;

    /**
     * getter for the concrete name of the service
     *
     * @return string
     */
    public function getConcrete(): string;

    /**
     * drops the (alternate) concrete name and sets the interface name as the current concrete name
     *
     * @return ServiceContract
     */
    public function forgetConcrete(): ServiceContract;

    /**
     * Setter for the instancing behavior. If the first parameter is true, the service must act as
     * a services that does serve singletons otherwise the service must act as a service that does serve
     * multitons.
     *
     * @param bool $switch
     * @return ServiceContract
     */
    public function singleton(bool $switch = true): ServiceContract;

    /**
     * checks whether the service results in singletons or not.
     *
     * @return bool
     */
    public function isSingleton(): bool;

    /**
     * named parameter setter.
     *
     * @param string $name
     * @param $value
     * @return ServiceContract
     */
    public function withParameter(string $name, $value): ServiceContract;

    /**
     * removes all ( if $name is not given ) or a specific named parameter.
     *
     * @param string|null $name
     * @return ServiceContract
     */
    public function forgetParameter(string $name = null): ServiceContract;

    /**
     * named parameter getter.
     *
     * @param string $name
     * @throws UnknownParameterException if the specific parameter was not known
     * @throws NotFoundException if the specific parameter was not known
     * @return mixed
     */
    public function getParameter(string $name);

    /**
     * public function returns a parameter iterator.
     *
     * @return \Iterator
     */
    public function getParameterIterator(): \Iterator;

    /**
     * instance setter for the service.
     *
     * @param $object
     * @return ServiceContract
     */
    public function withInstance($object): ServiceContract;

    /**
     * checks whether the service holds an instance or not.
     *
     * @return bool
     */
    public function hasInstance(): bool;

    /**
     * return the current available instance. Throws an NotFound-Exception if no instance is available.
     *
     * @return object
     * @throws NotFoundException
     */
    public function getInstance();
}