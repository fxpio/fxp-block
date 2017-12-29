<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\DataCollector;

use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Data collector for {@link \Fxp\Component\Block\BlockInterface} instances.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockDataCollector extends DataCollector implements BlockDataCollectorInterface
{
    /**
     * @var BlockDataExtractor
     */
    private $dataExtractor;

    /**
     * Stores the collected data per {@link BlockInterface} instance.
     *
     * Uses the hashes of the blocks as keys. This is preferrable over using
     * {@link \SplObjectStorage}, because in this way no references are kept
     * to the {@link BlockInterface} instances.
     *
     * @var array
     */
    private $dataByBlock;

    /**
     * Stores the collected data per {@link BlockView} instance.
     *
     * Uses the hashes of the views as keys. This is preferrable over using
     * {@link \SplObjectStorage}, because in this way no references are kept
     * to the {@link BlockView} instances.
     *
     * @var array
     */
    private $dataByView;

    /**
     * Connects {@link BlockView} with {@link BlockInterface} instances.
     *
     * Uses the hashes of the views as keys and the hashes of the blocks as
     * values. This is preferrable over storing the objects directly, because
     * this way they can safely be discarded by the GC.
     *
     * @var array
     */
    private $blocksByView;

    /**
     * @var array
     */
    private $viewIds;

    /**
     * Constructor.
     *
     * @param BlockDataExtractorInterface $dataExtractor
     */
    public function __construct(BlockDataExtractorInterface $dataExtractor)
    {
        $this->dataExtractor = $dataExtractor;
        $this->reset();
    }

    /**
     * Does nothing. The data is collected during the block event listeners.
     *
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [
            'blocks' => [],
            'duplicate_ids' => [],
        ];
        $this->viewIds = [];
    }

    /**
     * Does nothing. The data is collected during the block event listeners.
     *
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function associateBlockWithView(BlockInterface $block, BlockView $view)
    {
        $this->blocksByView[spl_object_hash($view)] = spl_object_hash($block);
    }

    /**
     * {@inheritdoc}
     */
    public function collectConfiguration(BlockInterface $block)
    {
        $hash = spl_object_hash($block);

        if (!isset($this->dataByBlock[$hash])) {
            $this->dataByBlock[$hash] = [];
        }

        $this->dataByBlock[$hash] = array_replace(
            $this->dataByBlock[$hash],
            $this->dataExtractor->extractConfiguration($block)
        );

        foreach ($block as $child) {
            $this->collectConfiguration($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collectDefaultData(BlockInterface $block)
    {
        $hash = spl_object_hash($block);

        if (!isset($this->dataByBlock[$hash])) {
            $this->dataByBlock[$hash] = [];
        }

        $this->dataByBlock[$hash] = array_replace(
            $this->dataByBlock[$hash],
            $this->dataExtractor->extractDefaultData($block)
        );

        foreach ($block as $child) {
            $this->collectDefaultData($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collectViewVariables(BlockView $view)
    {
        $hash = spl_object_hash($view);

        if (!isset($this->dataByView[$hash])) {
            $this->dataByView[$hash] = [];
        }

        $this->dataByView[$hash] = array_replace(
            $this->dataByView[$hash],
            $this->dataExtractor->extractViewVariables($view)
        );

        foreach ($view->children as $child) {
            $this->collectViewVariables($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildFinalBlockTree(BlockInterface $block, BlockView $view)
    {
        $hash = spl_object_hash($view);
        $this->data['blocks'][$hash] = [];

        $this->recursiveBuildFinalBlockTree($block, $view, $this->data['blocks'][$hash]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'fxp_block';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Recursive build final block tree.
     *
     * @param BlockInterface $block
     * @param BlockView      $view
     * @param string         $output
     */
    private function recursiveBuildFinalBlockTree(BlockInterface $block = null, BlockView $view, &$output = null)
    {
        $viewHash = spl_object_hash($view);
        $blockHash = null;

        if (null !== $block) {
            $blockHash = spl_object_hash($block);
        } elseif (isset($this->blocksByView[$viewHash])) {
            $blockHash = $this->blocksByView[$viewHash];
        }

        $output = isset($this->dataByView[$viewHash])
            ? $this->dataByView[$viewHash]
            : [];

        if (null !== $blockHash) {
            $output = array_replace(
                $output,
                isset($this->dataByBlock[$blockHash])
                    ? $this->dataByBlock[$blockHash]
                    : []
            );
        }

        $this->validateViewIds($block, $view);

        $output['children'] = [];

        foreach ($view->children as $name => $childView) {
            // The CSRF token, for example, is never added to the block tree.
            // It is only present in the view.
            $childBlock = null !== $block && $block->has($name)
                ? $block->get($name)
                : null;

            $childHash = spl_object_hash($childView);
            $output['children'][$childHash] = [];

            $this->recursiveBuildFinalBlockTree($childBlock, $childView, $output['children'][$childHash]);
        }
    }

    /**
     * Validate the view Id.
     *
     * @param BlockInterface $block
     * @param BlockView      $view
     */
    private function validateViewIds(BlockInterface $block = null, BlockView $view)
    {
        if (!$block->getOption('render_id')) {
            return;
        }

        $id = $view->vars['id'];
        $hash = spl_object_hash($block);
        $newIds = [$hash];

        if (isset($this->viewIds[$id]) && !in_array($hash, $this->viewIds[$id])) {
            $this->data['duplicate_ids'][] = $id;
            $this->data['duplicate_ids'] = array_unique($this->data['duplicate_ids']);

            $newIds = array_merge($this->viewIds[$id], $newIds);
        }

        $this->viewIds[$id] = $newIds;
    }
}
