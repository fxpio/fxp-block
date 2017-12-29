<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests;

use Fxp\Component\Block\BlockTypeGuesserInterface;
use Fxp\Component\Block\PreloadedExtension;
use Fxp\Component\Block\Tests\Fixtures\Extension\FooExtension;
use Fxp\Component\Block\Tests\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PreloadedExtensionTest extends AbstractBaseExtensionTest
{
    protected function setUp()
    {
        $types = [
            FooType::class => new FooType(),
        ];
        $extensions = [
            FooType::class => [new FooExtension()],
        ];
        /* @var BlockTypeGuesserInterface $guesser */
        $guesser = $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock();

        $this->extension = new PreloadedExtension($types, $extensions, $guesser);
    }
}
