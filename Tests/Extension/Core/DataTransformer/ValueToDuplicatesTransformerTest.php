<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Extension\Core\DataTransformer;

use Sonatra\Component\Block\Extension\Core\DataTransformer\ValueToDuplicatesTransformer;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ValueToDuplicatesTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValueToDuplicatesTransformer
     */
    private $transformer;

    protected function setUp()
    {
        $this->transformer = new ValueToDuplicatesTransformer(array('a', 'b', 'c'));
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function testTransform()
    {
        $output = array(
            'a' => 'Foo',
            'b' => 'Foo',
            'c' => 'Foo',
        );

        $this->assertSame($output, $this->transformer->transform('Foo'));
    }

    public function testTransformEmpty()
    {
        $output = array(
            'a' => null,
            'b' => null,
            'c' => null,
        );

        $this->assertSame($output, $this->transformer->transform(null));
    }
}
