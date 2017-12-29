<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\DataCollector\Type;

use Fxp\Component\Block\AbstractTypeExtension;
use Fxp\Component\Block\BlockBuilderInterface;
use Fxp\Component\Block\Extension\Core\Type\BlockType;
use Fxp\Component\Block\Extension\DataCollector\BlockDataCollectorInterface;
use Fxp\Component\Block\Extension\DataCollector\EventListener\DataCollectorListener;

/**
 * Type extension for collecting data of a block with this type.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DataCollectorTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventSubscriberInterface
     */
    private $listener;

    /**
     * Constructor.
     *
     * @param BlockDataCollectorInterface $dataCollector
     */
    public function __construct(BlockDataCollectorInterface $dataCollector)
    {
        $this->listener = new DataCollectorListener($dataCollector);
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->listener);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return BlockType::class;
    }
}
