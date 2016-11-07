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
 * Transforms a value between different representations.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface DataTransformerInterface
{
    /**
     * Transforms a value block the original representation to a transformed representation.
     *
     * This method is called when the block field is initialized with the data attached from the datasource (object or array).
     *
     * This method must be able to deal with empty values. Usually this will
     * be NULL, but depending on your implementation other empty values are
     * possible as well (such as empty strings). The reasoning behind this is
     * that value transformers must be chainable. If the transform() method
     * of the first value transformer outputs NULL, the second value transformer
     * must be able to process that value.
     *
     * By convention, transform() should return an empty string if NULL is
     * passed.
     *
     * @param mixed $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws Exception\UnexpectedTypeException       when the argument is not a string
     * @throws Exception\TransformationFailedException when the transformation fails
     */
    public function transform($value);
}
