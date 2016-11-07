<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests;

use Sonatra\Component\Block\BlockTypeGuesserInterface;
use Sonatra\Component\Block\PreloadedExtension;
use Sonatra\Component\Block\Tests\Fixtures\Extension\FooExtension;
use Sonatra\Component\Block\Tests\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PreloadedExtensionTest extends AbstractBaseExtensionTest
{
    protected function setUp()
    {
        $types = array(
            FooType::class => new FooType(),
        );
        $extensions = array(
            FooType::class => array(new FooExtension()),
        );
        /* @var BlockTypeGuesserInterface $guesser */
        $guesser = $this->getMockBuilder('Sonatra\Component\Block\BlockTypeGuesserInterface')->getMock();

        $this->extension = new PreloadedExtension($types, $extensions, $guesser);
    }
}
