<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\DataCollector\EventListener;

use Sonatra\Component\Block\BlockEvent;
use Sonatra\Component\Block\BlockEvents;
use Sonatra\Component\Block\Extension\DataCollector\BlockDataCollectorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener that invokes a data collector for the {@link BlockEvents::POST_SET_DATA}.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
        return array(
            // High priority in order to be called as soon as possible
            BlockEvents::POST_SET_DATA => array('postSetData', 255),
        );
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
