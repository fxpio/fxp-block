<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Fixtures;

use Fxp\Component\Block\BlockExtensionInterface;
use Fxp\Component\Block\BlockTypeExtensionInterface;
use Fxp\Component\Block\BlockTypeGuesserInterface;
use Fxp\Component\Block\BlockTypeInterface;

/**
 * Test for extensions.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
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
        $this->types[get_class($type)] = $type;
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
