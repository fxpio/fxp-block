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
use Sonatra\Component\Block\Extension\Core\DataTransformer\PercentToLocalizedStringTransformer;
use Symfony\Component\Intl\Util\IntlTestHelper;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PercentToLocalizedStringTransformerTest extends TestCase
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
        $transformer = new PercentToLocalizedStringTransformer();

        $this->assertEquals('10 %', $transformer->transform(0.1));
        $this->assertEquals('15 %', $transformer->transform(0.15));
        $this->assertEquals('12 %', $transformer->transform(0.1234));
        $this->assertEquals('200 %', $transformer->transform(2));
    }

    public function testTransformEmpty()
    {
        $transformer = new PercentToLocalizedStringTransformer();

        $this->assertEquals('', $transformer->transform(null));
    }

    public function testTransformWithInteger()
    {
        $transformer = new PercentToLocalizedStringTransformer(0);

        $this->assertEquals('0 %', $transformer->transform(0.001));
        $this->assertEquals('1 %', $transformer->transform(0.01));
        $this->assertEquals('15 %', $transformer->transform(0.15));
        $this->assertEquals('16 %', $transformer->transform(0.159));
    }

    public function testTransformWithPrecision()
    {
        $transformer = new PercentToLocalizedStringTransformer(2);

        $this->assertEquals('12,34 %', $transformer->transform(0.1234));
    }

    /**
     * @expectedException \Sonatra\Component\Block\Exception\TransformationFailedException
     */
    public function testTransformExpectsNumeric()
    {
        $transformer = new PercentToLocalizedStringTransformer();
        $transformer->transform('foo');
    }
}
