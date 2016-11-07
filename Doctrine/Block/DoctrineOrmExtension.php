<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Doctrine\Block;

use Doctrine\Common\Persistence\ManagerRegistry;
use Sonatra\Component\Block\AbstractExtension;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DoctrineOrmExtension extends AbstractExtension
{
    protected $registry;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTypes()
    {
        return array(
            new Type\EntityType($this->registry),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTypeGuesser()
    {
        return new DoctrineOrmTypeGuesser($this->registry);
    }
}
