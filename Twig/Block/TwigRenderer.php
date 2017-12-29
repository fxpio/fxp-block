<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Twig\Block;

use Fxp\Component\Block\BlockRenderer;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TwigRenderer extends BlockRenderer implements TwigRendererInterface
{
    /**
     * Constructor.
     *
     * @param TwigRendererEngineInterface $engine
     */
    public function __construct(TwigRendererEngineInterface $engine)
    {
        parent::__construct($engine);
    }
}
