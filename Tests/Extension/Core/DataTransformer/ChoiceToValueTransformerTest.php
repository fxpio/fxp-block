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

use Fxp\Component\Block\Extension\Core\DataTransformer\ChoiceToValueTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ChoiceToValueTransformerTest extends TestCase
{
    /**
     * @var ChoiceToValueTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $list = new ArrayChoiceList(['A' => 0, 'B' => 1, 'C' => 2]);
        $this->transformer = new ChoiceToValueTransformer($list);
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function transformProvider()
    {
        return [
            // more extensive test set can be found in FormUtilTest
            [0, '0'],
            [false, '0'],
            ['', ''],
        ];
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform($in, $out)
    {
        $this->assertSame($out, $this->transformer->transform($in));
    }
}
