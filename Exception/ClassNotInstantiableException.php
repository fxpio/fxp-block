<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Exception;

/**
 * Base ClassNotInstantiableException for the Block component.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ClassNotInstantiableException extends RuntimeException
{
    /**
     * Constructor.
     *
     * @param string $classname
     */
    public function __construct($classname)
    {
        parent::__construct(sprintf('The "%s" class cannot be instantiated', $classname));
    }
}
