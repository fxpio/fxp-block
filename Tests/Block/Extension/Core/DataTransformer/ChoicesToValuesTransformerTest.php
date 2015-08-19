<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block\Extension\Core\DataTransformer;

use Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer\ChoicesToValuesTransformer;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoicesToValuesTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChoicesToValuesTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $list = new ArrayChoiceList(array('A' => 0, 'B' => 1, 'C' => 2));
        $this->transformer = new ChoicesToValuesTransformer($list);
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function testTransform()
    {
        $in = array(0, 1, 2);
        $out = array('0', '1', '2');

        $this->assertSame($out, $this->transformer->transform($in));
    }

    public function testTransformNull()
    {
        $this->assertSame(array(), $this->transformer->transform(null));
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\TransformationFailedException
     */
    public function testTransformExpectsArray()
    {
        $this->transformer->transform('foobar');
    }
}
