<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block;

use Fxp\Component\Block\Exception\ClassNotInstantiableException;
use Fxp\Component\Block\Extension\Core\CoreExtension;

/**
 * Entry point of the Block component.
 *
 * Use this class to conveniently create new block factories:
 *
 * <code>
 * use Fxp\Component\Block\Blocks;
 * use Fxp\Component\Block\Extension\Core\Type\TextType;
 * use Fxp\Component\Block\Extension\Core\Type\IntegerType;
 * use Fxp\Component\Block\Extension\Core\Type\ChoiceType;
 *
 * $blockFactory = Blocks::createBlockFactory();
 *
 * $block = $blockFactory->createBuilder()
 *     ->add('firstName', TextType::class)
 *     ->add('lastName', TextType::class)
 *     ->add('age', IntegerType::class)
 *     ->add('gender', ChoiceType::class)
 *     ->getBlock();
 * </code>
 *
 * You can also add custom extensions to the block factory:
 *
 * <code>
 * $blockFactory = Blocks::createBlockFactoryBuilder()
 *     ->addExtension(new AcmeExtension())
 *     ->getBlockFactory();
 * </code>
 *
 * If you create custom block types or type extensions, it is
 * generally recommended to create your own extensions that lazily
 * load these types and type extensions. In projects where performance
 * does not matter that much, you can also pass them directly to the
 * block factory:
 *
 * <code>
 * $blockFactory = Blocks::createBlockFactoryBuilder()
 *     ->addType(new PersonType())
 *     ->addType(new PhoneNumberType())
 *     ->addTypeExtension(new BlockTypeHelpTextExtension())
 *     ->getBlockFactory();
 * </code>
 *
 * Support for the Templating component is provided by TemplatingExtension.
 * This extension needs a PhpEngine object for rendering blocks. As second
 * argument you should pass the names of the default themes. Here is an
 * example for using the default layout with "<div>" tags:
 *
 * <code>
 * use Fxp\Component\Block\Extension\Templating\TemplatingExtension;
 *
 * $blockFactory = Blocks::createBlockFactoryBuilder()
 *     ->addExtension(new TemplatingExtension($engine, array(
 *         'FxpBlockBundle:Block',
 *     )))
 *     ->getBlockFactory();
 * </code>
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class Blocks
{
    /**
     * Creates a block factory with the default configuration.
     *
     * @return BlockFactoryInterface The block factory
     */
    public static function createBlockFactory()
    {
        return self::createBlockFactoryBuilder()->getBlockFactory();
    }

    /**
     * Creates a block factory builder with the default configuration.
     *
     * @return BlockFactoryBuilderInterface The block factory builder
     */
    public static function createBlockFactoryBuilder()
    {
        $builder = new BlockFactoryBuilder();
        $builder->addExtension(new CoreExtension());

        return $builder;
    }

    public function __construct()
    {
        throw new ClassNotInstantiableException(__CLASS__);
    }
}
