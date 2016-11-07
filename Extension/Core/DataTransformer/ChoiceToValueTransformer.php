<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\Core\DataTransformer;

use Sonatra\Component\Block\DataTransformerInterface;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoiceToValueTransformer implements DataTransformerInterface
{
    private $choiceList;

    /**
     * Constructor.
     *
     * @param ChoiceListInterface $choiceList
     */
    public function __construct(ChoiceListInterface $choiceList)
    {
        $this->choiceList = $choiceList;
    }

    public function transform($choice)
    {
        $choice = (array) $choice;

        return (string) current($this->choiceList->getValuesForChoices($choice));
    }
}
