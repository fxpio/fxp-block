<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\Core\Type;

use Fxp\Component\Block\AbstractType;
use Fxp\Component\Block\BlockBuilderInterface;
use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockView;
use Fxp\Component\Block\Exception\InvalidConfigurationException;
use Fxp\Component\Block\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockType extends AbstractType
{
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * Constructor.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $isDataOptionSet = array_key_exists('data', $options);

        $builder
            ->setAutoInitialize($options['auto_initialize'])
            ->setEmptyData($options['empty_data'])
            ->setEmptyMessage($options['empty_message'])
            ->setMapped(\is_string($options['property_path']) ? true : $options['mapped'])
            ->setPropertyPath(\is_string($options['property_path']) ? $options['property_path'] : null)
            ->setInheritData($options['inherit_data'])
            ->setCompound($options['compound'])
            ->setData($isDataOptionSet ? $options['data'] : null)
            ->setDataMapper($options['compound'] ? new PropertyPathMapper($this->propertyAccessor) : null)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        $name = $block->getName();
        $blockName = $options['block_name'] ?: $block->getName();
        $translationDomain = $options['translation_domain'];
        $labelFormat = $options['label_format'];
        $id = $name;

        if ($view->parent) {
            $uniqueBlockPrefix = sprintf('%s_%s', $view->parent->vars['unique_block_prefix'], $blockName);

            if ($options['chained_block']) {
                $id = sprintf('%s_%s', $view->parent->vars['id'], $name);
            }

            if (null === $translationDomain) {
                $translationDomain = $view->parent->vars['translation_domain'];
            }

            if (!$labelFormat) {
                $labelFormat = $view->parent->vars['label_format'];
            }
        } else {
            $uniqueBlockPrefix = '_'.$blockName;

            // Strip leading underscores and digits. These are allowed in
            // block names, but not in HTML4 ID attributes.
            // http://www.w3.org/TR/html401/struct/global.html#adef-id
            $id = ltrim($id, '_0123456789');
        }

        $blockPrefixes = [];
        for ($type = $block->getConfig()->getType(); null !== $type; $type = $type->getParent()) {
            array_unshift($blockPrefixes, $type->getBlockPrefix());
        }
        $blockPrefixes[] = $uniqueBlockPrefix;

        if (true === $translationDomain) {
            $translationDomain = 'messages';
        }

        $view->vars = array_replace($view->vars, [
                'block' => $view,
                'id' => $id,
                'name' => $name,
                'rendered' => $options['rendered'],
                'render_id' => $options['render_id'],
                'row' => $options['row'],
                'row_label' => $options['row_label'],
                'value' => $block->getViewData(),
                'data' => $block->getNormData(),
                'label' => $options['label'],
                'label_format' => $labelFormat,
                'attr' => $options['attr'],
                'label_attr' => $options['label_attr'],
                'compound' => $block->getConfig()->getCompound(),
                'wrapped' => $options['wrapped'],
                'block_prefixes' => $blockPrefixes,
                'unique_block_prefix' => $uniqueBlockPrefix,
                'translation_domain' => $translationDomain,
                // Using the block name here speeds up performance in collection
                // blocks, where each entry has the same full block name.
                // Including the type is important too, because if rows of a
                // collection block have different types (dynamically), they should
                // be rendered differently.
                'cache_key' => $uniqueBlockPrefix.'_'.$block->getConfig()->getType()->getBlockPrefix(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options)
    {
        foreach ($view->children as $name => $child) {
            if (!$child->vars['rendered']) {
                unset($view->children[$name]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // Derive "data_class" option from passed "data" object
        $dataClass = function (Options $options) {
            return isset($options['data']) && \is_object($options['data']) ? \get_class($options['data']) : null;
        };

        // Derive "empty_data" closure from "data_class" option
        $emptyData = function (Options $options) {
            return function (BlockInterface $block) use ($options) {
                $class = $options['data_class'];
                $empty = null;

                if (null !== $class && null === $block->getConfig()->getData()) {
                    $ref = new \ReflectionClass($class);
                    $constructor = $ref->getConstructor();

                    if (null !== $constructor && $constructor->getNumberOfParameters() > 0) {
                        throw new InvalidConfigurationException('The option can not create an object with a constructor. Override this option with the creation of a custom object');
                    }

                    return $ref->newInstance();
                }

                return $empty;
            };
        };

        // If data is given, the block is locked to that data
        // (independent of its value)
        $resolver->setDefined('data');

        $resolver->setDefaults([
                'block_name' => null,
                'id' => null,
                'rendered' => true,
                'render_id' => false,
                'row' => false,
                'row_label' => false,
                'chained_block' => false,
                'data_class' => $dataClass,
                'empty_data' => $emptyData,
                'empty_message' => null,
                'property_path' => null,
                'mapped' => false,
                'label' => null,
                'label_format' => null,
                'attr' => [],
                'label_attr' => [],
                'inherit_data' => false,
                'compound' => true,
                'wrapped' => true,
                'translation_domain' => null,
                'auto_initialize' => true,
        ]);

        $resolver->setAllowedTypes('rendered', 'bool');
        $resolver->setAllowedTypes('empty_message', ['null', 'string']);
        $resolver->setAllowedTypes('attr', 'array');
        $resolver->setAllowedTypes('label_attr', 'array');
        $resolver->setAllowedTypes('auto_initialize', 'bool');
        $resolver->setAllowedTypes('translation_domain', ['null', 'bool', 'string']);

        $resolver->setNormalizer('block_name', function (Options $options, $value = null) {
            if (isset($options['id'])) {
                $value = $options['id'];
            }

            return $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        // return null
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'block';
    }
}
