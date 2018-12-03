<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\DependencyInjection;

use Fxp\Component\Block\BlockExtensionInterface;
use Fxp\Component\Block\BlockTypeGuesserChain;
use Fxp\Component\Block\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DependencyInjectionExtension implements BlockExtensionInterface
{
    /**
     * @var ContainerInterface
     */
    public $container;

    protected $typeServiceIds;
    protected $typeExtensionServiceIds;
    protected $guesserServiceIds;
    protected $guesser;
    protected $guesserLoaded = false;

    /**
     * Constructor.
     *
     * @param array $typeServiceIds
     * @param array $typeExtensionServiceIds
     * @param array $guesserServiceIds
     */
    public function __construct(
            array $typeServiceIds, array $typeExtensionServiceIds,
            array $guesserServiceIds)
    {
        $this->typeServiceIds = $typeServiceIds;
        $this->typeExtensionServiceIds = $typeExtensionServiceIds;
        $this->guesserServiceIds = $guesserServiceIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!isset($this->typeServiceIds[$name])) {
            throw new InvalidArgumentException(sprintf('The field type "%s" is not registered with the service container.', $name));
        }

        return $this->container->get($this->typeServiceIds[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        return isset($this->typeServiceIds[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions($name)
    {
        $extensions = [];

        if (isset($this->typeExtensionServiceIds[$name])) {
            foreach ($this->typeExtensionServiceIds[$name] as $serviceId) {
                $extensions[] = $extension = $this->container->get($serviceId);
                $extendedTypes = $extension::getExtendedTypes();

                // validate the result of getExtendedTypes() to ensure it is consistent with the service definition
                if (!\in_array($name, $extendedTypes, true)) {
                    throw new InvalidArgumentException(
                        sprintf('The extended type specified for the service "%s" does not match the actual extended type. Expected "%s", given "%s".',
                            $serviceId,
                            $name,
                            implode(', ', $extendedTypes)
                        )
                    );
                }
            }
        }

        return $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTypeExtensions($name)
    {
        return isset($this->typeExtensionServiceIds[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser()
    {
        if (!$this->guesserLoaded) {
            $this->guesserLoaded = true;
            $guessers = [];

            foreach ($this->guesserServiceIds as $serviceId) {
                $guessers[] = $this->container->get($serviceId);
            }

            if (\count($guessers) > 0) {
                $this->guesser = new BlockTypeGuesserChain($guessers);
            }
        }

        return $this->guesser;
    }
}
