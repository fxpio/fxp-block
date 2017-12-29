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

use Fxp\Component\Block\BlockConfigBuilder;
use Fxp\Component\Block\BlockConfigBuilderInterface;
use Fxp\Component\Block\DataMapperInterface;
use Fxp\Component\Block\DataTransformerInterface;
use Fxp\Component\Block\ResolvedBlockTypeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockConfigBuilderTest extends TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var BlockConfigBuilderInterface
     */
    protected $config;

    protected function setUp()
    {
        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        $options = array(
            'foo' => 'bar',
        );

        $this->config = new BlockConfigBuilder('name', null, $dispatcher, $options);
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->config = null;
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testNotStringName()
    {
        new BlockConfigBuilder(array(), null, $this->dispatcher);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\InvalidArgumentException
     */
    public function testInvalidName()
    {
        new BlockConfigBuilder('foo@bar', null, $this->dispatcher);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\InvalidArgumentException
     */
    public function testInvalidClassname()
    {
        new BlockConfigBuilder('name', 'Foobar', $this->dispatcher);
    }

    public function testAddEvents()
    {
        /* @var EventSubscriberInterface $subscriber */
        $subscriber = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventSubscriberInterface')->getMock();

        $this->config->addEventListener('foo', function () {
        }, 0);
        $this->assertInstanceOf(BlockConfigBuilderInterface::class, $this->config->addEventSubscriber($subscriber));
    }

    public function testViewTransformers()
    {
        /* @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->getMockBuilder('Fxp\Component\Block\DataTransformerInterface')->getMock();
        /* @var DataTransformerInterface $dataTransformer2 */
        $dataTransformer2 = $this->getMockBuilder('Fxp\Component\Block\DataTransformerInterface')->getMock();

        $this->config->addViewTransformer($dataTransformer);
        $this->config->addViewTransformer($dataTransformer2, true);
        $this->assertCount(2, $this->config->getViewTransformers());
        $viewTransformers = $this->config->getViewTransformers();
        $this->assertSame($dataTransformer, $viewTransformers[1]);
        $this->assertSame($dataTransformer2, $viewTransformers[0]);

        $this->config->resetViewTransformers();
        $this->assertCount(0, $this->config->getViewTransformers());
    }

    public function testModelTransformers()
    {
        /* @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->getMockBuilder('Fxp\Component\Block\DataTransformerInterface')->getMock();
        /* @var DataTransformerInterface $dataTransformer2 */
        $dataTransformer2 = $this->getMockBuilder('Fxp\Component\Block\DataTransformerInterface')->getMock();

        $this->config->addModelTransformer($dataTransformer);
        $this->config->addModelTransformer($dataTransformer2, true);
        $this->assertCount(2, $this->config->getModelTransformers());
        $modelTransformers = $this->config->getModelTransformers();
        $this->assertSame($dataTransformer, $modelTransformers[0]);
        $this->assertSame($dataTransformer2, $modelTransformers[1]);

        $this->config->resetModelTransformers();
        $this->assertCount(0, $this->config->getModelTransformers());
    }

    public function testGettersAndSetters()
    {
        /* @var ResolvedBlockTypeInterface $type */
        $type = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getMockBuilder('Fxp\Component\Block\DataMapperInterface')->getMock();
        /* @var FormInterface $form */
        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')->getMock();

        $this->assertEquals('name', $this->config->getName());
        $this->assertSame($this->dispatcher, $this->config->getEventDispatcher());
        $this->assertEquals(array('foo' => 'bar'), $this->config->getOptions());
        $this->assertTrue($this->config->hasOption('foo'));
        $this->assertFalse($this->config->hasOption('baz'));
        $this->assertEquals('bar', $this->config->getOption('foo'));
        $this->assertEquals('bar', $this->config->getOption('baz', 'bar'));

        $this->assertSame($this->config, $this->config->setPropertyPath('field'));
        $this->assertInstanceOf('Symfony\Component\PropertyAccess\PropertyPath', $this->config->getPropertyPath());
        $this->assertSame($this->config, $this->config->setMapped(true));
        $this->assertTrue($this->config->getMapped());
        $this->assertSame($this->config, $this->config->setInheritData(true));
        $this->assertTrue($this->config->getInheritData());
        $this->assertSame($this->config, $this->config->setCompound(true));
        $this->assertTrue($this->config->getCompound());
        $this->assertSame($this->config, $this->config->setType($type));
        $this->assertSame($type, $this->config->getType());
        $this->assertSame($this->config, $this->config->setDataMapper($dataMapper));
        $this->assertSame($dataMapper, $this->config->getDataMapper());
        $this->assertSame($this->config, $this->config->setEmptyData('empty'));
        $this->assertEquals('empty', $this->config->getEmptyData());
        $this->assertSame($this->config, $this->config->setEmptyMessage('empty message'));
        $this->assertEquals('empty message', $this->config->getEmptyMessage());
        $this->assertFalse($this->config->hasAttribute('foo'));
        $this->assertSame($this->config, $this->config->setAttribute('foo', 'bar'));
        $this->assertTrue($this->config->hasAttribute('foo'));
        $this->assertEquals(array('foo' => 'bar'), $this->config->getAttributes());
        $this->assertSame($this->config, $this->config->setAttributes(array('bar' => 'foo')));
        $this->assertFalse($this->config->hasAttribute('foo'));
        $this->assertTrue($this->config->hasAttribute('bar'));
        $this->assertEquals(array('bar' => 'foo'), $this->config->getAttributes());
        $this->assertEquals('foo', $this->config->getAttribute('bar'));
        $this->assertEquals('bar', $this->config->getAttribute('foo', 'bar'));

        $this->assertSame($this->config, $this->config->setDataClass('Foobar'));
        $this->assertEquals('Foobar', $this->config->getDataClass());
        $this->assertSame($this->config, $this->config->setData('data'));
        $this->assertEquals('data', $this->config->getData());
        $this->assertSame($this->config, $this->config->setForm($form));
        $this->assertSame($form, $this->config->getForm());
        $this->assertSame($this->config, $this->config->setData('data'));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testAddEventListenerAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->addEventListener('foo', function () {
        }, 0);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testAddEventSubscriberAfterGetBlockConfig()
    {
        /* @var EventSubscriberInterface $subscriber */
        $subscriber = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventSubscriberInterface')->getMock();

        $config = $this->getBlockConfig();
        $config->addEventSubscriber($subscriber);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testAddViewTransformersAfterGetBlockConfig()
    {
        /* @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->getMockBuilder('Fxp\Component\Block\DataTransformerInterface')->getMock();

        $config = $this->getBlockConfig();
        $config->addViewTransformer($dataTransformer);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testResetViewTransformersAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->resetViewTransformers();
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testAddModelTransformersAfterGetBlockConfig()
    {
        /* @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->getMockBuilder('Fxp\Component\Block\DataTransformerInterface')->getMock();
        $config = $this->getBlockConfig();
        $config->addModelTransformer($dataTransformer);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testResetModelTransformersAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->resetModelTransformers();
    }

    /**
     * @€@expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testGetBlockConfigAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->getBlockConfig();
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetPropertyPathAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->setPropertyPath('field');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetMappedAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->setMapped(true);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetInheritDataAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->setInheritData(true);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetCompoundAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->setCompound(true);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetTypeAfterGetBlockConfig()
    {
        /* @var ResolvedBlockTypeInterface $type */
        $type = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();

        $config = $this->getBlockConfig();
        $config->setType($type);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetDataMapperAfterGetBlockConfig()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getMockBuilder('Fxp\Component\Block\DataMapperInterface')->getMock();

        $config = $this->getBlockConfig();
        $config->setDataMapper($dataMapper);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetEmptyDataAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->setEmptyData('empty');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetEmptyMessageAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->setEmptyMessage('empty message');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetAttributeAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->setAttribute('foo', 'bar');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetAttributesAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->setAttributes(array('foo' => 'bar'));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetDataAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->setData('data');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetDataClassAfterGetBlockConfig()
    {
        $config = $this->getBlockConfig();
        $config->setDataClass('Foobar');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testSetFormAfterGetBlockConfig()
    {
        /* @var FormInterface $form */
        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')->getMock();

        $config = $this->getBlockConfig();
        $config->setForm($form);
    }

    /**
     * @return BlockConfigBuilderInterface
     */
    private function getBlockConfig()
    {
        return $this->config->getBlockConfig();
    }
}
