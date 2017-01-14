<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\DataCollector\Proxy;

use Sonatra\Component\Block\BlockTypeInterface;
use Sonatra\Component\Block\Extension\DataCollector\BlockDataCollectorInterface;
use Sonatra\Component\Block\ResolvedBlockTypeFactoryInterface;
use Sonatra\Component\Block\ResolvedBlockTypeInterface;

/**
 * Proxy that wraps resolved types into {@link ResolvedTypeDataCollectorProxy}
 * instances.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
