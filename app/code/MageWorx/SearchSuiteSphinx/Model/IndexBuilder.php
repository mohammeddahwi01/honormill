<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Model;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\RequestInterface;
use MageWorx\SearchSuiteSphinx\Model\VersionResolver\FilterSelectCreator;

/**
 * Build Query for Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder implements \Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface
{
    /**
     * @var BaseSelectSphinxStrategy
     */
    private $baseSelectSphinxStrategy;

    /**
     * @var FilterSelectCreator
     */
    private $filterSelectCreator;

    /**
     * IndexBuilder constructor.
     *
     * @param BaseSelectSphinxStrategy $baseSelectSphinxStrategy
     * @param FilterSelectCreator      $filterSelectCreator
     */
    public function __construct(
        BaseSelectSphinxStrategy $baseSelectSphinxStrategy,
        FilterSelectCreator $filterSelectCreator
    ) {
        $this->baseSelectSphinxStrategy = $baseSelectSphinxStrategy;
        $this->filterSelectCreator = $filterSelectCreator;
    }

    /**
     * @param RequestInterface $request
     * @return Select
     */
    public function build(RequestInterface $request)
    {
        $select = $this->baseSelectSphinxStrategy->createBaseSelect();
        $select = $this->filterSelectCreator->applyFilters($select, $request);
        $select = $this->filterSelectCreator->processDimensions($select);

        return $select;
    }
}
