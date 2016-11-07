<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Guess;

use Sonatra\Component\Block\Guess\Guess;
use Sonatra\Component\Block\Tests\Fixtures\Guess\TestGuess;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GuessTest extends \PHPUnit_Framework_TestCase
{
    public function testGetBestGuessReturnsGuessWithHighestConfidence()
    {
        $guess1 = new TestGuess(Guess::MEDIUM_CONFIDENCE);
        $guess2 = new TestGuess(Guess::LOW_CONFIDENCE);
        $guess3 = new TestGuess(Guess::HIGH_CONFIDENCE);

        $this->assertSame($guess3, Guess::getBestGuess(array($guess1, $guess2, $guess3)));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGuessExpectsValidConfidence()
    {
        new TestGuess(5);
    }
}
