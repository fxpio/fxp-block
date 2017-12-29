<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\Templating;

use Fxp\Component\Block\AbstractExtension;
use Fxp\Component\Block\BlockRenderer;
use Fxp\Component\Block\Templating\Helper\BlockHelper;
use Symfony\Component\Templating\PhpEngine;

/**
 * Integrates the Templating component with the Block library.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TemplatingExtension extends AbstractExtension
{
    /**
     * Constructor.
     *
     * @param PhpEngine $engine
     * @param array     $defaultThemes
     */
    public function __construct(PhpEngine $engine, array $defaultThemes = array())
    {
        $engine->addHelpers(array(
            new BlockHelper(new BlockRenderer(new TemplatingRendererEngine($engine, $defaultThemes))),
        ));
    }
}
