<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\DataCollector\Type;

use Sonatra\Component\Block\AbstractTypeExtension;
use Sonatra\Component\Block\Extension\Core\Type\BlockType;
use Sonatra\Component\Block\Extension\DataCollector\EventListener\DataCollectorListener;
use Sonatra\Component\Block\Extension\DataCollector\BlockDataCollectorInterface;
use Sonatra\Component\Block\BlockBuilderInterface;

/**
 * Type extension for collecting data of a block with this type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
