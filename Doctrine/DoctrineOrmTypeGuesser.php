<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException as LegacyMappingException;
use Sonatra\Component\Block\BlockTypeGuesserInterface;
use Sonatra\Component\Block\Doctrine\Type\EntityType;
use Sonatra\Component\Block\Extension\Core\Type\CheckboxType;
use Sonatra\Component\Block\Extension\Core\Type\CollectionType;
use Sonatra\Component\Block\Extension\Core\Type\DateTimeType;
use Sonatra\Component\Block\Extension\Core\Type\DateType;
use Sonatra\Component\Block\Extension\Core\Type\IntegerType;
use Sonatra\Component\Block\Extension\Core\Type\NumberType;
use Sonatra\Component\Block\Extension\Core\Type\TextareaType;
use Sonatra\Component\Block\Extension\Core\Type\TextType;
use Sonatra\Component\Block\Extension\Core\Type\TimeType;
use Sonatra\Component\Block\Guess\Guess;
use Sonatra\Component\Block\Guess\TypeGuess;

class DoctrineOrmTypeGuesser implements BlockTypeGuesserInterface
{
    protected $registry;
    private $cache;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->cache = array();
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        if (!$ret = $this->getMetadata($class)) {
            return new TypeGuess(TextType::class, array(), Guess::LOW_CONFIDENCE);
        }

        /* @var ClassMetadataInfo $metadata */
        list($metadata, $name) = $ret;

        if ($metadata->hasAssociation($property)) {
            $multiple = $metadata->isCollectionValuedAssociation($property);
            $mapping = $metadata->getAssociationMapping($property);

            return new TypeGuess(EntityType::class, array('em' => $name, 'class' => $mapping['targetEntity'], 'multiple' => $multiple), Guess::HIGH_CONFIDENCE);
        }

        switch ($metadata->getTypeOfField($property)) {
            case 'array':
                return new TypeGuess(CollectionType::class, array(), Guess::MEDIUM_CONFIDENCE);

            case 'boolean':
                return new TypeGuess(CheckboxType::class, array(), Guess::HIGH_CONFIDENCE);

            case 'datetime':
            case 'vardatetime':
            case 'datetimetz':
                return new TypeGuess(DateTimeType::class, array(), Guess::HIGH_CONFIDENCE);

            case 'date':
                return new TypeGuess(DateType::class, array(), Guess::HIGH_CONFIDENCE);

            case 'time':
                return new TypeGuess(TimeType::class, array(), Guess::HIGH_CONFIDENCE);

            case 'decimal':
            case 'float':
                return new TypeGuess(NumberType::class, array(), Guess::MEDIUM_CONFIDENCE);

            case 'integer':
            case 'bigint':
            case 'smallint':
                return new TypeGuess(IntegerType::class, array(), Guess::MEDIUM_CONFIDENCE);

            case 'string':
                return new TypeGuess(TextType::class, array(), Guess::MEDIUM_CONFIDENCE);

            case 'text':
                return new TypeGuess(TextareaType::class, array(), Guess::MEDIUM_CONFIDENCE);

            default:
                return new TypeGuess(TextType::class, array(), Guess::LOW_CONFIDENCE);
        }
    }

    /**
     * Get class metadata.
     *
     * @param string $class
     *
     * @return ClassMetadata|null
     */
    protected function getMetadata($class)
    {
        if (array_key_exists($class, $this->cache)) {
            return $this->cache[$class];
        }

        $this->cache[$class] = null;

        foreach ($this->registry->getManagers() as $name => $em) {
            try {
                return $this->cache[$class] = array($em->getClassMetadata($class), $name);
            } catch (MappingException $e) {
                // not an entity or mapped super class
            } catch (LegacyMappingException $e) {
                // not an entity or mapped super class, using Doctrine ORM 2.2
            }
        }

        return $this->cache[$class];
    }
}
