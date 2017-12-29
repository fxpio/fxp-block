<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Extension\Core\DataTransformer;

use Fxp\Component\Block\Extension\Core\DataTransformer\ValueToDuplicatesTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ValueToDuplicatesTransformerTest extends TestCase
{
    /**
     * @var ValueToDuplicatesTransformer
     */
    private $transformer;

    protected function setUp()
    {
        $this->transformer = new ValueToDuplicatesTransformer(['a', 'b', 'c']);
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function testTransform()
    {
        $output = [
            'a' => 'Foo',
            'b' => 'Foo',
            'c' => 'Foo',
        ];

        $this->assertSame($output, $this->transformer->transform('Foo'));
    }

    public function testTransformEmpty()
    {
        $output = [
            'a' => null,
            'b' => null,
            'c' => null,
        ];

        $this->assertSame($output, $this->transformer->transform(null));
    }
}
