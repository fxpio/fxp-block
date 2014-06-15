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

use Sonatra\Bundle\BlockBundle\Block\BlockRendererInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testHumanize()
    {
        /* @var BlockRendererInterface $renderer */
        $renderer = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockRenderer')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->assertEquals('Is active', $renderer->humanize('is_active'));
        $this->assertEquals('Is active', $renderer->humanize('isActive'));
    }
}
