<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * BlockTranslator extends Twig with block translation capabilities.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
        return array(
            new \Twig_SimpleFilter('block_trans', array($this, 'trans')),
        );
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
    public function trans($value, array $parameters = array(), $domain = null, $locale = null)
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
        return is_string($value) && strlen($value) > 0;
    }
}
