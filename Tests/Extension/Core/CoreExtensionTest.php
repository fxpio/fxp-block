<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Extension\Core;

use Fxp\Component\Block\Extension\Core\CoreExtension;
use Fxp\Component\Block\Extension\Core\Type\BirthdayType;
use Fxp\Component\Block\Extension\Core\Type\BlockType;
use Fxp\Component\Block\Extension\Core\Type\CheckboxType;
use Fxp\Component\Block\Extension\Core\Type\ChoiceType;
use Fxp\Component\Block\Extension\Core\Type\CollectionType;
use Fxp\Component\Block\Extension\Core\Type\CountryType;
use Fxp\Component\Block\Extension\Core\Type\DateTimeType;
use Fxp\Component\Block\Extension\Core\Type\DateType;
use Fxp\Component\Block\Extension\Core\Type\EmailType;
use Fxp\Component\Block\Extension\Core\Type\HiddenType;
use Fxp\Component\Block\Extension\Core\Type\IntegerType;
use Fxp\Component\Block\Extension\Core\Type\LanguageType;
use Fxp\Component\Block\Extension\Core\Type\LocaleType;
use Fxp\Component\Block\Extension\Core\Type\MoneyType;
use Fxp\Component\Block\Extension\Core\Type\NumberType;
use Fxp\Component\Block\Extension\Core\Type\PasswordType;
use Fxp\Component\Block\Extension\Core\Type\PercentType;
use Fxp\Component\Block\Extension\Core\Type\RadioType;
use Fxp\Component\Block\Extension\Core\Type\RepeatedType;
use Fxp\Component\Block\Extension\Core\Type\TextareaType;
use Fxp\Component\Block\Extension\Core\Type\TextType;
use Fxp\Component\Block\Extension\Core\Type\TimeType;
use Fxp\Component\Block\Extension\Core\Type\TimezoneType;
use Fxp\Component\Block\Extension\Core\Type\UrlType;
use Fxp\Component\Block\Tests\Fixtures\Type\FooType;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
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
        $this->assertInstanceOf('Fxp\Component\Block\BlockExtensionInterface', $this->extension);
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
