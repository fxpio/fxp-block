<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * BlockTranslator extends Twig with block translation capabilities.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockTranslatorExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_Filter('block_trans', [$this, 'trans']),
        ];
    }

    /**
     * Translate the value, only if the domain is defined.
     *
     * @param string           $value      The value
     * @param array            $parameters The translation parameters
     * @param string|bool|null $domain     The translation domain
     * @param string|null      $locale     The translation locale
     *
     * @return string
     */
    public function trans($value, array $parameters = [], $domain = null, $locale = null)
    {
        $domain = true === $domain ? 'messages' : $domain;

        if ($this->isString($value) && $this->isString($domain)) {
            $value = $this->translator->trans($value, $parameters, $domain, $locale);
        }

        return $value;
    }

    /**
     * Check if the value is a string with content.
     *
     * @param mixed $value The value to check
     *
     * @return bool
     */
    protected function isString($value)
    {
        return \is_string($value) && \strlen($value) > 0;
    }
}
