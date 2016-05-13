<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer;


use Crey\Conjurer\Exceptions\NotFoundException;
use Crey\Conjurer\Exceptions\UnknownParameterException;

/**
 * Class AbstractService
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
abstract class AbstractService
{
    /**
     * @var string
     */
    protected $interface;

    /**
     * @var string
     */
    protected $concrete;

    /**
     * @var bool
     */
    protected $singleton = false;

    /**
     * @var mixed[]
     */
    protected $parameters = [];

    /**
     * @var object
     */
    protected $instance;

    /**
     * getter for the interface name of the service
     *
     * @return string
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * setter for the interface name of the service
     *
     * @param string $concrete
     * @return ServiceContract
     */
    public function withConcrete(string $concrete): ServiceContract
    {
        $this->concrete = $concrete;

        return $this;
    }

    /**
     * getter for the concrete name of the service
     *
     * @return string
     */
    public function getConcrete(): string
    {
        return $this->concrete;
    }

    /**
     * drops the (alternate) concrete name and sets the interface name as the current concrete name
     *
     * @return ServiceContract
     */
    public function forgetConcrete(): ServiceContract
    {
        $this->concrete = $this->interface;

        return $this;
    }

    /**
     * Setter for the instancing behavior. If the first parameter is true, the service must act as
     * a services that does serve singletons otherwise the service must act as a service that does serve
     * multitons.
     *
     * @param bool $switch
     * @return ServiceContract
     */
    public function singleton(bool $switch = true): ServiceContract
    {
        $this->singleton = $switch;

        return $this;
    }

    /**
     * checks whether the service results in singletons or not.
     *
     * @return bool
     */
    public function isSingleton(): bool
    {
        return $this->singleton;
    }

    /**
     * named parameter setter.
     *
     * @param string $name
     * @param $value
     * @return ServiceContract
     */
    public function withParameter(string $name, $value): ServiceContract
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * removes all ( if $name is not given ) or a specific named parameter.
     *
     * @param string|null $name
     * @return ServiceContract
     */
    public function forgetParameter(string $name = null): ServiceContract
    {
        if ( is_string($name) ) {
            unset($this->parameters[$name]);
        }
        else {
            $this->parameters = [];
        }

        return $this;
    }

    /**
     * named parameter getter.
     *
     * @param string $name
     * @throws UnknownParameterException if the specific parameter was not known
     * @throws NotFoundException if the specific parameter was not known
     * @return mixed
     */
    public function getParameter(string $name)
    {
        if ( ! array_key_exists($name, $this->parameters) ) {
            throw new UnknownParameterException(
                sprintf('parameter with name `%s` is not known to this services', $name)
            );
        }

        return $this->parameters[$name];
    }

    /**
     * public function returns a parameter iterator.
     *
     * @return \Iterator
     */
    public function getParameterIterator(): \Iterator
    {
        foreach ( $this->parameters as $key => $value ) {
            yield $key => $value;
        }
    }

    /**
     * instance setter for the service.
     *
     * @param $object
     * @return ServiceContract
     */
    public function withInstance($object): ServiceContract
    {
        if ( ! is_a($object, $this->getInterface()) ) {
            throw new \LogicException('Incompatible instance');
        }

        $this->instance = $object;

        return $this;
    }

    /**
     * checks whether the service holds an instance or not.
     *
     * @return bool
     */
    public function hasInstance(): bool
    {
        return is_object($this->instance);
    }

    /**
     * return the current available instance. Throws an NotFound-Exception if no instance is available.
     *
     * @return object
     * @throws NotFoundException
     */
    public function getInstance()
    {
        if ( ! $this->isSingleton() ) {
            throw new \LogicException('This service can not hold instances');
        }

        if ( ! $this->hasInstance() ) {
            throw new NotFoundException('No instance stored');
        }

        return $this->instance;
    }
}