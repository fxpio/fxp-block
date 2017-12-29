<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block;

/**
 * Creates ResolvedBlockTypeInterface instances.
 *
 * This interface allows you to use your custom ResolvedBlockTypeInterface
 * implementation, within which you can customize the concrete BlockBuilderInterface
 * implementations or Block subclasses that are used by the framework.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface ResolvedBlockTypeFactoryInterface
{
    /**
     * Resolves a block type.
     *
     * @param BlockTypeInterface            $type
     * @param BlockTypeExtensionInterface[] $typeExtensions
     * @param ResolvedBlockTypeInterface    $parent
     *
     * @return ResolvedBlockTypeInterface
     *
     * @throws Exception\UnexpectedTypeException  If the types parent {@link BlockTypeInterface::getParent()} is not a string
     * @throws Exception\InvalidArgumentException If the types parent can not be retrieved block any extension
     */
    public function createResolvedType(BlockTypeInterface $type, array $typeExtensions, ResolvedBlockTypeInterface $parent = null);
}
