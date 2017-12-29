<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\Core\EventListener;

use Fxp\Component\Block\BlockEvent;
use Fxp\Component\Block\BlockEvents;
use Fxp\Component\Block\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Resize a collection form element based on the data sent from the client.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
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
    public function __construct($type, array $options = [])
    {
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BlockEvents::PRE_SET_DATA => 'preSetData',
        ];
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
            $data = [];
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
            $block->add($name, $this->type, array_replace([
                    'property_path' => '['.$name.']',
            ], $this->options));
        }
    }
}
