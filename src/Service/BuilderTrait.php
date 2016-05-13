<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer\Service;


use Crey\Conjurer\Exceptions\IncompatibleInterfaceException;
use Crey\Conjurer\FactoryContract;
use Crey\Conjurer\NotificationRepository;
use Crey\Conjurer\ServiceContract;
use Crey\Conjurer\Exceptions\ParameterException;
use \ReflectionParameter as Parameter;

/**
 * Trait BuilderTrait
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
trait BuilderTrait
{
    use InspectorTrait;
    use CallerTrait;

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
     * builds the instance of a given service.
     *
     * @param ServiceContract $service
     * @param array $arguments
     * @return object
     */
    protected function build(ServiceContract $service, array $arguments = [])
    {
        foreach ( $service->getParameterIterator() as $name => $current ) {
            if ( ! array_key_exists($name, $arguments) ) {
                $arguments[$name] = $current;
            }
        }

        if ( $service instanceof FactoryContract && method_exists($service, 'factorize') ) {
            $factorizedArguments = $this->call([$service, 'factorize'], ['arguments' => $arguments], $service);

            foreach ( $factorizedArguments as $key => $value ) {
                $arguments[$key] = $value;
            }
        }

        if ( $service->getInterface() !== $service->getConcrete() ) {
            return $this->build($this->fetchService($service->getConcrete()), $arguments);
        }

        $this->getNotificationRepository()->build(
            $service,
            $arguments,
            $this
        );

        $callableParameters = [];

        foreach ( $this->inspectClassConstructor($service->getConcrete()) as $parameter ) {

            /** @var \ReflectionParameter $parameter */
            $callableParameters[$parameter->getPosition()] = $this->aggregateParameter($parameter, $arguments, $service);

        }

        $class = $service->getConcrete();

        return new $class(... $callableParameters);
    }

    /**
     * makes the service instance of the given interface.
     *
     * @param string $interface
     * @param array $arguments
     * @throws \Throwable
     * @return object
     */
    public function make(string $interface, array $arguments = [])
    {
        $service = $this->fetchService($interface);

        if ( $service->isSingleton() && $service->hasInstance() ) {
            return $service->getInstance();
        }

        try {
            $object = $this->build($service, $arguments);

            if ( $service->isSingleton() ) {
                $service->withInstance($object);
            }

            if ( ! is_a($object, $service->getInterface()) ) {
                throw new IncompatibleInterfaceException(
                    sprintf(
                        "object's interface `%s` does not match contracted interface `%s`",
                        get_class($object),
                        $service->getInterface()
                    )
                );
            }

            return $object;
        }
        catch ( \Throwable $failure ) {
            $this->getNotificationRepository()->buildFail(
                $failure,
                $service,
                $arguments,
                $this
            );

            throw $failure;
        }
    }

    /**
     * Aggregates the dependency for a parameter.
     *
     * @param Parameter $parameter
     * @param array $arguments
     * @param ServiceContract|null $service
     * @return mixed|object
     */
    protected function aggregateParameter(Parameter $parameter, array $arguments, ServiceContract $service = null)
    {
        if ( array_key_exists($parameter->name, $arguments) ) {
            return $arguments[$parameter->name];
        }

        if ( array_key_exists($parameter->getPosition(), $arguments) ) {
            return $arguments[$parameter->getPosition()];
        }

        if ( $parameter->isOptional() && $parameter->isDefaultValueAvailable() ) {
            return $parameter->getDefaultValue();
        }

        if ( $service instanceof FactoryContract && $class = $parameter->getClass() ) {
            $innerService = $service->fetchService($class->name);

            if ( $innerService->isSingleton() && $innerService->hasInstance() ) {
                return $innerService->getInstance();
            }

            $instance = $this->build($innerService);

            if ( $innerService->isSingleton() ) {
                $innerService->withInstance($instance);
            }

            return $instance;
        }

        if ( $service instanceof ServiceContract && $class = $parameter->getClass() ) {
            return $this->make($class->name);
        }

        throw new ParameterException(
            sprintf('Missing required parameter: %s', $parameter->name)
        );
    }
}