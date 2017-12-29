<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\DataCollector\Proxy;

use Fxp\Component\Block\BlockTypeInterface;
use Fxp\Component\Block\Extension\DataCollector\BlockDataCollectorInterface;
use Fxp\Component\Block\ResolvedBlockTypeFactoryInterface;
use Fxp\Component\Block\ResolvedBlockTypeInterface;

/**
 * Proxy that wraps resolved types into {@link ResolvedTypeDataCollectorProxy}
 * instances.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ResolvedTypeFactoryDataCollectorProxy implements ResolvedBlockTypeFactoryInterface
{
    /**
     * @var ResolvedBlockTypeFactoryInterface
     */
    private $proxiedFactory;

    /**
     * @var BlockDataCollectorInterface
     */
    private $dataCollector;

    /**
     * Constructor.
     *
     * @param ResolvedBlockTypeFactoryInterface $proxiedFactory
     * @param BlockDataCollectorInterface       $dataCollector
     */
    public function __construct(ResolvedBlockTypeFactoryInterface $proxiedFactory, BlockDataCollectorInterface $dataCollector)
    {
        $this->proxiedFactory = $proxiedFactory;
        $this->dataCollector = $dataCollector;
    }

    /**
     * {@inheritdoc}
     */
    public function createResolvedType(BlockTypeInterface $type, array $typeExtensions, ResolvedBlockTypeInterface $parent = null)
    {
        return new ResolvedTypeDataCollectorProxy(
            $this->proxiedFactory->createResolvedType($type, $typeExtensions, $parent),
            $this->dataCollector
        );
    }
}
