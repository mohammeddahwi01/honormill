<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Model\Indexer;

use \MageWorx\SearchSuiteSphinx\Helper\Sphinx as HelperSphinx;

use Magento\Framework\Indexer\ActionFactory;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\StructureFactory;

class Product extends \Magento\Indexer\Model\Indexer implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const INDEXER_ID = 'mageworx_sphinxsearch_products';

    /**
     * @var \MageWorx\SearchSuiteSphinx\Helper\Sphinx
     */
    protected $helperSphinx;

    /**
     * Product constructor.
     *
     * @param HelperSphinx $helperSphinx
     * @param ConfigInterface $config
     * @param ActionFactory $actionFactory
     * @param StructureFactory $structureFactory
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param \Magento\Indexer\Model\Indexer\StateFactory $stateFactory
     * @param \Magento\Indexer\Model\Indexer\CollectionFactory $indexersFactory
     * @param array $data
     */
    public function __construct(
        HelperSphinx $helperSphinx,
        ConfigInterface $config,
        ActionFactory $actionFactory,
        StructureFactory $structureFactory,
        \Magento\Framework\Mview\ViewInterface $view,
        \Magento\Indexer\Model\Indexer\StateFactory $stateFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexersFactory,
        array $data = []
    ) {
        $this->helperSphinx = $helperSphinx;
        parent::__construct($config, $actionFactory, $structureFactory, $view, $stateFactory, $indexersFactory, $data);
        $this->setViewId('mageworx_sphinxsearch_products');
    }

    /**
     *
     */
    public function executeFull()
    {
        $this->helperSphinx->runSphinxReindex();
    }

    /**
     * @param array $ids
     */
    public function executeList(array $ids)
    {
        $this->helperSphinx->updateSphinxDeltaReindex($ids);
    }

    /**
     * @param int $id
     */
    public function executeRow($id)
    {
        $this->executeList([$id]);
    }

    /**
     * @param int[] $ids
     */
    public function execute($ids)
    {
        $this->helperSphinx->runSphinxReindex();
    }

}

