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
use Sonatra\Component\Block\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use Symfony\Component\Intl\Util\IntlTestHelper;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
        return array(
            array(null, '', 'fr_FR'),
            array(1, '1', 'fr_FR'),
            array(1.5, '1,5', 'fr_FR'),
            array(1234.5, '1234,5', 'fr_FR'),
            array(12345.912, '12345,912', 'fr_FR'),
            array(1234.5, '1234,5', 'ru'),
            array(1234.5, '1234,5', 'fi'),
        );
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
        return array(
            array(1234.5, '1 234,5', 'fr_FR'),
            array(12345.912, '12 345,912', 'fr_FR'),
            array(1234.5, '1 234,5', 'fr'),
            array(1234.5, '1 234,5', 'ru'),
            array(1234.5, '1 234,5', 'fi'),
        );
    }

    /**
     * @dataProvider provideTransformationsWithGrouping
     */
    public function testTransformWithGrouping($from, $to, $locale)
    {
        \Locale::setDefault($locale);

        $transformer = new NumberToLocalizedStringTransformer(null, true);

        $this->assertSame($to, $transformer->transform($from));
    }

    public function testTransformWithPrecision()
    {
        $transformer = new NumberToLocalizedStringTransformer(2);

        $this->assertEquals('1234,50', $transformer->transform(1234.5));
        $this->assertEquals('678,92', $transformer->transform(678.916));
    }

    public function transformWithRoundingProvider()
    {
        return array(
            // towards positive infinity (1.6 -> 2, -1.6 -> -1)
            array(0, 1234.5, '1235', NumberToLocalizedStringTransformer::ROUND_CEILING),
            array(0, 1234.4, '1235', NumberToLocalizedStringTransformer::ROUND_CEILING),
            array(0, -1234.5, '-1234', NumberToLocalizedStringTransformer::ROUND_CEILING),
            array(0, -1234.4, '-1234', NumberToLocalizedStringTransformer::ROUND_CEILING),
            array(1, 123.45, '123,5', NumberToLocalizedStringTransformer::ROUND_CEILING),
            array(1, 123.44, '123,5', NumberToLocalizedStringTransformer::ROUND_CEILING),
            array(1, -123.45, '-123,4', NumberToLocalizedStringTransformer::ROUND_CEILING),
            array(1, -123.44, '-123,4', NumberToLocalizedStringTransformer::ROUND_CEILING),
            // towards negative infinity (1.6 -> 1, -1.6 -> -2)
            array(0, 1234.5, '1234', NumberToLocalizedStringTransformer::ROUND_FLOOR),
            array(0, 1234.4, '1234', NumberToLocalizedStringTransformer::ROUND_FLOOR),
            array(0, -1234.5, '-1235', NumberToLocalizedStringTransformer::ROUND_FLOOR),
            array(0, -1234.4, '-1235', NumberToLocalizedStringTransformer::ROUND_FLOOR),
            array(1, 123.45, '123,4', NumberToLocalizedStringTransformer::ROUND_FLOOR),
            array(1, 123.44, '123,4', NumberToLocalizedStringTransformer::ROUND_FLOOR),
            array(1, -123.45, '-123,5', NumberToLocalizedStringTransformer::ROUND_FLOOR),
            array(1, -123.44, '-123,5', NumberToLocalizedStringTransformer::ROUND_FLOOR),
            // away from zero (1.6 -> 2, -1.6 -> 2)
            array(0, 1234.5, '1235', NumberToLocalizedStringTransformer::ROUND_UP),
            array(0, 1234.4, '1235', NumberToLocalizedStringTransformer::ROUND_UP),
            array(0, -1234.5, '-1235', NumberToLocalizedStringTransformer::ROUND_UP),
            array(0, -1234.4, '-1235', NumberToLocalizedStringTransformer::ROUND_UP),
            array(1, 123.45, '123,5', NumberToLocalizedStringTransformer::ROUND_UP),
            array(1, 123.44, '123,5', NumberToLocalizedStringTransformer::ROUND_UP),
            array(1, -123.45, '-123,5', NumberToLocalizedStringTransformer::ROUND_UP),
            array(1, -123.44, '-123,5', NumberToLocalizedStringTransformer::ROUND_UP),
            // towards zero (1.6 -> 1, -1.6 -> -1)
            array(0, 1234.5, '1234', NumberToLocalizedStringTransformer::ROUND_DOWN),
            array(0, 1234.4, '1234', NumberToLocalizedStringTransformer::ROUND_DOWN),
            array(0, -1234.5, '-1234', NumberToLocalizedStringTransformer::ROUND_DOWN),
            array(0, -1234.4, '-1234', NumberToLocalizedStringTransformer::ROUND_DOWN),
            array(1, 123.45, '123,4', NumberToLocalizedStringTransformer::ROUND_DOWN),
            array(1, 123.44, '123,4', NumberToLocalizedStringTransformer::ROUND_DOWN),
            array(1, -123.45, '-123,4', NumberToLocalizedStringTransformer::ROUND_DOWN),
            array(1, -123.44, '-123,4', NumberToLocalizedStringTransformer::ROUND_DOWN),
            // round halves (.5) to the next even number
            array(0, 1234.6, '1235', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(0, 1234.5, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(0, 1234.4, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(0, 1233.5, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(0, 1232.5, '1232', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(0, -1234.6, '-1235', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(0, -1234.5, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(0, -1234.4, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(0, -1233.5, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(0, -1232.5, '-1232', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(1, 123.46, '123,5', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(1, 123.45, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(1, 123.44, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(1, 123.35, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(1, 123.25, '123,2', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(1, -123.46, '-123,5', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(1, -123.45, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(1, -123.44, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(1, -123.35, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            array(1, -123.25, '-123,2', NumberToLocalizedStringTransformer::ROUND_HALF_EVEN),
            // round halves (.5) away from zero
            array(0, 1234.6, '1235', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(0, 1234.5, '1235', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(0, 1234.4, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(0, -1234.6, '-1235', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(0, -1234.5, '-1235', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(0, -1234.4, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(1, 123.46, '123,5', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(1, 123.45, '123,5', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(1, 123.44, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(1, -123.46, '-123,5', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(1, -123.45, '-123,5', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            array(1, -123.44, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_UP),
            // round halves (.5) towards zero
            array(0, 1234.6, '1235', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(0, 1234.5, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(0, 1234.4, '1234', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(0, -1234.6, '-1235', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(0, -1234.5, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(0, -1234.4, '-1234', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(1, 123.46, '123,5', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(1, 123.45, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(1, 123.44, '123,4', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(1, -123.46, '-123,5', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(1, -123.45, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
            array(1, -123.44, '-123,4', NumberToLocalizedStringTransformer::ROUND_HALF_DOWN),
        );
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
     * @expectedException \Sonatra\Component\Block\Exception\TransformationFailedException
     */
    public function testTransformExpectsNumeric()
    {
        $transformer = new NumberToLocalizedStringTransformer();
        $transformer->transform('foo');
    }
}
