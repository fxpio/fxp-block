<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface DataMapperInterface
{
    /**
     * Maps properties of some data to a list of blocks.
     *
     * @param mixed              $data   Structured data
     * @param array|\Traversable $blocks A list of {@link BlockInterface} instances
     *
     * @throws Exception\UnexpectedTypeException if the type of the data parameter is not supported
     */
    public function mapDataToViews($data, $blocks);
}
