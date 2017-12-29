<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\Validator;

use Fxp\Component\Block\BlockTypeGuesserInterface;
use Fxp\Component\Block\Extension\Core\Type\CheckboxType;
use Fxp\Component\Block\Extension\Core\Type\CollectionType;
use Fxp\Component\Block\Extension\Core\Type\CountryType;
use Fxp\Component\Block\Extension\Core\Type\DateTimeType;
use Fxp\Component\Block\Extension\Core\Type\DateType;
use Fxp\Component\Block\Extension\Core\Type\EmailType;
use Fxp\Component\Block\Extension\Core\Type\IntegerType;
use Fxp\Component\Block\Extension\Core\Type\LanguageType;
use Fxp\Component\Block\Extension\Core\Type\LocaleType;
use Fxp\Component\Block\Extension\Core\Type\NumberType;
use Fxp\Component\Block\Extension\Core\Type\TextType;
use Fxp\Component\Block\Extension\Core\Type\TimeType;
use Fxp\Component\Block\Extension\Core\Type\UrlType;
use Fxp\Component\Block\Guess\Guess;
use Fxp\Component\Block\Guess\TypeGuess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\MemberMetadata;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ValidatorTypeGuesser implements BlockTypeGuesserInterface
{
    protected $metadataFactory;

    /**
     * Constructor.
     *
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        $guesser = $this;

        return $this->guess($class, $property, function (Constraint $constraint) use ($guesser) {
            return $guesser->guessTypeForConstraint($constraint);
        });
    }

    /**
     * Guesses a field class name for a given constraint.
     *
     * @param Constraint $constraint The constraint to guess for
     *
     * @return TypeGuess The guessed field class and options
     */
    public function guessTypeForConstraint(Constraint $constraint)
    {
        $empty = null;

        switch (get_class($constraint)) {
            case 'Symfony\Component\Validator\Constraints\Type':
                /* @var \Symfony\Component\Validator\Constraints\Type $constraint */
                switch ($constraint->type) {
                    case 'array':
                        return new TypeGuess(CollectionType::class, [], Guess::MEDIUM_CONFIDENCE);
                    case 'boolean':
                    case 'bool':
                        return new TypeGuess(CheckboxType::class, [], Guess::MEDIUM_CONFIDENCE);

                    case 'double':
                    case 'float':
                    case 'numeric':
                    case 'real':
                        return new TypeGuess(NumberType::class, [], Guess::MEDIUM_CONFIDENCE);

                    case 'integer':
                    case 'int':
                    case 'long':
                        return new TypeGuess(IntegerType::class, [], Guess::MEDIUM_CONFIDENCE);

                    case '\DateTime':
                        return new TypeGuess(DateType::class, [], Guess::MEDIUM_CONFIDENCE);

                    case 'string':
                        return new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE);
                }
                break;

            case 'Symfony\Component\Validator\Constraints\Country':
                return new TypeGuess(CountryType::class, [], Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Date':
                return new TypeGuess(DateType::class, ['input' => 'string'], Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\DateTime':
                return new TypeGuess(DateTimeType::class, ['input' => 'string'], Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Email':
                return new TypeGuess(EmailType::class, [], Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\File':
            case 'Symfony\Component\Validator\Constraints\Image':
                return new TypeGuess(TextType::class, [], Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Language':
                return new TypeGuess(LanguageType::class, [], Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Locale':
                return new TypeGuess(LocaleType::class, [], Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Time':
                return new TypeGuess(TimeType::class, ['input' => 'string'], Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Url':
                return new TypeGuess(UrlType::class, [], Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Ip':
                return new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Length':
            case 'Symfony\Component\Validator\Constraints\Regex':
                return new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Range':
                return new TypeGuess(NumberType::class, [], Guess::LOW_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Count':
                return new TypeGuess(CollectionType::class, [], Guess::LOW_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\IsTrue':
            case 'Symfony\Component\Validator\Constraints\IsFalse':
                return new TypeGuess(CheckboxType::class, [], Guess::MEDIUM_CONFIDENCE);
        }

        return $empty;
    }

    /**
     * Iterates over the constraints of a property, executes a constraints on
     * them and returns the best guess.
     *
     * @param string   $class    The class to read the constraints block
     * @param string   $property The property for which to find constraints
     * @param \Closure $closure  The closure that returns a guess for a given constraint
     *
     * @return Guess The guessed value with the highest confidence
     */
    protected function guess($class, $property, \Closure $closure)
    {
        $guesses = [];
        /* @var ClassMetadata $classMetadata */
        $classMetadata = $this->metadataFactory->getMetadataFor($class);

        if ($classMetadata->hasPropertyMetadata($property)) {
            /* @var MemberMetadata[] $memberMetadatas */
            $memberMetadatas = $classMetadata->getPropertyMetadata($property);

            foreach ($memberMetadatas as $memberMetadata) {
                $constraints = $memberMetadata->getConstraints();

                foreach ($constraints as $constraint) {
                    if ($guess = $closure($constraint)) {
                        $guesses[] = $guess;
                    }
                }
            }
        }

        return Guess::getBestGuess($guesses);
    }
}
