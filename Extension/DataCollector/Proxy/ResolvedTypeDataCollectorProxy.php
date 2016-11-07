<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\DataCollector\Proxy;

use Sonatra\Component\Block\Extension\DataCollector\BlockDataCollectorInterface;
use Sonatra\Component\Block\BlockBuilderInterface;
use Sonatra\Component\Block\BlockFactoryInterface;
use Sonatra\Component\Block\BlockInterface;
use Sonatra\Component\Block\BlockView;
use Sonatra\Component\Block\ResolvedBlockTypeInterface;

/**
 * Proxy that invokes a data collector when creating a block and its view.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ResolvedTypeDataCollectorProxy implements ResolvedBlockTypeInterface
{
    /**
     * @var ResolvedBlockTypeInterface
     */
    private $proxiedType;

    /**
     * @var BlockDataCollectorInterface
     */
    private $dataCollector;

    /**
     * Constructor.
     *
     * @param ResolvedBlockTypeInterface  $proxiedType
     * @param BlockDataCollectorInterface $dataCollector
     */
    public function __construct(ResolvedBlockTypeInterface $proxiedType, BlockDataCollectorInterface $dataCollector)
    {
        $this->proxiedType = $proxiedType;
        $this->dataCollector = $dataCollector;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return $this->proxiedType->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->proxiedType->getParent();
    }

    /**
     * {@inheritdoc}
     */
    public function getInnerType()
    {
        return $this->proxiedType->getInnerType();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions()
    {
        return $this->proxiedType->getTypeExtensions();
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilder(BlockFactoryInterface $factory, $name, array $options = array())
    {
        $builder = $this->proxiedType->createBuilder($factory, $name, $options);

        $builder->setAttribute('data_collector/passed_options', $options);
        $builder->setType($this);

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function createView(BlockInterface $block, BlockView $parent = null)
    {
        return $this->proxiedType->createView($block, $parent);
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $this->proxiedType->buildBlock($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function finishBlock(BlockBuilderInterface $builder, array $options)
    {
        $this->proxiedType->finishBlock($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function addParent(BlockInterface $parent, BlockInterface $block, array $options)
    {
        $this->proxiedType->addParent($parent, $block, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function removeParent(BlockInterface $parent, BlockInterface $block, array $options)
    {
        $this->proxiedType->removeParent($parent, $block, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(BlockInterface $child, BlockInterface $block, array $options)
    {
        $this->proxiedType->addChild($child, $block, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(BlockInterface $child, BlockInterface $block, array $options)
    {
        $this->proxiedType->removeChild($child, $block, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        $this->proxiedType->buildView($view, $block, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options)
    {
        $this->proxiedType->finishView($view, $block, $options);

        // Remember which view belongs to which block instance, so that we can
        // get the collected data for a view when its block instance is not
        // available
        $this->dataCollector->associateBlockWithView($block, $view);

        if (null === $block->getParent()) {
            $this->dataCollector->collectViewVariables($view);

            // Re-assemble data.
            // Since finishView() is called after finishing the views of all
            // children, we can safely assume that information has been
            // collected about the complete block tree.
            $this->dataCollector->buildFinalBlockTree($block, $view);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsResolver()
    {
        return $this->proxiedType->getOptionsResolver();
    }
}
