<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Twig\Block;

use Sonatra\Component\Block\BlockRenderer;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
