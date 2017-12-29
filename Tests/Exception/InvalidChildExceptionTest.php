<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Exception;

use Fxp\Component\Block\Exception\InvalidChildException;
use Fxp\Component\Block\Test\BlockBuilderInterface;
use Fxp\Component\Block\Tests\Fixtures\Type\FooType;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class InvalidChildExceptionTest extends TestCase
{
    /**
     * @var BlockBuilderInterface
     */
    protected $builder;

    /**
     * @var BlockBuilderInterface
     */
    protected $builderChild;

    protected function setUp()
    {
        $rType = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $rType->expects($this->any())
            ->method('getInnerType')
            ->will($this->returnValue(new FooType()));

        $this->builder = $this->getMockBuilder('Fxp\Component\Block\BlockBuilderInterface')->getMock();
        $this->builder->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));
        $this->builder->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($rType));

        $this->builderChild = $this->getMockBuilder('Fxp\Component\Block\BlockBuilderInterface')->getMock();
        $this->builderChild->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('bar'));
        $this->builderChild->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($rType));
    }

    protected function tearDown()
    {
        $this->builder = null;
        $this->builderChild = null;
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\InvalidChildException
     * @expectedExceptionMessage The child "bar" ("Fxp\Component\Block\Tests\Fixtures\Type\FooType" type) is not allowed for "foo" block ("Fxp\Component\Block\Tests\Fixtures\Type\FooType" type)
     */
    public function testExceptionWithoutAllowedType()
    {
        throw new InvalidChildException($this->builder, $this->builderChild);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\InvalidChildException
     * @expectedExceptionMessage The child "bar" ("Fxp\Component\Block\Tests\Fixtures\Type\FooType" type) is not allowed for "foo" block ("Fxp\Component\Block\Tests\Fixtures\Type\FooType" type), only "baz" allowed
     */
    public function testExceptionWithSingleAllowedType()
    {
        throw new InvalidChildException($this->builder, $this->builderChild, 'baz');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\InvalidChildException
     * @expectedExceptionMessage The child "bar" ("Fxp\Component\Block\Tests\Fixtures\Type\FooType" type) is not allowed for "foo" block ("Fxp\Component\Block\Tests\Fixtures\Type\FooType" type), only "Baz", "Boo" allowed
     */
    public function testExceptionWithMultipleAllowedType()
    {
        throw new InvalidChildException($this->builder, $this->builderChild, array('Baz', 'Boo'));
    }
}
