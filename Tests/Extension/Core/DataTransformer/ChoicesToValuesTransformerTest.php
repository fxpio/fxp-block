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

use Fxp\Component\Block\Extension\Core\DataTransformer\ChoicesToValuesTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ChoicesToValuesTransformerTest extends TestCase
{
    /**
     * @var ChoicesToValuesTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $list = new ArrayChoiceList(['A' => 0, 'B' => 1, 'C' => 2]);
        $this->transformer = new ChoicesToValuesTransformer($list);
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function testTransform()
    {
        $in = [0, 1, 2];
        $out = ['0', '1', '2'];

        $this->assertSame($out, $this->transformer->transform($in));
    }

    public function testTransformNull()
    {
        $this->assertSame([], $this->transformer->transform(null));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\TransformationFailedException
     */
    public function testTransformExpectsArray()
    {
        $this->transformer->transform('foobar');
    }
}
