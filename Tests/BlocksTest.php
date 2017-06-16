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

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Block\Blocks;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlocksTest extends TestCase
{
    public function testObjectFactoryBuilderCreator()
    {
        $bf = Blocks::createBlockFactoryBuilder();

        $this->assertInstanceOf('Sonatra\Component\Block\BlockFactoryBuilderInterface', $bf);
    }

    public function testObjectFactoryCreator()
    {
        $bf = Blocks::createBlockFactory();

        $this->assertInstanceOf('Sonatra\Component\Block\BlockFactoryInterface', $bf);
    }

    /**
     * @expectedException \Sonatra\Component\Block\Exception\RuntimeException
     */
    public function testInstantiationOfClass()
    {
        new Blocks();
    }
}
