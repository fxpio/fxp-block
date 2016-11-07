<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests;

use Sonatra\Component\Block\BlockEvents;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockEventsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Sonatra\Component\Block\Exception\RuntimeException
     */
    public function testInstantiationOfClass()
    {
        new BlockEvents();
    }
}
