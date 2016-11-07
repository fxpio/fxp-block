<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface BlockTypeGuesserInterface
{
    /**
     * Returns a field guess for a property name of a class.
     *
     * @param string $class    The fully qualified class name
     * @param string $property The name of the property to guess for
     *
     * @return Guess\TypeGuess A guess for the field's type and options
     */
    public function guessType($class, $property);
}
