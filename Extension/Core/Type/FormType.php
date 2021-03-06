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
use Fxp\Component\Block\Util\BlockFormUtil;
use Symfony\Component\Form\Extension\Core\Type\FormType as FormFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FormType extends AbstractType
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * Constructor.
     *
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $form = $this->buildForm($builder, $options);

        if (null !== $form->getData()) {
            $builder->setData($form->getData());
        }

        if (null !== $form->getConfig()->getDataClass()) {
            $builder->setDataClass($form->getConfig()->getDataClass());
            $builder->setInheritData($form->getConfig()->getInheritData());
        }

        $builder->setForm($form);
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(BlockInterface $child, BlockInterface $block, array $options)
    {
        $parentForm = BlockFormUtil::getParentForm($block);
        $form = $child->getForm();

        if (null !== $parentForm && null !== $form) {
            if (!$parentForm->has($form->getName())) {
                $parentForm->add($form);
            } else {
                $child->setForm($parentForm->get($form->getName()));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(BlockInterface $child, BlockInterface $block, array $options)
    {
        $parentForm = BlockFormUtil::getParentForm($block);
        $form = $child->getForm();

        if (null !== $parentForm && null !== $form) {
            $parentForm->remove($form->getName());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        $view->vars = array_replace($view->vars, [
            'block_form' => BlockFormUtil::createFormView($view, $block),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type' => FormFormType::class,
            'options' => [],
            'form_path' => null,
        ]);

        $resolver->addAllowedTypes('type', ['string', 'Symfony\Component\Form\FormInterface']);
        $resolver->addAllowedTypes('options', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'form';
    }

    /**
     * Build the form.
     *
     * @param BlockBuilderInterface $builder      The block builder
     * @param array                 $blockOptions The block options
     *
     * @return FormInterface
     */
    protected function buildForm(BlockBuilderInterface $builder, array $blockOptions)
    {
        $type = $blockOptions['type'];

        if (!$type instanceof FormInterface) {
            $name = isset($blockOptions['block_name']) ? $blockOptions['block_name'] : $builder->getName();
            $options = $blockOptions['options'];
            $options['auto_initialize'] = false;

            if (null !== $builder->getData()) {
                $options['auto_initialize'] = true;
                $options['data'] = $builder->getData();
            }

            if (null !== $builder->getDataClass()) {
                $options['auto_initialize'] = true;
                $options['data_class'] = $builder->getDataClass();
            }

            $type = $this->formFactory->createNamed($name, $type, null, $options);
        }

        return $type;
    }
}
