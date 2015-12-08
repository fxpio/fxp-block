<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\Type;

use Sonatra\Bundle\BlockBundle\Block\AbstractType;
use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockView;
use Sonatra\Bundle\BlockBundle\Block\Util\BlockFormUtil;
use Symfony\Component\Form\FormView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FormFunctionType extends AbstractType
{
    /**
     * @var string
     */
    protected $functionName;

    /**
     * Constructor.
     *
     * @param string $functionName The form function name
     */
    public function __construct($functionName)
    {
        $this->functionName = $functionName;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options)
    {
        $parentForm = BlockFormUtil::getParentFormView($view);

        if ($parentForm instanceof FormView) {
            $view->vars = array_replace($view->vars, array(
                'block_form' => $parentForm,
            ));

            $parentForm->vars['skip_'.$this->functionName] = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'form_'.$this->functionName;
    }
}
