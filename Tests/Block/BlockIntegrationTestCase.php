<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block;

use Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface;
use Sonatra\Bundle\BlockBundle\Block\Blocks;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class BlockIntegrationTestCase extends \PHPUnit_Framework_TestCase
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
