<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Extension\Core\DataTransformer;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Block\Extension\Core\DataTransformer\PasswordTransformer;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PasswordTransformerTest extends TestCase
{
    public function providerTestTransform()
    {
        return array(
            array(true, 6, '*', null, ''),
            array(true, 6, '*', 'abcd', '******'),
            array(true, 6, '*', 'abcdefghijkl', '******'),
            array(true, 2, '§', null, ''),
            array(true, 2, '§', 'abcd', '§§'),
            array(true, 2, '§', 'abcdefghijkl', '§§'),
            array(false, 20, '*', null, ''),
            array(false, 20, '*', 'abcd', 'abcd'),
            array(false, 20, '*', 'abcdefghijkl', 'abcdefghijkl'),
        );
    }

    /**
     * @dataProvider providerTestTransform
     */
    public function testTransform($mask, $maskLength, $maskSymbol, $input, $output)
    {
        $transformer = new PasswordTransformer($mask, $maskLength, $maskSymbol);

        $this->assertEquals($output, $transformer->transform($input));
    }
}
