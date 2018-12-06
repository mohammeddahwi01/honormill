<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Model\VersionResolver;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Search\Request\QueryInterface as Query;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Class FilterMapper
 * This class added for compatibility with Magento 2.1
 */
class FilterSelectCreator
{
    /**
     * @var ModuleListInterface
     */

    protected $moduleList;
    /**
     * @var ResourceConnection
     */

    private $resourceConnection;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ScopeResolverInterface
     */
    private $dimensionScopeResolver;
    
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    private $validFieldsForExclusionStrategy = ['price', 'category_ids'];

    private $hasCustomAttributesFilters;

    private $isShowOutOfStockEnabled;

    private $hasVisibilityFilter;

    private $visibilityFilter;

    private $nonCustomAttributesFilters;

    private $customAttributesFilters;

    private $dimensions;

    const STOCK_FILTER_SUFFIX = '_stock';

    const TYPE_TERM = 'termFilter';

    const FILTER_JUST_ENTITY = 'general_filter';

    const FILTER_ENTITY_AND_SUB_PRODUCTS = 'filter_with_sub_products';

    const FILTER_BY_JOIN = 'join_filter';

    const FILTER_BY_WHERE = 'where_filter';

    const VISIBILITY_FILTER_FIELD = 'visibility';

    const TYPE_BOOL = 'boolFilter';

    const TYPE_RANGE = 'rangeFilter';

    const TYPE_WILDCARD = 'wildcardFilter';

    /**
     * FilterSelectCreator constructor.
     *
     * @param ResourceConnection          $resourceConnection
     * @param ConditionManager            $conditionManager
     * @param EavConfig                   $eavConfig
     * @param StoreManagerInterface       $storeManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface      $stockRegistry
     * @param ScopeConfigInterface        $scopeConfig
     * @param ScopeResolverInterface      $dimensionScopeResolver
     * @param ModuleListInterface         $moduleList
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ConditionManager $conditionManager,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        ScopeConfigInterface $scopeConfig,
        ScopeResolverInterface $dimensionScopeResolver,
        ModuleListInterface $moduleList
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->conditionManager = $conditionManager;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        $this->dimensionScopeResolver = $dimensionScopeResolver;
        $this->moduleList = $moduleList;
    }

    /**
     * @param Select $select
     * @param $request
     * @return Select
     */
    public function applyFilters(Select $select, $request)
    {
        $this->initRequestData($request);

        $select = $this->apply($select, $this->customAttributesFilters);

        return $select;
    }

    /**
     * @param RequestInterface $request
     */
    public function initRequestData(RequestInterface $request)
    {
        $nonCustomAttributesFilters = [];
        $customAttributesFilters = [];
        $visibilityFilter = null;

        foreach ($this->extractFiltersFromQuery($request->getQuery()) as $filter) {
            if ($this->isCustom($filter))
            {
                if ($filter->getField() === self::VISIBILITY_FILTER_FIELD) {
                    $visibilityFilter = clone $filter;
                } else {
                    $customAttributesFilters[] = clone $filter;
                }
            } else {
                $nonCustomAttributesFilters[] = clone $filter;
            }
        }

        $this->hasCustomAttributesFilters = !empty($customAttributesFilters);
        $this->isShowOutOfStockEnabled    = $this->isSetShowOutOfStockFlag();
        $this->hasVisibilityFilter        = $visibilityFilter !== null;
        $this->visibilityFilter           = $visibilityFilter;
        $this->customAttributesFilters    = $customAttributesFilters;
        $this->nonCustomAttributesFilters = $nonCustomAttributesFilters;
        $this->dimensions                 = $request->getDimensions();
    }

