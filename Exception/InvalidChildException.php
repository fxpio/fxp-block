<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Exception;

use Fxp\Component\Block\BlockBuilderInterface;

/**
 * Base InvalidChildException for the Block and Block builder component.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class InvalidChildException extends InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param BlockBuilderInterface $builder
     * @param BlockBuilderInterface $builderChild
     * @param string|array<string>  $allowed
     */
    public function __construct(BlockBuilderInterface $builder, BlockBuilderInterface $builderChild, $allowed = null)
    {
        $msg = sprintf('The child "%s" ("%s" type) is not allowed for "%s" block ("%s" type)', $builderChild->getName(), get_class($builderChild->getType()->getInnerType()), $builder->getName(), get_class($builder->getType()->getInnerType()));

        if (null !== $allowed && !empty($allowed)) {
            $msg .= sprintf(', only "%s" allowed', implode('", "', (array) $allowed));
        }

        parent::__construct($msg);
    }
}
