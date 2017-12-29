<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Util;

/**
 * Iterator that traverses fields of a field group.
 *
 * If the iterator encounters a inherit data field group, it enters the field
 * group and traverses its children as well.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class InheritDataAwareIterator extends \IteratorIterator implements \RecursiveIterator
{
    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return new static($this->current());
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return (bool) $this->current()->getConfig()->getInheritData();
    }
}
