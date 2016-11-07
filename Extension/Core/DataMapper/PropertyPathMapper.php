<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\Core\DataMapper;

use Sonatra\Component\Block\BlockInterface;
use Sonatra\Component\Block\DataMapperInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PropertyPathMapper implements DataMapperInterface
{
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * Creates a new property path mapper.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToViews($data, $blocks)
    {
        $empty = null === $data || array() === $data;

        if (!$empty && !is_array($data) && !is_object($data)) {
            return;
        }

        /* @var BlockInterface $block */
        foreach ($blocks as $block) {
            $propertyPath = $block->getPropertyPath();
            $config = $block->getConfig();

            if (!$empty && null !== $propertyPath && $config->getMapped()) {
                $block->setData($this->propertyAccessor->getValue($data, $propertyPath));
            } else {
                $block->setData($block->getConfig()->getData());
            }
        }
    }
}
