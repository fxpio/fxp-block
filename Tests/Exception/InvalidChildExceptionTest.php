<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Exception;

use Sonatra\Component\Block\Exception\InvalidChildException;
use Sonatra\Component\Block\Test\BlockBuilderInterface;
use Sonatra\Component\Block\Tests\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InvalidChildExceptionTest extends \PHPUnit_Framework_TestCase
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
        $rType = $this->getMockBuilder('Sonatra\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $rType->expects($this->any())
            ->method('getInnerType')
            ->will($this->returnValue(new FooType()));

        $this->builder = $this->getMockBuilder('Sonatra\Component\Block\BlockBuilderInterface')->getMock();
        $this->builder->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));
        $this->builder->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($rType));

        $this->builderChild = $this->getMockBuilder('Sonatra\Component\Block\BlockBuilderInterface')->getMock();
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
     * @expectedException \Sonatra\Component\Block\Exception\InvalidChildException
     * @expectedExceptionMessage The child "bar" ("Sonatra\Component\Block\Tests\Fixtures\Type\FooType" type) is not allowed for "foo" block ("Sonatra\Component\Block\Tests\Fixtures\Type\FooType" type)
     */
    public function testExceptionWithoutAllowedType()
    {
        throw new InvalidChildException($this->builder, $this->builderChild);
    }

    /**
     * @expectedException \Sonatra\Component\Block\Exception\InvalidChildException
     * @expectedExceptionMessage The child "bar" ("Sonatra\Component\Block\Tests\Fixtures\Type\FooType" type) is not allowed for "foo" block ("Sonatra\Component\Block\Tests\Fixtures\Type\FooType" type), only "baz" allowed
     */
    public function testExceptionWithSingleAllowedType()
    {
        throw new InvalidChildException($this->builder, $this->builderChild, 'baz');
    }

    /**
     * @expectedException \Sonatra\Component\Block\Exception\InvalidChildException
     * @expectedExceptionMessage The child "bar" ("Sonatra\Component\Block\Tests\Fixtures\Type\FooType" type) is not allowed for "foo" block ("Sonatra\Component\Block\Tests\Fixtures\Type\FooType" type), only "Baz", "Boo" allowed
     */
    public function testExceptionWithMultipleAllowedType()
    {
        throw new InvalidChildException($this->builder, $this->builderChild, array('Baz', 'Boo'));
    }
}
