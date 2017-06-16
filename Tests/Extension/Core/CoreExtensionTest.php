<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Extension\Core;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Block\Extension\Core\CoreExtension;
use Sonatra\Component\Block\Extension\Core\Type\BirthdayType;
use Sonatra\Component\Block\Extension\Core\Type\BlockType;
use Sonatra\Component\Block\Extension\Core\Type\CheckboxType;
use Sonatra\Component\Block\Extension\Core\Type\ChoiceType;
use Sonatra\Component\Block\Extension\Core\Type\CollectionType;
use Sonatra\Component\Block\Extension\Core\Type\CountryType;
use Sonatra\Component\Block\Extension\Core\Type\DateTimeType;
use Sonatra\Component\Block\Extension\Core\Type\DateType;
use Sonatra\Component\Block\Extension\Core\Type\EmailType;
use Sonatra\Component\Block\Extension\Core\Type\HiddenType;
use Sonatra\Component\Block\Extension\Core\Type\IntegerType;
use Sonatra\Component\Block\Extension\Core\Type\LanguageType;
use Sonatra\Component\Block\Extension\Core\Type\LocaleType;
use Sonatra\Component\Block\Extension\Core\Type\MoneyType;
use Sonatra\Component\Block\Extension\Core\Type\NumberType;
use Sonatra\Component\Block\Extension\Core\Type\PasswordType;
use Sonatra\Component\Block\Extension\Core\Type\PercentType;
use Sonatra\Component\Block\Extension\Core\Type\RadioType;
use Sonatra\Component\Block\Extension\Core\Type\RepeatedType;
use Sonatra\Component\Block\Extension\Core\Type\TextareaType;
use Sonatra\Component\Block\Extension\Core\Type\TextType;
use Sonatra\Component\Block\Extension\Core\Type\TimeType;
use Sonatra\Component\Block\Extension\Core\Type\TimezoneType;
use Sonatra\Component\Block\Extension\Core\Type\UrlType;
use Sonatra\Component\Block\Tests\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CoreExtensionTest extends TestCase
{
    /**
     * @var CoreExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new CoreExtension();
    }

    protected function tearDown()
    {
        $this->extension = null;
    }

    public function testCoreExtension()
    {
        $this->assertInstanceOf('Sonatra\Component\Block\BlockExtensionInterface', $this->extension);
        $this->assertFalse($this->extension->hasType(FooType::class));
        $this->assertFalse($this->extension->hasTypeExtensions(FooType::class));

        $this->assertTrue($this->extension->hasType(BlockType::class));
        $this->assertTrue($this->extension->hasType(BirthdayType::class));
        $this->assertTrue($this->extension->hasType(CheckboxType::class));
        $this->assertTrue($this->extension->hasType(ChoiceType::class));
        $this->assertTrue($this->extension->hasType(CollectionType::class));
        $this->assertTrue($this->extension->hasType(CountryType::class));
        $this->assertTrue($this->extension->hasType(DateType::class));
        $this->assertTrue($this->extension->hasType(DateTimeType::class));
        $this->assertTrue($this->extension->hasType(EmailType::class));
        $this->assertTrue($this->extension->hasType(HiddenType::class));
        $this->assertTrue($this->extension->hasType(IntegerType::class));
        $this->assertTrue($this->extension->hasType(LanguageType::class));
        $this->assertTrue($this->extension->hasType(LocaleType::class));
        $this->assertTrue($this->extension->hasType(MoneyType::class));
        $this->assertTrue($this->extension->hasType(NumberType::class));
        $this->assertTrue($this->extension->hasType(PasswordType::class));
        $this->assertTrue($this->extension->hasType(PercentType::class));
        $this->assertTrue($this->extension->hasType(RadioType::class));
        $this->assertTrue($this->extension->hasType(RepeatedType::class));
        $this->assertTrue($this->extension->hasType(TextareaType::class));
        $this->assertTrue($this->extension->hasType(TextType::class));
        $this->assertTrue($this->extension->hasType(TimeType::class));
        $this->assertTrue($this->extension->hasType(TimezoneType::class));
        $this->assertTrue($this->extension->hasType(UrlType::class));
    }
}
