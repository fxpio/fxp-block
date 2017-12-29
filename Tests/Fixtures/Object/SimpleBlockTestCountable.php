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
 * Countable class for simple block test.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SimpleBlockTestCountable implements \Countable
{
    /**
     * @var int
     */
    private $count;

    /**
     * @param int $count
     */
    public function __construct($count)
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->count;
    }
}
