<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block;

use Sonatra\Component\Block\Guess\Guess;
use Sonatra\Component\Block\Exception\UnexpectedTypeException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockTypeGuesserChain implements BlockTypeGuesserInterface
{
    private $guessers = array();

    /**
     * Constructor.
     *
     * @param BlockTypeGuesserInterface[] $guessers Guessers as instances of BlockTypeGuesserInterface
     *
     * @throws UnexpectedTypeException if any guesser does not implement BlockTypeGuesserInterface
     */
    public function __construct(array $guessers)
    {
        foreach ($guessers as $guesser) {
            if (!$guesser instanceof BlockTypeGuesserInterface) {
                throw new UnexpectedTypeException($guesser, 'Sonatra\Component\Block\BlockTypeGuesserInterface');
            }

            if ($guesser instanceof self) {
                $this->guessers = array_merge($this->guessers, $guesser->guessers);
            } else {
                $this->guessers[] = $guesser;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        return $this->guess(function (BlockTypeGuesserInterface $guesser) use ($class, $property) {
            return $guesser->guessType($class, $property);
        });
    }

    /**
     * Executes a closure for each guesser and returns the best guess from the
     * return values.
     *
     * @param \Closure $closure The closure to execute. Accepts a guesser
     *                          as argument and should return a Guess instance
     *
     * @return Guess The guess with the highest confidence
     */
    protected function guess(\Closure $closure)
    {
        $guesses = array();

        foreach ($this->guessers as $guesser) {
            if ($guess = $closure($guesser)) {
                $guesses[] = $guess;
            }
        }

        return Guess::getBestGuess($guesses);
    }
}
