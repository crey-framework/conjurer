<?php declare(strict_types=1);
/**
 * This file is part of the CREY framework.
 * (c) 2016 Matthias Kaschubowski, All rights reserved.
 *
 * The applied license is stored at the root directory of this package.
 */

namespace Crey\Conjurer\Service;


use Crey\Conjurer\Exceptions\NotFoundException;

/**
 * Trait InspectorTrait
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
trait InspectorTrait
{
    /**
     * @var \Generator[]
     */
    private static $parameterCache = [];

    /**
     * inspects a callable, returns a iterator with the given parameters
     *
     * @param callable $variant
     * @return \Iterator
     * @throws NotFoundException
     */
    protected function inspectCallable(callable $variant): \Iterator
    {
        /**
         * normal inspection for closures
         */
        if ( $variant instanceof \Closure ) {
            return $this->inspectClosure($variant);
        }

        is_callable($variant, true, $target);

        /**
         * bridge inspection for anonymous classes
         */
        if ( $target === 'class@anonymous' ) {
            return $this->inspectMethod($variant[0], $variant[1] ?? '__invoke');
        }

        /**
         * normal inspection for regular classes
         */
        if ( false !== strpos($target, '::') ) {
            list($class, $method) = explode('::', $target);
            return $this->inspectMethod($class, $method);
        }

        /**
         * normal inspection for functions
         */
        return $this->inspectFunction($target);
    }

    /**
     * inspects a class constructor, returns a iterator with the given parameters.
     *
     * @param object|string $class
     * @return \Iterator
     */
    protected function inspectClassConstructor($class): \Iterator
    {
        $key = $this->marshalKey(
            $this->marshalClassName($this->marshalClassKey($class)),
            $this->marshalMethodName('__construct')
        );

        if ( ! array_key_exists($key, self::$parameterCache) ) {

            $class = new \ReflectionClass($class);

            if ( ! ( $constructor = $class->getConstructor() ) ) {
                $generator = function () {
                    yield from new \EmptyIterator();
                };
            } else {
                $generator = function () use ($constructor) {
                    yield from $constructor->getParameters();
                };
            }

            self::$parameterCache[$key] = $generator();
        }

        yield from self::$parameterCache[$key];
    }

    /**
     * inspects a class method, returns a iterator with the given parameters.
     *
     * @param object|string $class
     * @param string $method
     * @return \Iterator
     * @throws NotFoundException
     */
    protected function inspectMethod($class, string $method): \Iterator
    {
        $key = $this->marshalKey(
            $this->marshalClassName($this->marshalClassKey($class)),
            $this->marshalMethodName($method)
        );

        if ( ! array_key_exists($key, self::$parameterCache) ) {

            $class = new \ReflectionClass($class);

            if ( ! $class->hasMethod($method) ) {
                throw new NotFoundException('Unknown method: '.$method);
            }

            self::$parameterCache[$key] = $this->generateParameters($class->getMethod($method));
        }

        yield from self::$parameterCache[$key];
    }

    /**
     * inspects a function, returns a iterator with the given parameters
     *
     * @param string $function
     * @return \Iterator
     */
    protected function inspectFunction(string $function): \Iterator
    {
        $key = $this->marshalKey(
            $this->marshalClassName('(FUNCTION)'),
            $this->marshalMethodName($function)
        );

        if ( ! array_key_exists($key, self::$parameterCache) ) {
            $reflection = new \ReflectionFunction($function);

            self::$parameterCache[$key] = $this->generateParameters($reflection);
        }

        yield from self::$parameterCache[$key];
    }

    /**
     * inspects a closure, returns a iterator with the given parameters.
     *
     * @param \Closure $closure
     * @return \Iterator
     */
    protected function inspectClosure(\Closure $closure): \Iterator
    {
        $key = $this->marshalKey('(CLOSURE)', spl_object_hash($closure));

        if ( ! array_key_exists($key, self::$parameterCache) ) {
            $reflection = new \ReflectionFunction($closure);

            self::$parameterCache[$key] = $this->generateParameters($reflection);
        }

        yield from self::$parameterCache[$key];
    }

    /**
     * marshals the class name key-part for the parameter cache.
     *
     * @param string $class
     * @return string
     */
    protected function marshalClassName(string $class): string
    {
        return strtolower(trim($class, "\\"));
    }

    /**
     * marshals the method name key-part for the parameter cache.
     *
     * @param string $method
     * @return string
     */
    protected function marshalMethodName(string $method): string
    {
        return strtolower($method);
    }

    /**
     * marshals the class key
     *
     * @param $class
     * @return string
     */
    protected function marshalClassKey($class)
    {
        if ( is_object($class) && (new \ReflectionClass($class))->isAnonymous() ) {
            return spl_object_hash($class);
        }

        if ( is_string($class) ) {
            return $class;
        }

        return get_class($class);
    }

    /**
     * marshals the general key
     *
     * @param $primary
     * @param $secondary
     * @return string
     */
    protected function marshalKey($primary, $secondary)
    {
        return sprintf('%s::%s', $primary, $secondary);
    }

    /**
     * creates a generator for the given reflection
     *
     * @param \ReflectionFunctionAbstract $reflection
     * @return \Iterator
     */
    protected function generateParameters(\ReflectionFunctionAbstract $reflection): \Iterator
    {
        yield from $reflection->getParameters();
    }
}