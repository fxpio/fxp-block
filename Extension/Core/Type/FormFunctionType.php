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
use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockView;
use Fxp\Component\Block\Util\BlockFormUtil;
use Symfony\Component\Form\FormView;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
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
