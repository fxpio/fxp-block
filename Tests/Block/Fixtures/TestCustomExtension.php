<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures;

use Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeExtensionInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface;

/**
 * Test for extensions.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class TestCustomExtension implements BlockExtensionInterface
{
    private $types = array();

    private $extensions = array();

    private $guesser;

    public function __construct(BlockTypeGuesserInterface $guesser)
    {
        $this->guesser = $guesser;
    }

    public function addType(BlockTypeInterface $type)
    {
        $this->types[$type->getName()] = $type;
    }

    public function getType($name)
    {
        return isset($this->types[$name]) ? $this->types[$name] : null;
    }

    public function hasType($name)
    {
        return isset($this->types[$name]);
    }

    public function addTypeExtension(BlockTypeExtensionInterface $extension)
    {
        $type = $extension->getExtendedType();

        if (!isset($this->extensions[$type])) {
            $this->extensions[$type] = array();
        }

        $this->extensions[$type][] = $extension;
    }

    public function getTypeExtensions($name)
    {
        return isset($this->extensions[$name]) ? $this->extensions[$name] : array();
    }

    public function hasTypeExtensions($name)
    {
        return isset($this->extensions[$name]);
    }

    public function getTypeGuesser()
    {
        return $this->guesser;
    }
}
