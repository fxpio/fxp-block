<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Doctrine\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Sonatra\Component\Block\AbstractType;
use Sonatra\Component\Block\BlockBuilderInterface;
use Sonatra\Component\Block\Doctrine\DataTransformer\CollectionToArrayTransformer;
use Sonatra\Component\Block\Doctrine\DataTransformer\EntityToArrayTransformer;
use Sonatra\Component\Block\Exception\RuntimeException;
use Sonatra\Component\Block\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;
use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class DoctrineType extends AbstractType
{
    /**
     * @var ChoiceListFactoryInterface
     */
    protected $choiceListFactory;
    /**
     * @var DoctrineChoiceLoader[]
     */
    private $choiceLoaders = array();

    /**
     * Constructor.
     *
     * @param ManagerRegistry            $registry          The doctrine instance
     * @param PropertyAccessorInterface  $propertyAccessor  The property accessor
     * @param ChoiceListFactoryInterface $choiceListFactory The choice list factory
     */
    public function __construct(ManagerRegistry $registry, PropertyAccessorInterface $propertyAccessor = null, ChoiceListFactoryInterface $choiceListFactory = null)
    {
        $this->registry = $registry;
        $this->choiceListFactory = $choiceListFactory ?: new PropertyAccessDecorator(new DefaultChoiceListFactory(), $propertyAccessor);
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder->resetViewTransformers();

        if ($options['multiple']) {
            $builder->addViewTransformer(new CollectionToArrayTransformer(), true);
        } else {
            $builder->addViewTransformer(new EntityToArrayTransformer(), true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $registry = $this->registry;
        $choiceListFactory = $this->choiceListFactory;
        $choiceLoaders = &$this->choiceLoaders;
        $type = $this;

        $choiceLoader = function (Options $options, $value) use ($choiceListFactory, &$choiceLoaders, $type) {
            if (null === $options['choices']) {
                $hash = null;
                $qbParts = null;
                // If there is no QueryBuilder we can safely cache DoctrineChoiceLoader,
                // also if concrete Type can return important QueryBuilder parts to generate
                // hash key we go for it as well
                if (!$options['query_builder'] || false !== ($qbParts = $type->getQueryBuilderPartsForCachingHash($options['query_builder']))) {
                    $hash = CachingFactoryDecorator::generateHash(array(
                        $options['em'],
                        $options['class'],
                        $qbParts,
                    ));
                    if (isset($choiceLoaders[$hash])) {
                        return $choiceLoaders[$hash];
                    }
                }

                if (null !== $options['query_builder']) {
                    $entityLoader = $type->getLoader($options['em'], $options['query_builder'], $options['class']);
                } else {
                    $queryBuilder = $options['em']->getRepository($options['class'])->createQueryBuilder('e');
                    $entityLoader = $type->getLoader($options['em'], $queryBuilder, $options['class']);
                }

                $doctrineChoiceLoader = new DoctrineChoiceLoader(
                    $choiceListFactory,
                    $options['em'],
                    $options['class'],
                    $options['id_reader'],
                    $entityLoader
                );

                if ($hash !== null) {
                    $choiceLoaders[$hash] = $doctrineChoiceLoader;
                }

                return $doctrineChoiceLoader;
            }

            return $value;
        };

        $choiceLabel = function (Options $options) {
            // BC: use __toString() by default
            return array(__CLASS__, 'createChoiceLabel');
        };

        $choiceName = function (Options $options) {
            /** @var IdReader $idReader */
            $idReader = $options['id_reader'];

            // If the object has a single-column, numeric ID, use that ID as
            // field name. We can only use numeric IDs as names, as we cannot
            // guarantee that a non-numeric ID contains a valid form name
            // Otherwise, an incrementing integer is used as name automatically
            return $idReader->isIntId()
                ? array(__CLASS__, 'createChoiceName')
                : null;
        };

        // The choices are always indexed by ID (see "choices" normalizer
        // and DoctrineChoiceLoader), unless the ID is composite. Then they
        // are indexed by an incrementing integer.
        // Use the ID/incrementing integer as choice value.
        $choiceValue = function (Options $options) {
            /** @var IdReader $idReader */
            $idReader = $options['id_reader'];

            // If the entity has a single-column ID, use that ID as value
            // Otherwise, an incrementing integer is used as value automatically
            return $idReader->isIntId()
                ? array($idReader, 'getIdValue')
                : null;
        };

        $emNormalizer = function (Options $options, $em) use ($registry) {
            /* @var ManagerRegistry $registry */
            if (null !== $em) {
                if ($em instanceof ObjectManager) {
                    return $em;
                }

                return $registry->getManager($em);
            }

            $em = $registry->getManagerForClass($options['class']);

            if (null === $em) {
                throw new RuntimeException(sprintf(
                    'Class "%s" seems not to be a managed Doctrine entity. '.
                    'Did you forget to map it?',
                    $options['class']
                ));
            }

            return $em;
        };

        // Invoke the query builder closure so that we can cache choice lists
        // for equal query builders
        $queryBuilderNormalizer = function (Options $options, $queryBuilder) {
            if (is_callable($queryBuilder)) {
                $queryBuilder = call_user_func($queryBuilder, $options['em']->getRepository($options['class']));
            }

            return $queryBuilder;
        };

        // Set the "id_reader" option via the normalizer. This option is not
        // supposed to be set by the user.
        $idReaderNormalizer = function (Options $options) use (&$idReaders) {
            $hash = CachingFactoryDecorator::generateHash(array(
                $options['em'],
                $options['class'],
            ));

            // The ID reader is a utility that is needed to read the object IDs
            // when generating the field values. The callback generating the
            // field values has no access to the object manager or the class
            // of the field, so we store that information in the reader.
            // The reader is cached so that two choice lists for the same class
            // (and hence with the same reader) can successfully be cached.
            if (!isset($idReaders[$hash])) {
                $classMetadata = $options['em']->getClassMetadata($options['class']);
                $idReaders[$hash] = new IdReader($options['em'], $classMetadata);
            }

            return $idReaders[$hash];
        };

        $resolver->setDefaults(array(
                'em' => null,
                'query_builder' => null,
                'choices' => null,
                'choice_loader' => $choiceLoader,
                'choice_label' => $choiceLabel,
                'choice_name' => $choiceName,
                'choice_value' => $choiceValue,
                'id_reader' => null, // internal
                'choice_translation_domain' => false,
        ));

        $resolver->setRequired(array('class'));

        $resolver->setNormalizer('em', $emNormalizer);
        $resolver->setNormalizer('query_builder', $queryBuilderNormalizer);
        $resolver->setNormalizer('id_reader', $idReaderNormalizer);

        $resolver->setAllowedTypes('em', array('null', 'string', 'Doctrine\Common\Persistence\ObjectManager'));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * Gets important parts from QueryBuilder that will allow to cache its results.
     * For instance in ORM two query builders with an equal SQL string and
     * equal parameters are considered to be equal.
     *
     * @param object $queryBuilder
     *
     * @return array|false Array with important QueryBuilder parts or false if
     *                     they can't be determined
     *
     * @internal This method is public to be usable as callback. It should not
     *           be used in user code
     */
    public function getQueryBuilderPartsForCachingHash($queryBuilder)
    {
        return false;
    }

    /**
     * Return the default loader object.
     *
     * @param ObjectManager $manager
     * @param mixed         $queryBuilder
     * @param string        $class
     *
     * @return EntityLoaderInterface
     */
    abstract public function getLoader(ObjectManager $manager, $queryBuilder, $class);

    /**
     * Creates the label for a choice.
     *
     * For backwards compatibility, objects are cast to strings by default.
     *
     * @param object $choice The object
     *
     * @return string The string representation of the object
     *
     * @internal This method is public to be usable as callback. It should not
     *           be used in user code
     */
    public static function createChoiceLabel($choice)
    {
        return (string) $choice;
    }

    /**
     * Creates the field name for a choice.
     *
     * This method is used to generate field names if the underlying object has
     * a single-column integer ID. In that case, the value of the field is
     * the ID of the object. That ID is also used as field name.
     *
     * @param object     $choice The object
     * @param int|string $key    The choice key
     * @param string     $value  The choice value. Corresponds to the object's
     *                           ID here
     *
     * @return string The field name
     *
     * @internal This method is public to be usable as callback. It should not
     *           be used in user code
     */
    public static function createChoiceName($choice, $key, $value)
    {
        return str_replace('-', '_', (string) $value);
    }
}
