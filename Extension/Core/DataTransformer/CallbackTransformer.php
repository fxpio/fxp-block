<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\Core\DataTransformer;

use Fxp\Component\Block\DataTransformerInterface;
use Fxp\Component\Block\Exception\TransformationFailedException;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CallbackTransformer implements DataTransformerInterface
{
    /**
     * @var \Closure
     */
    private $transform;

    /**
     * Constructor.
     *
     * @param \Closure $transform The forward transform callback
     */
    public function __construct(\Closure $transform)
    {
        $this->transform = $transform;
    }

    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * @param mixed $data The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws TransformationFailedException When the transformation fails
     */
    public function transform($data)
    {
        return \call_user_func($this->transform, $data);
    }
}
