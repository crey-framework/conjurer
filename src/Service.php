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
 * Class Service
 *
 * @package crey.conjurer
 * @author Matthias Kaschubowski
 */
final class Service extends AbstractService implements ServiceContract
{
    /**
     * Service constructor.
     *
     * @param string $interface
     */
    public function __construct(string $interface)
    {
        $this->interface = $this->concrete = $interface;
    }
}