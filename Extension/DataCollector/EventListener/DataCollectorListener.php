<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\DataCollector\EventListener;

use Fxp\Component\Block\BlockEvent;
use Fxp\Component\Block\BlockEvents;
use Fxp\Component\Block\Extension\DataCollector\BlockDataCollectorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener that invokes a data collector for the {@link BlockEvents::POST_SET_DATA}.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DataCollectorListener implements EventSubscriberInterface
{
    /**
     * @var BlockDataCollectorInterface
     */
    private $dataCollector;

    /**
     * Constructor.
     *
     * @param BlockDataCollectorInterface $dataCollector
     */
    public function __construct(BlockDataCollectorInterface $dataCollector)
    {
        $this->dataCollector = $dataCollector;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // High priority in order to be called as soon as possible
            BlockEvents::POST_SET_DATA => ['postSetData', 255],
        ];
    }

    /**
     * Listener for the {@link BlockEvents::POST_SET_DATA} event.
     *
     * @param BlockEvent $event The event object
     */
    public function postSetData(BlockEvent $event)
    {
        // Collect basic information about each block
        $this->dataCollector->collectConfiguration($event->getBlock());

        // Collect the default data
        $this->dataCollector->collectDefaultData($event->getBlock());
    }
}
