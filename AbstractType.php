<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block;

use Fxp\Component\Block\Extension\Core\Type\BlockType;
use Fxp\Component\Block\Util\StringUtil;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractType implements BlockTypeInterface
{
    use CommonTypeTrait;

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return StringUtil::fqcnToBlockPrefix(\get_class($this));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BlockType::class;
    }
}
