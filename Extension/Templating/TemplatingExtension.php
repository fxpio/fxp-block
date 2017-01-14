<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\Templating;

use Sonatra\Component\Block\AbstractExtension;
use Sonatra\Component\Block\BlockRenderer;
use Sonatra\Component\Block\Templating\Helper\BlockHelper;
use Symfony\Component\Templating\PhpEngine;

/**
 * Integrates the Templating component with the Block library.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
