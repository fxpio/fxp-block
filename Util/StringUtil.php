<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Util;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class StringUtil
{
    /**
     * Converts a fully-qualified class name to a block prefix.
     *
     * @param string $fqcn   The fully-qualified class name
     * @param bool   $vendor Check if type must be prefixed withe the vendor of namespace
     *
     * @return string|null The block prefix or null if not a valid FQCN
     */
    public static function fqcnToBlockPrefix($fqcn, $vendor = false)
    {
        $type = null;

        // Non-greedy ("+?") to match "type" suffix, if present
        if (preg_match('~([^\\\\]+?)(type)?$~i', $fqcn, $matches)) {
            $type = strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), $matches[1]));

            if ($vendor && is_string($type) && strlen($type) > 0 && strpos($fqcn, '\\') > 0) {
                $vendor = strtolower(substr($fqcn, 0, strpos($fqcn, '\\')));

                $type = 'fxp' === $vendor
                    ? $type
                    : $vendor.':'.$type;
            }
        }

        return $type;
    }
}
