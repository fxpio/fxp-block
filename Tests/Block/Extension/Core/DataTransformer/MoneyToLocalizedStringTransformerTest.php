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

use Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use Symfony\Component\Intl\Util\IntlTestHelper;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MoneyToLocalizedStringTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // Since we test against "fr_FR", we need the full implementation
        IntlTestHelper::requireFullIntl($this);

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
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\TransformationFailedException
     */
    public function testTransformWithInvalidCurrency()
    {
        $transformer = new MoneyToLocalizedStringTransformer(null, null, null, null, '$', 100);
        $transformer->transform(123);
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException
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
