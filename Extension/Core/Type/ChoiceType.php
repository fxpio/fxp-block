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
use Fxp\Component\Block\Extension\Core\DataTransformer\ChoicesToValuesTransformer;
use Fxp\Component\Block\Extension\Core\DataTransformer\ChoiceToValueTransformer;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceListView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ChoiceType extends AbstractType
{
    /**
     * @var ChoiceListFactoryInterface
     */
    private $choiceListFactory;

    /**
     * Constructor.
     *
     * @param ChoiceListFactoryInterface|null $choiceListFactory
     */
    public function __construct(ChoiceListFactoryInterface $choiceListFactory = null)
    {
        $this->choiceListFactory = $choiceListFactory ?: new PropertyAccessDecorator(new DefaultChoiceListFactory());
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $choiceList = $this->createChoiceList($options);
        $builder->setAttribute('choice_list', $choiceList);

        if ($options['multiple']) {
            $builder->addViewTransformer(new ChoicesToValuesTransformer($choiceList));
        } else {
            $builder->addViewTransformer(new ChoiceToValueTransformer($choiceList));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        /* @var ChoiceListView $choiceListView */
        $choiceListView = $block->getConfig()->hasAttribute('choice_list_view')
            ? $block->getConfig()->getAttribute('choice_list_view')
            : $this->createChoiceListView($block->getConfig()->getAttribute('choice_list'), $options);
        $emptyValue = $options['empty_value'];

        if ($emptyValue instanceof \Closure) {
            $emptyValue = $emptyValue($block);
        }

        $view->vars = array_replace($view->vars, array(
            'multiple' => $options['multiple'],
            'expanded' => $options['expanded'],
            'selected_choices' => $this->getSelectedChoices($choiceListView->choices, (array) $view->vars['value']),
            'empty_value' => $emptyValue,
            'choice_translation_domain' => $options['choice_translation_domain'],
            'inline' => $options['inline'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $compound = function (Options $options) {
            return $options['expanded'];
        };

        $emptyValue = function (Options $options) {
            return $options['empty_data'];
        };

        $choiceTranslationDomainNormalizer = function (Options $options, $choiceTranslationDomain) {
            if (true === $choiceTranslationDomain) {
                return $options['translation_domain'];
            }

            return $choiceTranslationDomain;
        };

        $resolver->setDefaults(array(
                'inline' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => array(),
                'choice_loader' => null,
                'choice_label' => null,
                'choice_name' => null,
                'choice_value' => null,
                'choice_attr' => null,
                'preferred_choices' => array(),
                'group_by' => null,
                'empty_value' => $emptyValue,
                'compound' => $compound,
                'data_class' => null,
                'block_name' => 'entry',
                'choice_translation_domain' => true,
        ));

        $resolver->setAllowedTypes('choices', array('null', 'array', '\Traversable'));
        $resolver->setAllowedTypes('choice_translation_domain', array('null', 'bool', 'string'));
        $resolver->setAllowedTypes('choice_loader', array('null', 'Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface'));
        $resolver->setAllowedTypes('choice_label', array('null', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));
        $resolver->setAllowedTypes('choice_name', array('null', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));
        $resolver->setAllowedTypes('choice_value', array('null', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));
        $resolver->setAllowedTypes('choice_attr', array('null', 'array', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));
        $resolver->setAllowedTypes('preferred_choices', array('array', '\Traversable', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));
        $resolver->setAllowedTypes('group_by', array('null', 'array', '\Traversable', 'string', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));

        $resolver->setNormalizer('choice_translation_domain', $choiceTranslationDomainNormalizer);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FieldType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'choice';
    }

    /**
     * Create the choice list.
     *
     * @param array $options The block options
     *
     * @return ChoiceListInterface
     */
    protected function createChoiceList(array $options)
    {
        if (null !== $options['choice_loader']) {
            return $this->choiceListFactory->createListFromLoader(
                $options['choice_loader'],
                $options['choice_value']
            );
        }

        // Harden against NULL values (like in EntityType and ModelType)
        $choices = null !== $options['choices'] ? $options['choices'] : array();

        return $this->choiceListFactory->createListFromChoices($choices, $options['choice_value']);
    }

    /**
     * Get the selected choices.
     *
     * @param ChoiceGroupView[]|ChoiceView[] $choiceViews The choice views
     * @param string[]                       $values      The selected values
     *
     * @return ChoiceView[] The selected choices
     */
    protected function getSelectedChoices($choiceViews, array $values)
    {
        $selectedChoices = array();

        foreach ($choiceViews as $index => $choiceView) {
            if ($choiceView instanceof ChoiceGroupView) {
                $selectedChoices = array_merge($selectedChoices, $this->getSelectedChoices($choiceView->choices, $values));
            } elseif ($choiceView instanceof ChoiceView) {
                if (in_array($choiceView->value, $values)) {
                    $selectedChoices[] = $choiceView;
                }
            }
        }

        return $selectedChoices;
    }

    private function createChoiceListView(ChoiceListInterface $choiceList, array $options)
    {
        // If no explicit grouping information is given, use the structural
        // information from the "choices" option for creating groups
        if (!$options['group_by'] && $options['choices']) {
            $options['group_by'] = $options['choices'];
        }

        return $this->choiceListFactory->createView(
            $choiceList,
            $options['preferred_choices'],
            $options['choice_label'],
            $options['choice_name'],
            $options['group_by'],
            $options['choice_attr']
        );
    }
}
