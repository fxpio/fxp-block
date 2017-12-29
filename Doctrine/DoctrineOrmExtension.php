<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Fxp\Component\Block\AbstractExtension;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
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
        return [
            new Type\EntityType($this->registry),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTypeGuesser()
    {
        return new DoctrineOrmTypeGuesser($this->registry);
    }
}
