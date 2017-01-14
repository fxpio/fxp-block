<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\Core\Type;

use Sonatra\Component\Block\AbstractType;
use Sonatra\Component\Block\BlockInterface;
use Sonatra\Component\Block\BlockView;
use Sonatra\Component\Block\Util\BlockUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class UrlType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options)
    {
        $view->vars['title'] = $options['title'];
        $view->vars['url_attr'] = $options['url_attr'];

        if ($view->vars['value'] instanceof \Closure) {
            $view->vars['value'] = $view->vars['value']($options);
        }

        if (!BlockUtil::isEmpty($view->vars['value'])
                && $view->vars['value'] !== $block->getConfig()->getEmptyMessage()
                && false === strpos($view->vars['value'], '://')
                && '/' !== substr($view->vars['value'], 0, 1)) {
            $view->vars['value'] = 'http://'.$view->vars['value'];
        }

        if ('/' === substr($view->vars['value'], 0, 1)) {
            $view->vars['value'] = substr($view->vars['value'], 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'title' => null,
            'url_attr' => array(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'url';
    }
}
