<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SearchSuiteSphinx\Model;

use \Magento\Search\Model\AdapterFactory;
use \MageWorx\SearchSuiteSphinx\Model\SearchEngine\AdapterSphinxFactory;
use \Magento\Framework\Search\RequestInterface;
use \Magento\Framework\Search\SearchEngineInterface;

/**
 * Search Engine
 */
class SearchEngine implements SearchEngineInterface
{
    /**
     * Adapter factory
     *
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \MageWorx\SearchSuiteSphinx\Model\SearchEngine\AdapterSphinxFactory
     */
    protected $adapterSphinxFactory;

    /**
     * SearchEngine constructor.
     *
     * @param AdapterFactory $adapterFactory
     * @param AdapterSphinxFactory $adapterSphinxFactory
     */
    public function __construct(
        AdapterFactory $adapterFactory,
        AdapterSphinxFactory $adapterSphinxFactory
    ) {
    
        $this->adapterFactory = $adapterFactory;
        $this->adapterSphinxFactory = $adapterSphinxFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function search(RequestInterface $request)
    {
        if ($request->getName() != 'quick_search_container') {
            return $this->adapterFactory->create()->query($request);
        }

        return $this->adapterSphinxFactory->create()->query($request);
    }
}
