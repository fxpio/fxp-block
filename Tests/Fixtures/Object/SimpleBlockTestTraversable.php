<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Fixtures\Object;

/**
 * Traversable class for simple block test.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SimpleBlockTestTraversable implements \IteratorAggregate
{
    /**
     * @var \ArrayIterator
     */
    private $iterator;

    /**
     * @param int $count
     */
    public function __construct($count)
    {
        $this->iterator = new \ArrayIterator($count > 0 ? array_fill(0, $count, 'Foo') : []);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return $this->iterator;
    }
}
