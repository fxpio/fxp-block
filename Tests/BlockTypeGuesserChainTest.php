<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests;

use Fxp\Component\Block\BlockTypeGuesserChain;
use Fxp\Component\Block\Guess\Guess;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockTypeGuesserChainTest extends TestCase
{
    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testInvalidGuessers()
    {
        new BlockTypeGuesserChain([42]);
    }

    public function testGuessers()
    {
        $guessers = new BlockTypeGuesserChain([
            $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock(),
            new BlockTypeGuesserChain([
                $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock(),
            ]),
        ]);

        $ref = new \ReflectionClass($guessers);
        $ref = $ref->getProperty('guessers');
        $ref->setAccessible(true);
        $value = $ref->getValue($guessers);

        $this->assertEquals([
            $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock(),
            $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock(),
        ], $value);
    }

    public function testGuessType()
    {
        $guess = $this->getMockForAbstractClass('Fxp\Component\Block\Guess\Guess', [Guess::MEDIUM_CONFIDENCE]);
        $guesser = $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock();
        $guessers = new BlockTypeGuesserChain([$guesser]);

        $guesser->expects($this->any())
            ->method('guessType')
            ->will($this->returnValue($guess));

        $this->assertEquals($guess, $guessers->guessType('stdClass', 'bar'));
    }
}
