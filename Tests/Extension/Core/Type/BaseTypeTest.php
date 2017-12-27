<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Extension\Core\Type;

use Sonatra\Component\Block\Extension\Core\Type\BlockType;
use Sonatra\Component\Block\Tests\TypeTestCase;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class BaseTypeTest extends TypeTestCase
{
    abstract protected function getTestedType();

    public function testPassIdAndNameToView()
    {
        $view = $this->factory->createNamed('name', $this->getTestedType())
            ->createView();

        $this->assertEquals('name', $view->vars['id']);
        $this->assertEquals('name', $view->vars['name']);
    }

    public function testStripLeadingUnderscoresAndDigitsBlockId()
    {
        $view = $this->factory->createNamed('_09name', $this->getTestedType())
            ->createView();

        $this->assertEquals('name', $view->vars['id']);
        $this->assertEquals('_09name', $view->vars['name']);
    }

    public function testPassIdAndNameToViewWithParent()
    {
        $view = $this->factory->createNamedBuilder('parent', BlockType::class)
            ->add('child', $this->getTestedType(), array('chained_block' => true))
            ->getBlock()
            ->createView();

        $this->assertEquals('parent_child', $view['child']->vars['id']);
        $this->assertEquals('child', $view['child']->vars['name']);
    }

    public function testPassIdAndNameToViewWithGrandParent()
    {
        $builder = $this->factory->createNamedBuilder('parent', BlockType::class)
            ->add('child', BlockType::class, array('chained_block' => true));
        $builder->get('child')->add('grand_child', $this->getTestedType(), array('chained_block' => true));
        $view = $builder->getBlock()->createView();

        $this->assertEquals('parent_child_grand_child', $view['child']['grand_child']->vars['id']);
        $this->assertEquals('grand_child', $view['child']['grand_child']->vars['name']);
    }

    public function testPassTranslationDomainToView()
    {
        $block = $this->factory->create($this->getTestedType(), null, array(
            'translation_domain' => 'domain',
        ));
        $view = $block->createView();

        $this->assertSame('domain', $view->vars['translation_domain']);
    }

    public function testInheritTranslationDomainBlockParent()
    {
        $view = $this->factory
            ->createNamedBuilder('parent', BlockType::class, null, array(
                'translation_domain' => 'domain',
            ))
            ->add('child', $this->getTestedType())
            ->getBlock()
            ->createView();

        $this->assertEquals('domain', $view['child']->vars['translation_domain']);
    }

    public function testPreferOwnTranslationDomain()
    {
        $view = $this->factory
            ->createNamedBuilder('parent', BlockType::class, null, array(
                'translation_domain' => 'parent_domain',
            ))
            ->add('child', $this->getTestedType(), array(
                'translation_domain' => 'domain',
            ))
            ->getBlock()
            ->createView();

        $this->assertEquals('domain', $view['child']->vars['translation_domain']);
    }

    public function testDefaultTranslationDomain()
    {
        $view = $this->factory->createNamedBuilder('parent', BlockType::class)
            ->add('child', $this->getTestedType())
            ->getBlock()
            ->createView();

        $this->assertNull($view['child']->vars['translation_domain']);
    }

    public function testPassLabelToView()
    {
        $block = $this->factory->createNamed('__test___field', $this->getTestedType(), null, array('label' => 'My label'));
        $view = $block->createView();

        $this->assertSame('My label', $view->vars['label']);
    }
}
