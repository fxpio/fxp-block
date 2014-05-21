<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\DataCollector;

use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockView;
use Symfony\Component\HttpKernel\DataCollector\Util\ValueExporter;

/**
 * Default implementation of {@link BlockDataExtractorInterface}.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockDataExtractor implements BlockDataExtractorInterface
{
    /**
     * @var ValueExporter
     */
    private $valueExporter;

    /**
     * Constructs a new data extractor.
     *
     * @param ValueExporter $valueExporter
     */
    public function __construct(ValueExporter $valueExporter = null)
    {
        $this->valueExporter = $valueExporter ?: new ValueExporter();
    }

    /**
     * {@inheritdoc}
     */
    public function extractConfiguration(BlockInterface $block)
    {
        $data = array(
            'id' => $this->buildId($block),
            'type' => $block->getConfig()->getType()->getName(),
            'type_class' => get_class($block->getConfig()->getType()->getInnerType()),
            'passed_options' => array(),
            'resolved_options' => array(),
        );

        foreach ($block->getConfig()->getAttribute('data_collector/passed_options', array()) as $option => $value) {
            $data['passed_options'][$option] = $this->valueExporter->exportValue($value);
        }

        foreach ($block->getOptions() as $option => $value) {
            $data['resolved_options'][$option] = $this->valueExporter->exportValue($value);
        }

        ksort($data['passed_options']);
        ksort($data['resolved_options']);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function extractDefaultData(BlockInterface $block)
    {
        $data = array(
            'default_data' => array(
                'norm' => $this->valueExporter->exportValue($block->getNormData()),
            ),
        );

        if ($block->getData() !== $block->getNormData()) {
            $data['default_data']['model'] = $this->valueExporter->exportValue($block->getData());
        }

        if ($block->getViewData() !== $block->getNormData()) {
            $data['default_data']['view'] = $this->valueExporter->exportValue($block->getViewData());
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function extractViewVariables(BlockView $view)
    {
        $data = array();

        foreach ($view->vars as $varName => $value) {
            $data['view_vars'][$varName] = $this->valueExporter->exportValue($value);
        }

        ksort($data['view_vars']);

        return $data;
    }

    /**
     * Recursively builds an HTML ID for a block.
     *
     * @param BlockInterface $block The block
     *
     * @return string The HTML ID
     */
    private function buildId(BlockInterface $block)
    {
        return $block->getName();
    }
}
