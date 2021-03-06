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

use Fxp\Component\Block\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Intl\Util\IntlTestHelper;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class NumberToLocalizedStringTransformerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // Since we test against "fr_FR", we need the full implementation
        IntlTestHelper::requireFullIntl($this, false);

        \Locale::setDefault('fr_FR');
    }

    public function provideTransformations()
    {
        return [
            [null, '', 'fr_FR'],
            [1, '1', 'fr_FR'],
            [1.5, '1,5', 'fr_FR'],
            [1234.5, '1234,5', 'fr_FR'],
            [12345.912, '12345,912', 'fr_FR'],
            [1234.5, '1234,5', 'ru'],
            [1234.5, '1234,5', 'fi'],
        ];
    }

    /**
     * @dataProvider provideTransformations
     */
    public function testTransform($from, $to, $locale)
    {
        \Locale::setDefault($locale);

        $transformer = new NumberToLocalizedStringTransformer();

        $this->assertSame($to, $transformer->transform($from));
    }

    public function provideTransformationsWithGrouping()
    {
        return [
            [1234.5, '1 234,5', 'fr_FR'],
            [12345.912, '12 345,912', 'fr_FR'],
            [1234.5, '1 234,5', 'fr'],
            [1234.5, '1 234,5', 'ru'],
            [1234.5, '1 234,5', 'fi'],
        ];
    }

    /**
     * @dataProvider provideTransformationsWithGrouping
     */
    public function testTransformWithGrouping($from, $to, $locale)
    {
        \Locale::setDefault($locale);

        $transformer = new NumberToLocalizedStringTransformer(null, true);

        $this->assertSame($this->cleanString($to), $this->cleanString($transformer->transform($from)));
    }

    public function testTransformWithPrecision()
    {
        $transformer = new NumberToLocalizedStringTransformer(2);

        $this->assertEquals('1234,50', $transformer->transform(1234.5));
        $this->assertEquals('678,92', $transformer->transform(678.916));
    }

    public function transformWithRoundingProvider()
    {
        return [
            // towards positive infinity (1.6 -> 2, -1.6 -> -1)
            [0, 1234.5, '1235', NumberToLocalizedStringTransformer::ROUND_CEILING],
            [0, 1234.4, '1235', NumberToLocalizedStringTransformer::ROUND_CEILING],
            [0, -1234.5, '-1234', NumberToLocalizedStringTransformer::ROUND_CEILING],
            [0, -1234.4, '-1234', NumberToLocalizedStringTransformer::ROUND_CEILING],
            [1, 123.45, '123,5', NumberToLocalizedStringTransformer::ROUND_CEILING],
            [1, 123.44, '123,5', NumberToLocalizedStringTransformer::ROUND_CEILING],
            [1, -123.45, '-123,4', NumberToLocalizedStringTransformer::ROUND_CEILING],
            [1, -123.44, '-123,4', NumberToLocalizedStringTransformer::ROUND_CEILING],
            // towards negative infinity (1.6 -> 1, -1.6 -> -2)
            [0, 1234.5, '1234', NumberToLocalizedStringTransformer::ROUND_FLOOR],
            [0, 1234.4, '1234', NumberToLocalizedStringTransformer::ROUND_FLOOR],
            [0, -1234.5, '-1235', NumberToLocalizedStringTransformer::ROUND_FLOOR],
            [0, -1234.4, '-1235', NumberToLocalizedStringTransformer::ROUND_FLOOR],
            [1, 123.45, '123,4', NumberToLocalizedStringTransformer::ROUND_FLOOR],
            [1, 123.44, '123,4', NumberToLocalizedStringTransformer::ROUND_FLOOR],
            [1, -123.45, '-123,5', NumberToLocalizedStringTransformer::ROUND_FLOOR],
            [1, -123.44, '-123,5', NumberToLocalizedStringTransformer::ROUND_FLOOR],
            // away from zero (1.6 -> 2, -1.6 -> 2)
            [0, 1234.5, '1235', NumberToLocalizedStringTransformer::ROUND_UP],
            [0, 1234.4, '1235', NumberToLocalizedStringTransformer::ROUND_UP],
            [0, -1234.5, '-1235', NumberToLocalizedStringTransformer::ROUND_UP],
            [0, -1234.4, '-1235', NumberToLocalizedStringTransformer::ROUND_UP],
            [1, 123.45, '123,5', NumberToLocalizedStringTransformer::ROUND_UP],
            [1, 123.44, '123,5', NumberToLocalizedStringTransformer::ROUND_UP],
            [1, -123.45, '-123,5', NumberToLocalizedStringTransformer::ROUND_UP],
            [1, -123.44, '-123,5', NumberToLocalizedStringTransformer::ROUND_UP],
            // towards zero (1.6 -> 1, -1.6 -> -1)
            [0, 1234.5, '1234', NumberToLocalizedStringTransformer::ROUND_DOWN],
            [0, 1234.4, '1234', NumberToLocalizedStringTransformer::ROUND_DOWN],
            [0, -1234.5, '-1234', NumberToLocalizedStringTransformer::ROUND_DOWN],
            [0, -1234.4, '-1234', NumberToLocalizedStringTransformer::ROUND_DOWN],
            [1, 123.45, '123,4', NumberToLocalizedStringTransformer::ROUND_DOWN],
            [1, 123.44, '123,4', NumberToLocalizedStringTransformer::ROUND_DOWN],
            [1, -123.45, '-123,4', NumberToLocalizedStringTransformer::ROUND_DOWN],
            [1, -123.44, '-123,4', NumberToLocalizedStringTransformer::ROUND_DOWN],
            // round halves (.5) to the next even number
            [0, 1234.6, '1235', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [0, 1234.5, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [0, 1234.4, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [0, 1233.5, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [0, 1232.5, '1232', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [0, -1234.6, '-1235', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [0, -1234.5, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [0, -1234.4, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [0, -1233.5, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [0, -1232.5, '-1232', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [1, 123.46, '123,5', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [1, 123.45, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [1, 123.44, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [1, 123.35, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [1, 123.25, '123,2', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [1, -123.46, '-123,5', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [1, -123.45, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [1, -123.44, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [1, -123.35, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            [1, -123.25, '-123,2', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN],
            // round halves (.5) away from zero
            [0, 1234.6, '1235', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [0, 1234.5, '1235', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [0, 1234.4, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [0, -1234.6, '-1235', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [0, -1234.5, '-1235', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [0, -1234.4, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [1, 123.46, '123,5', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [1, 123.45, '123,5', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [1, 123.44, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [1, -123.46, '-123,5', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [1, -123.45, '-123,5', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            [1, -123.44, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_UP],
            // round halves (.5) towards zero
            [0, 1234.6, '1235', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [0, 1234.5, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [0, 1234.4, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [0, -1234.6, '-1235', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [0, -1234.5, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [0, -1234.4, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [1, 123.46, '123,5', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [1, 123.45, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [1, 123.44, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [1, -123.46, '-123,5', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [1, -123.45, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
            [1, -123.44, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN],
        ];
    }

    /**
     * @dataProvider transformWithRoundingProvider
     */
    public function testTransformWithRounding($precision, $input, $output, $roundingMode)
    {
        $transformer = new NumberToLocalizedStringTransformer($precision, null, $roundingMode);

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function testTransformDoesNotRoundIfNoPrecision()
    {
        $transformer = new NumberToLocalizedStringTransformer(null, null, NumberToLocalizedStringTransformer::ROUND_DOWN);

        $this->assertEquals('1234,547', $transformer->transform(1234.547));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\TransformationFailedException
     */
    public function testTransformExpectsNumeric()
    {
        $transformer = new NumberToLocalizedStringTransformer();
        $transformer->transform('foo');
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private function cleanString($str)
    {
        $str = preg_replace('/[^0-9\,\.\+\-]/', ' ', $str);

        return trim(preg_replace('!\s+!', ' ', $str));
    }
}
