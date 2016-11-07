<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Guess;

/**
 * Contains a guessed class name and a list of options for creating an instance
 * of that class.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class TypeGuess extends Guess
{
    /**
     * The guessed field type.
     *
     * @var string
     */
    private $type;

    /**
     * The guessed options for creating an instance of the guessed class.
     *
     * @var array
     */
    private $options;

    /**
     * Constructor.
     *
     * @param string $type       The guessed field type
     * @param array  $options    The options for creating instances of the
     *                           guessed class
     * @param int    $confidence The confidence that the guessed class name
     *                           is correct
     */
    public function __construct($type, array $options, $confidence)
    {
        parent::__construct($confidence);

        $this->type = $type;
        $this->options = $options;
    }

    /**
     * Returns the guessed field type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the guessed options for creating instances of the guessed type.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
