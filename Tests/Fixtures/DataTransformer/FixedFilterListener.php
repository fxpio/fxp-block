<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Fixtures\DataTransformer;

use Sonatra\Component\Block\BlockEvent;
use Sonatra\Component\Block\BlockEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FixedFilterListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = array_merge(array(
            'preSetData' => array(),
        ), $mapping);
    }

    /**
     * @param BlockEvent $event
     */
    public function preSetData(BlockEvent $event)
    {
        $data = $event->getData();

        if (isset($this->mapping['preSetData'][$data])) {
            $event->setData($this->mapping['preSetData'][$data]);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BlockEvents::PRE_SET_DATA => 'preSetData',
        );
    }
}
