<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Extension\Core\DataTransformer;

use Fxp\Component\Block\Extension\Core\DataTransformer\PasswordTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PasswordTransformerTest extends TestCase
{
    public function providerTestTransform()
    {
        return [
            [true, 6, '*', null, ''],
            [true, 6, '*', 'abcd', '******'],
            [true, 6, '*', 'abcdefghijkl', '******'],
            [true, 2, '§', null, ''],
            [true, 2, '§', 'abcd', '§§'],
            [true, 2, '§', 'abcdefghijkl', '§§'],
            [false, 20, '*', null, ''],
            [false, 20, '*', 'abcd', 'abcd'],
            [false, 20, '*', 'abcdefghijkl', 'abcdefghijkl'],
        ];
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
