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

use Fxp\Component\Block\BlockFactoryInterface;
use Fxp\Component\Block\Blocks;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class BlockIntegrationTestCase extends TestCase
{
    /**
     * @var BlockFactoryInterface
     */
    protected $factory;

    protected function setUp()
    {
        $this->factory = Blocks::createBlockFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->getBlockFactory();
    }

    protected function getExtensions()
    {
        return array();
    }
}
