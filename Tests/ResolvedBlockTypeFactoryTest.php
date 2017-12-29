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

use Fxp\Component\Block\BlockTypeInterface;
use Fxp\Component\Block\ResolvedBlockTypeFactory;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ResolvedBlockTypeFactoryTest extends TestCase
{
    public function testCreateResolvedType()
    {
        /* @var BlockTypeInterface|\PHPUnit_Framework_MockObject_MockObject $type */
        $type = $this->getMockBuilder('Fxp\Component\Block\BlockTypeInterface')->getMock();

        $parentType = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();

        $factory = new ResolvedBlockTypeFactory();
        $rType = $factory->createResolvedType($type, [], $parentType);

        $this->assertInstanceOf('Fxp\Component\Block\ResolvedBlockTypeInterface', $rType);
    }
}
