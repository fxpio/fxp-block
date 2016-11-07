<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Fixtures\Object;

/**
 * Countable class for simple block test.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
