<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Extension\Validator;

use Fxp\Component\Block\Extension\Validator\ValidatorTypeGuesser;
use Fxp\Component\Block\Guess\Guess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\IsFalse;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Language;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Locale;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ValidatorTypeGuesserTest extends TestCase
{
    /**
     * @var ValidatorTypeGuesser
     */
    protected $typeGuesser;

    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    public function setUp()
    {
        if (!class_exists('Symfony\Component\Validator\Validator\RecursiveValidator')) {
            $this->markTestSkipped('The "Validator" component is not available');
        }

        $this->metadataFactory = $this->getMockBuilder('Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface')->getMock();

        /* @var MetadataFactoryInterface $metadataFactory */
        $metadataFactory = $this->metadataFactory;

        $this->typeGuesser = new ValidatorTypeGuesser($metadataFactory);
    }

    /**
     * @dataProvider dataProviderTestGetGuessTypeForConstraint
     */
    public function testGetGuessTypeForConstraint($type, $confidence)
    {
        $constraint = new Type($type);
        $result = $this->typeGuesser->guessTypeForConstraint($constraint);

        $this->assertInstanceOf('Fxp\Component\Block\Guess\TypeGuess', $result);
        $this->assertEquals($confidence, $result->getConfidence());
    }

    public function testGetGuessTypeForInvalidConstraint()
    {
        $constraint = new Type(42);
        $result = $this->typeGuesser->guessTypeForConstraint($constraint);

        $this->assertNull($result);
    }

    /**
     * @dataProvider dataProviderTestGetGuessTypeForSpecificConstraint
     */
    public function testGetGuessTypeForSpecificConstraint($constraint, $confidence)
    {
        $result = $this->typeGuesser->guessTypeForConstraint($constraint);

        $this->assertInstanceOf('Fxp\Component\Block\Guess\TypeGuess', $result);
        $this->assertEquals($confidence, $result->getConfidence());
    }

    public function testGetGuessType()
    {
        $metadataFactory = new LazyLoadingMetadataFactory();
        $class = 'Fxp\Component\Block\Tests\Fixtures\Object\Foo';
        /* @var ClassMetadata $classMetadata */
        $classMetadata = $metadataFactory->getMetadataFor($class);
        $classMetadata->addPropertyConstraint('bar', new Type(['type' => 'string']));

        $typeGuesser = new ValidatorTypeGuesser($metadataFactory);
        $result = $typeGuesser->guessType($class, 'bar');

        $this->assertInstanceOf('Fxp\Component\Block\Guess\TypeGuess', $result);
        $this->assertEquals(Guess::LOW_CONFIDENCE, $result->getConfidence());
    }

    public static function dataProviderTestGetGuessTypeForConstraint()
    {
        return [
            ['array', Guess::MEDIUM_CONFIDENCE],
            ['bool', Guess::MEDIUM_CONFIDENCE],
            ['double', Guess::MEDIUM_CONFIDENCE],
            ['float', Guess::MEDIUM_CONFIDENCE],
            ['numeric', Guess::MEDIUM_CONFIDENCE],
            ['real', Guess::MEDIUM_CONFIDENCE],
            ['integer', Guess::MEDIUM_CONFIDENCE],
            ['long', Guess::MEDIUM_CONFIDENCE],
            ['\DateTime', Guess::MEDIUM_CONFIDENCE],
            ['string', Guess::LOW_CONFIDENCE],
        ];
    }

    public static function dataProviderTestGetGuessTypeForSpecificConstraint()
    {
        return [
            [new Country(), Guess::HIGH_CONFIDENCE],
            [new Date(), Guess::HIGH_CONFIDENCE],
            [new DateTime(), Guess::HIGH_CONFIDENCE],
            [new Email(), Guess::HIGH_CONFIDENCE],
            [new File(), Guess::HIGH_CONFIDENCE],
            [new Image(), Guess::HIGH_CONFIDENCE],
            [new Language(), Guess::HIGH_CONFIDENCE],
            [new Locale(), Guess::HIGH_CONFIDENCE],
            [new Time(), Guess::HIGH_CONFIDENCE],
            [new Url(), Guess::HIGH_CONFIDENCE],
            [new Ip(), Guess::MEDIUM_CONFIDENCE],
            [new Length(['min' => 0, 'max' => 255]), Guess::LOW_CONFIDENCE],
            [new Regex(['pattern' => '*']), Guess::LOW_CONFIDENCE],
            [new Range(['min' => 0, 'max' => 255]), Guess::LOW_CONFIDENCE],
            [new Count(['min' => 0, 'max' => 255]), Guess::LOW_CONFIDENCE],
            [new IsTrue(), Guess::MEDIUM_CONFIDENCE],
            [new IsFalse(), Guess::MEDIUM_CONFIDENCE],
        ];
    }
}
