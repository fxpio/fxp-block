<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\Core\EventListener;

use Sonatra\Component\Block\BlockEvent;
use Sonatra\Component\Block\BlockEvents;
use Sonatra\Component\Block\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Resize a collection form element based on the data sent from the client.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ResizeBlockListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor.
     *
     * @param mixed $type
     * @param array $options
     */
    public function __construct($type, array $options = array())
    {
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BlockEvents::PRE_SET_DATA => 'preSetData',
        );
    }

    /**
     * Pre set data.
     *
     * @param BlockEvent $event
     *
     * @throws UnexpectedTypeException
     */
    public function preSetData(BlockEvent $event)
    {
        $block = $event->getBlock();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        // First remove all rows
        foreach ($block as $name => $child) {
            $block->remove($name);
        }

        // Then add all rows again in the correct order
        foreach ($data as $name => $value) {
            $block->add($name, $this->type, array_replace(array(
                    'property_path' => '['.$name.']',
            ), $this->options));
        }
    }
}
