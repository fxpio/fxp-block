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

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Block\Extension\Core\DataTransformer\ChoiceToValueTransformer;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoiceToValueTransformerTest extends TestCase
{
    /**
     * @var ChoiceToValueTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $list = new ArrayChoiceList(array('A' => 0, 'B' => 1, 'C' => 2));
        $this->transformer = new ChoiceToValueTransformer($list);
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function transformProvider()
    {
        return array(
            // more extensive test set can be found in FormUtilTest
            array(0, '0'),
            array(false, '0'),
            array('', ''),
        );
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform($in, $out)
    {
        $this->assertSame($out, $this->transformer->transform($in));
    }
}
