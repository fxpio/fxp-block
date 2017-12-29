<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Fixtures\DataTransformer;

use Fxp\Component\Block\BlockEvent;
use Fxp\Component\Block\BlockEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
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
        $this->mapping = array_merge([
            'preSetData' => [],
        ], $mapping);
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
        return [
            BlockEvents::PRE_SET_DATA => 'preSetData',
        ];
    }
}