    /**
     * @param FilterInterface $filter
     * @return bool
     */
    private function isCustom(FilterInterface $filter)
    {
        $attribute = $this->getAttributeByCode($filter->getField());

        return $attribute
        && $filter->getType() === self::TYPE_TERM
        && in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true);
    }

    /**
     * @return bool
     */
    private function isSetShowOutOfStockFlag()
    {
        return $this->scopeConfig
            ->isSetFlag('cataloginventory/options/show_out_of_stock', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param FilterInterface $filter
     * @return null|string
     */
    public function getFilterAlias(FilterInterface $filter)
    {
        $alias = null;
        $field = $filter->getField();

        switch ($field) {
            case 'category_ids':
                $alias = 'category_ids_index';
                break;
            case 'price':
                $alias = 'price_index';
                break;
            default:
                $alias = $field . RequestGenerator::FILTER_SUFFIX;
                break;
        }
        return $alias;
    }
    /**
     * @param Query $query
     * @return array
     */
    public function extractFiltersFromQuery(Query $query)
    {
        $filters = [];

        switch ($query->getType()) {
            case Query::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\BoolExpression $query */
                foreach ($query->getMust() as $subQuery) {
                    $filters = array_merge(
                        $filters,
                        $this->extractFiltersFromQuery($subQuery)
                    );
                }
                foreach ($query->getShould() as $subQuery) {
                    $filters = array_merge(
                        $filters,
                        $this->extractFiltersFromQuery($subQuery)
                    );
                }
                foreach ($query->getMustNot() as $subQuery) {
                    $filters = array_merge(
                        $filters,
                        $this->extractFiltersFromQuery($subQuery)
                    );
                }
                break;
            case Query::TYPE_FILTER:
                $filter = $query->getReference();
                if (self::TYPE_BOOL === $filter->getType()) {
                    $filters = array_merge(
                        $filters,
                        $this->getFilterFromBoolFilter($filter)
                    );
                } else {
                    $filters[] = $filter;
                }
                break;
            default:
                break;
        }

        return $filters;
    }

    /**
     * @param BoolExpression $boolExpression
     * @return array
     */
    private function getFilterFromBoolFilter(BoolExpression $boolExpression)
    {
        $filters = [];

        /** @var BoolExpression $filter */
        foreach ($boolExpression->getMust() as $filter) {
            if ($filter->getType() === self::TYPE_BOOL) {
                $filters = array_merge(
                    $filters,
                    $this->getFilterFromBoolFilter($filter)
                );
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getShould() as $filter) {
            if ($filter->getType() === self::TYPE_BOOL) {
                $filters = array_merge(
                    $filters,
                    $this->getFilterFromBoolFilter($filter)
                );
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getMustNot() as $filter) {
            if ($filter->getType() === self::TYPE_BOOL) {
                $filters = array_merge(
                    $filters,
                    $this->getFilterFromBoolFilter($filter)
                );
            } else {
                $filters[] = $filter;
            }
        }

        return $filters;
    }

    /**
     * @param Select $select
     * @param $filters
     * @return Select
     */
    public function apply(Select $select, $filters){

        if ($this->hasCustomAttributesFilters) {
            $select = $this->applyCustomAttributeFilter($select, $filters);
        }

        $filterType = self::FILTER_JUST_ENTITY;
        if ($this->hasCustomAttributesFilters) {
            $filterType = self::FILTER_ENTITY_AND_SUB_PRODUCTS;
        }

        $select = $this->applyStockStatusFilter(
            $select,
            Stock::STOCK_IN_STOCK,
            $filterType,
            $this->isShowOutOfStockEnabled
        );

        $appliedFilters = [];

        if ($this->hasVisibilityFilter) {
            $filterType = self::FILTER_BY_WHERE;
            if ($this->hasCustomAttributesFilters) {
                $filterType = self::FILTER_BY_JOIN;
            }

            $select = $this->applyVisibilityFilter($select, $this->visibilityFilter, $filterType);
            $appliedFilters[$this->getFilterAlias($this->visibilityFilter)] = true;
        }

        foreach ($this->nonCustomAttributesFilters  as $filter) {
            $alias = $this->getFilterAlias($filter);

            if (!array_key_exists($alias, $appliedFilters)) {
                $isApplied = $this->applyFilterStrategy($filter, $select);
                if ($isApplied) {
                    $appliedFilters[$alias] = true;
                }
            }
        }

        return $select;
    }

    /**
     * @param Select          $select
     * @param $filters
     * @return Select
     */
    public function applyCustomAttributeFilter(Select $select, $filters)
    {
        $select = clone $select;
        $mainTableAlias = $this->extractTableAliasFromSelect($select);
        $attributes = [];

        foreach ($filters as $filter) {
            $filterJoinAlias = $this->getFilterAlias($filter);

            $attributeId = $this->getAttributeIdByCode($filter->getField());

            if ($attributeId === null) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid attribute id for field: %s', $filter->getField())
                );
            }

            $attributes[] = $attributeId;

            $select->joinInner(
                [$filterJoinAlias => $this->resourceConnection->getTableName('catalog_product_index_eav')],
                $this->conditionManager->combineQueries(
                    $this->getJoinConditions($attributeId, $mainTableAlias, $filterJoinAlias),
                    Select::SQL_AND
                ),
                []
            );

            $select->where(
                $this->conditionManager->generateCondition(
                    sprintf('%s.value', $filterJoinAlias),
                    is_array($filter->getValue()) ? 'in' : '=',
                    $filter->getValue()
                )
            );
        }

        return $select;
    }
    /**
     * Returns Joins conditions for table catalog_product_index_eav
     *
     * @param int $attrId
     * @param string $mainTable
     * @param string $joinTable
     * @return array
     */
    private function getJoinConditions($attrId, $mainTable, $joinTable)
    {
        if ($this->isOldVersion()) {
            return [
                sprintf('`%s`.`entity_id` = `%s`.`entity_id`', $mainTable, $joinTable),
                $this->conditionManager->generateCondition(
                    sprintf('%s.attribute_id', $joinTable),
                    '=',
                    $attrId
                ),
                $this->conditionManager->generateCondition(
                    sprintf('%s.store_id', $joinTable),
                    '=',
                    (int) $this->storeManager->getStore()->getId()
                )
            ];
        } else {
            return [
                sprintf('`%s`.`entity_id` = `%s`.`entity_id`', $mainTable, $joinTable),
                sprintf('`%s`.`source_id` = `%s`.`source_id`', $mainTable, $joinTable),
                $this->conditionManager->generateCondition(
                    sprintf('%s.attribute_id', $joinTable),
                    '=',
                    $attrId
                ),
                $this->conditionManager->generateCondition(
                    sprintf('%s.store_id', $joinTable),
                    '=',
                    (int) $this->storeManager->getStore()->getId()
                )
            ];
        }
    }

    /**
     * @return bool
     */
    public function isOldVersion()
    {
        $catalogVersion = $this->moduleList->getOne('Magento_Catalog')['setup_version'];
        if (version_compare($catalogVersion, '2.1.4', '<')) {
            return true;
        }
        return true;
    }

    /**
     * Returns attribute id by code
     *
     * @param string $field
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeIdByCode($field)
    {
        $attr = $this->eavConfig->getAttribute(Product::ENTITY, $field);
        return ($attr && $attr->getId()) ? (int) $attr->getId() : null;
    }

    /**
     * Extracts alias for table that is used in FROM clause in Select
     *
     * @param Select $select
     * @return string|null
     * @throws \Zend_Db_Select_Exception
     */
    private function extractTableAliasFromSelect(Select $select)
    {
        $fromArr = array_filter(
            $select->getPart(Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }

    /**
     * @param Select $select
     * @param        $stockValues
     * @param        $type
     * @param        $showOutOfStockFlag
     * @return Select
     */
    public function applyStockStatusFilter(Select $select, $stockValues, $type, $showOutOfStockFlag)
    {
        if ($type !== self::FILTER_ENTITY_AND_SUB_PRODUCTS && $type !== self::FILTER_JUST_ENTITY) {
            throw new \InvalidArgumentException(sprintf('Invalid filter type: %s', $type));
        }

        $select = clone $select;
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $this->addMainStockStatusJoin($select, $stockValues, $mainTableAlias, $showOutOfStockFlag);

        if ($type === self::FILTER_ENTITY_AND_SUB_PRODUCTS) {
            $this->addSubProductsStockStatusJoin($select, $stockValues, $mainTableAlias, $showOutOfStockFlag);
        }

        return $select;
    }

    /**
     * @param Select $select
     * @param        $stockValues
     * @param        $mainTable
     * @param        $showOutOfStockFlag
     */
    private function addMainStockStatusJoin(Select $select, $stockValues, $mainTable, $showOutOfStockFlag)
    {
        $catalogInventoryTable = $this->resourceConnection->getTableName('cataloginventory_stock_status');
        $select->joinInner(
            ['stock_index' => $catalogInventoryTable],
            $this->conditionManager->combineQueries(
                [
                    sprintf('stock_index.product_id = %s.entity_id', $mainTable),
                    $this->conditionManager->generateCondition(
                        'stock_index.website_id',
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),

                    $showOutOfStockFlag
                        ? ''
                        : $this->conditionManager->generateCondition(
                        'stock_index.stock_status',
                        is_array($stockValues) ? 'in' : '=',
                        $stockValues
                    ),

                    $this->conditionManager->generateCondition(
                        'stock_index.stock_id',
                        '=',
                        (int) $this->stockRegistry->getStock()->getStockId()
                    ),
                ],

                Select::SQL_AND
            ),
            []
        );
    }

    /**
     * @param Select $select
     * @param        $stockValues
     * @param        $mainTable
     * @param        $showOutOfStockFlag
     */
    private function addSubProductsStockStatusJoin(Select $select, $stockValues, $mainTable, $showOutOfStockFlag)
    {
        $catalogInventoryTable = $this->resourceConnection->getTableName('cataloginventory_stock_status');

        if ($this->isOldVersion()) {
            $string = 'sub_products_stock_index.product_id = %s.entity_id';
        } else {
            $string = 'sub_products_stock_index.product_id = %s.source_id';
        }

        $select->joinInner(
            ['sub_products_stock_index' => $catalogInventoryTable],
            $this->conditionManager->combineQueries(
                [
                    sprintf($string, $mainTable),
                    $this->conditionManager->generateCondition(
                        'sub_products_stock_index.website_id',
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),

                    $showOutOfStockFlag
                        ? ''
                        : $this->conditionManager->generateCondition(
                        'sub_products_stock_index.stock_status',
                        is_array($stockValues) ? 'in' : '=',
                        $stockValues
                    ),

                    $this->conditionManager->generateCondition(
                        'sub_products_stock_index.stock_id',
                        '=',
                        (int) $this->stockRegistry->getStock()->getStockId()
                    ),
                ],

                Select::SQL_AND
            ),
            []
        );
    }

    /**
     * Applies visibility filter through join or where condition
     *
     * @param Select $select
     * @param $filter
     * @param string $type
     * @return Select
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyVisibilityFilter(Select $select, $filter, $type)
    {
        if ($type !== self::FILTER_BY_JOIN && $type !== self::FILTER_BY_WHERE) {
            throw new \InvalidArgumentException(sprintf('Invalid filter type: %s', $type));
        }

        $select = clone $select;

        $type === self::FILTER_BY_JOIN
            ? $this->applyFilterByJoin($filter, $select)
            : $this->applyFilterByWhere($filter, $select);

        return $select;
    }

    /**
     * Applies filter by visibility as inner join
     *
     * @param Select $select
     * @param $filter
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function applyFilterByJoin($filter, Select $select)
    {
        $mainTable = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            ['visibility_filter' => $this->resourceConnection->getTableName('catalog_product_index_eav')],
            $this->conditionManager->combineQueries(
                [
                    sprintf('%s.entity_id = visibility_filter.entity_id', $mainTable),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.attribute_id',
                        '=',
                        $this->getVisibilityAttributeId()
                    ),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.value',
                        is_array($filter->getValue()) ? 'in' : '=',
                        $filter->getValue()
                    ),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.store_id',
                        '=',
                        $this->storeManager->getStore()->getId()
                    ),
                ],
                Select::SQL_AND
            ),
            []
        );
    }

    /**
     * Applies filter by visibility as where condition
     *
     * @param Select $select
     * @param $filter
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function applyFilterByWhere($filter, Select $select)
    {
        $mainTable = $this->extractTableAliasFromSelect($select);

        $select->where(
            $this->conditionManager->combineQueries(
                [
                    $this->conditionManager->generateCondition(
                        sprintf('%s.attribute_id', $mainTable),
                        '=',
                        $this->getVisibilityAttributeId()
                    ),

                    $this->conditionManager->generateCondition(
                        sprintf('%s.value', $mainTable),
                        is_array($filter->getValue()) ? 'in' : '=',
                        $filter->getValue()
                    ),

                    $this->conditionManager->generateCondition(
                        sprintf('%s.store_id', $mainTable),
                        '=',
                        $this->storeManager->getStore()->getId()
                    ),
                ],

                Select::SQL_AND
            )
        );
    }

    /**
     * @return int
     */
    private function getVisibilityAttributeId()
    {
        $attr = $this->eavConfig->getAttribute(
            Product::ENTITY,
            self::VISIBILITY_FILTER_FIELD
        );

        return (int) $attr->getId();
    }


    /**
     * @param FilterInterface $filter
     * @param Select          $select
     * @return bool
     */
    public function applyFilterStrategy(FilterInterface $filter, Select $select)
    {
        $applied = $this->applyExclusionStrategy($filter, $select);

        if (!$applied) {
            $attribute = $this->getAttributeByCode($filter->getField());
            if ($attribute) {
                if ($filter->getType() === self::TYPE_TERM
                    && in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true)
                ) {
                    $applied = false;
                } elseif ($attribute->getBackendType() === AbstractAttribute::TYPE_STATIC) {
                    $applied = $this->applyStaticAttributeStrategy($filter, $select);
                }
            }
        }

        return $applied;
    }

    /**
     * @param string $field
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode($field)
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $field);
    }

    /**
     * @param FilterInterface $filter
     * @param Select          $select
     * @return bool
     */
    public function applyExclusionStrategy(FilterInterface $filter, Select $select)
    {
        if (!in_array($filter->getField(), $this->validFieldsForExclusionStrategy, true)) {
            return false;
        }

        if ($filter->getField() === 'price') {
            $select = $this->applyPriceFilter($filter, $select);
        } elseif ($filter->getField() === 'category_ids') {
            $select = $this->applyCategoryFilter($filter, $select);
        }
        return $select;
    }

    /**
     * @param FilterInterface $filter
     * @param Select          $select
     * @return bool
     */
    private function applyPriceFilter(FilterInterface $filter, Select $select)
    {
        $alias = $this->getFilterAlias($filter);
        $tableName = $this->resourceConnection->getTableName('catalog_product_index_price');
        $mainTable = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName
            ],
            $this->resourceConnection->getConnection()->quoteInto(
                sprintf('%s.entity_id = price_index.entity_id AND price_index.website_id = ?', $mainTable),
                $this->storeManager->getWebsite()->getId()
            ),
            []
        );

        return true;
    }

    /**
     * @param        $filter
     * @param Select $select
     * @return bool
     */
    private function applyCategoryFilter(FilterInterface $filter, Select $select) {
        $alias = $this->getFilterAlias($filter);
        $tableName = $this->resourceConnection
            ->getTableName('catalog_category_product_index');

        $mainTable = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName
            ],

            $this->resourceConnection->getConnection()->quoteInto(
                sprintf(
                    '%s.entity_id = category_ids_index.product_id AND category_ids_index.store_id = ?',
                    $mainTable
                ),
                $this->storeManager->getStore()->getId()
            ),
            []
        )->where('category_ids_index.category_id = '. $filter->getValue());
        ;

        return true;
    }

    /**
     * @param        $filter
     * @param Select $select
     * @return bool
     */
    public function applyStaticAttributeStrategy($filter, Select $select)
    {
        $attribute = $this->getAttributeByCode($filter->getField());
        $alias = $this->getFilterAlias($filter);
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [$alias => $attribute->getBackendTable()],
            sprintf('%s.entity_id = ', $mainTableAlias)
            . $this->resourceConnection->getConnection()
                ->quoteIdentifier("$alias.entity_id"),
            []
        );

        return true;
    }

    /**
     * @param $select
     * @return mixed
     */
    public function processDimensions(Select $select)
    {
        $query = $this->conditionManager
            ->combineQueries(
                $this->prepareDimension($this->dimensions),
                Select::SQL_OR
            );

        if (!empty($query)) {
            $select->where(
                $this->conditionManager->wrapBrackets($query)
            );
        }

        return $select;
    }

    /**
     * Prepares where conditions from dimensions
     *
     * @param Dimension[] $dimensions
     * @return string[]
     */
    private function prepareDimension(array $dimensions)
    {
        $preparedDimensions = [];

        foreach ($dimensions as $dimension) {
            if ('scope' === $dimension->getName()) {
                continue;
            }

            $preparedDimensions[] = $this->conditionManager
                ->generateCondition(
                    $dimension->getName(),
                    '=',
                    $this->dimensionScopeResolver->getScope($dimension->getValue())->getId()
                );
        }

        return $preparedDimensions;
    }
}
