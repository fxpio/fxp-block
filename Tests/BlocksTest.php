<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests;

use Fxp\Component\Block\Blocks;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlocksTest extends TestCase
{
    public function testObjectFactoryBuilderCreator()
    {
        $bf = Blocks::createBlockFactoryBuilder();

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryBuilderInterface', $bf);
    }

    public function testObjectFactoryCreator()
    {
        $bf = Blocks::createBlockFactory();

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryInterface', $bf);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\RuntimeException
     */
    public function testInstantiationOfClass()
    {
        new Blocks();
    }
}
