<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Fixtures\Object;

/**
 * Foo class test.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Foo extends AbstractFoo
{
    /**
     * @var string
     */
    private $bar;

    /**
     * @param string $value
     */
    public function setBar($value)
    {
        $this->bar = $value;
    }

    /**
     * @return string
     */
    public function getBar()
    {
        return $this->bar;
    }
}
