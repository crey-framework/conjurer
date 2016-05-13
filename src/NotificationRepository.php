<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer;


use Crey\Conjurer\Notifier\NullNotifier;

/**
 * Class NotificationRepository
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
class NotificationRepository
{
    /**
     * @var callable
     */
    protected $build;

    /**
     * @var callable
     */
    protected $register;

    /**
     * @var callable
     */
    protected $buildFail;

    /**
     * NotificationRepository constructor.
     */
    public function __construct()
    {
        $notifier = new NullNotifier();

        $this->build = $notifier;
        $this->register = $notifier;
        $this->buildFail = $notifier;
    }

    /**
     * invokes the register callback
     *
     * @param ServiceContract $service
     * @param ServiceContainerContract $container
     */
    public function register(ServiceContract $service, ServiceContainerContract $container)
    {
        if ( ! $this->register instanceof NullNotifier ) {
            call_user_func($this->build, $service, $container);
        }
    }

    /**
     * sets the register callback
     *
     * @param callable $callback
     */
    public function setRegisterCallback(callable $callback)
    {
        $this->register = $callback;
    }

    /**
     * invokes the build callback
     *
     * @param ServiceContract $service
     * @param array $arguments
     * @param ServiceContainerContract $container
     */
    public function build(ServiceContract $service, array $arguments, ServiceContainerContract $container)
    {
        if ( ! $this->build instanceof NullNotifier ) {
            call_user_func($this->build, $service, $arguments, $container);
        }
    }

    /**
     * sets the build callback
     *
     * @param callable $callback
     */
    public function setBuildCallback(callable $callback)
    {
        $this->build = $callback;
    }

    /**
     * invokes the build fail callback
     *
     * @param \Throwable $throwable
     * @param ServiceContract $service
     * @param array $arguments
     * @param ServiceContainerContract $container
     */
    public function buildFail(\Throwable $throwable, ServiceContract $service, array $arguments, ServiceContainerContract $container)
    {
        if ( ! $this->buildFail instanceof NullNotifier ) {
            call_user_func($this->buildFail, $throwable, $service, $arguments, $container);
        }
    }

    /**
     * sets the build fail callback
     *
     * @param callable $callback
     */
    public function setBuildFailCallback(callable $callback)
    {
        $this->buildFail = $callback;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return array_map(function(callable $callback) {
            is_callable($callback, true, $target);

            return $target;
        }, [
            $this->build,
            $this->register,
            $this->buildFail,
        ]);
    }
}