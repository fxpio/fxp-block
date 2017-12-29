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

use Fxp\Component\Block\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Intl\Util\IntlTestHelper;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MoneyToLocalizedStringTransformerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // Since we test against "fr_FR", we need the full implementation
        IntlTestHelper::requireFullIntl($this, false);

        \Locale::setDefault('fr_FR');
    }

    public function testTransform()
    {
        $transformer = new MoneyToLocalizedStringTransformer(null, null, null, null, null, 100);

        $this->assertEquals('1,23 €', $transformer->transform(123));
    }

    public function testTransformWithCurrency()
    {
        $transformer = new MoneyToLocalizedStringTransformer(null, null, null, null, 'USD', 100);

        $this->assertEquals('1,23 $US', $transformer->transform(123));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\TransformationFailedException
     */
    public function testTransformWithInvalidCurrency()
    {
        $transformer = new MoneyToLocalizedStringTransformer(null, null, null, null, '$', 100);
        $transformer->transform(123);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testTransformExpectsNumeric()
    {
        $transformer = new MoneyToLocalizedStringTransformer(null, null, null, null, null, 100);

        $transformer->transform('abcd');
    }

    public function testTransformEmpty()
    {
        $transformer = new MoneyToLocalizedStringTransformer();

        $this->assertSame('', $transformer->transform(null));
    }
}
