<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Model\SearchEngine;

use \Magento\Framework\Search\AdapterInterface;
use \Magento\Framework\Search\RequestInterface;
use \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use \Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use \Magento\Framework\Search\Adapter\Mysql\Mapper;
use \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder as AggregationBuilder;
use \MageWorx\SearchSuiteSphinx\Helper\Data as HelperData;
use \MageWorx\SearchSuiteSphinx\Helper\Sphinx as HelperSphinx;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Request\Http as HttpRequest;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Search\Model\QueryFactory;
use MageWorx\SearchSuiteSphinx\Model\IndexBuilder;
use \Psr\Log\LoggerInterface;
use \Sphinx\SphinxClient;

/**
 * Sphinx search Adapter
 */
class AdapterSphinx implements AdapterInterface
{
    /**
     * @var \SphinxClient
     */
    protected $connection = null;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    protected $temporaryStorageFactory;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Mapper
     */
    protected $mapper;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder
     */
    protected $aggregationBuilder;

    /**
     * @var \MageWorx\SearchSuiteSphinx\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\SearchSuiteSphinx\Helper\Sphinx
     */
    protected $helperSphinx;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var IndexBuilder
     */
    protected $indexBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AdapterSphinx constructor.
     *
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param ResponseFactory $responseFactory
     * @param Mapper $mapper
     * @param AggregationBuilder $aggregationBuilder
     * @param HelperData $helperData
     * @param HelperSphinx $helperSphinx
     * @param StoreManagerInterface $storeManager
     * @param HttpRequest $httpRequest
     * @param IndexBuilder $indexBuilder
     * @param LoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        TemporaryStorageFactory $temporaryStorageFactory,
        ResponseFactory $responseFactory,
        Mapper $mapper,
        AggregationBuilder $aggregationBuilder,
        HelperData $helperData,
        HelperSphinx $helperSphinx,
        StoreManagerInterface $storeManager,
        HttpRequest $httpRequest,
        IndexBuilder $indexBuilder,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection
    ) {
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->responseFactory         = $responseFactory;
        $this->mapper                  = $mapper;
        $this->aggregationBuilder      = $aggregationBuilder;
        $this->helperData              = $helperData;
        $this->helperSphinx            = $helperSphinx;
        $this->storeManager            = $storeManager;
        $this->httpRequest             = $httpRequest;
        $this->indexBuilder            = $indexBuilder;
        $this->logger                  = $logger;
        $this->resourceConnection      = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function query(RequestInterface $request)
    {
        $connection   = $this->getConnection();
        $requestQuery = $this->getRequestQuery();
        $sphinxResult = $this->getSphinxResult($connection, $requestQuery);
        if (!$sphinxResult) {
            $error = $connection->getLastError();
            $this->logger->error('MageWorx_SearchSuiteSphinx ERROR: ' . $error);
            $warning = $connection->getLastWarning();
            $this->logger->error('MageWorx_SearchSuiteSphinx WARNING: ' . $warning);
        }

        $filtredIds = $this->filterResults($sphinxResult, $request);
        $documents  = $this->getDocuments($sphinxResult, $filtredIds);

        $temporaryStorage = $this->temporaryStorageFactory->create();
        $table            = $temporaryStorage->storeApiDocuments($this->prepareDataForTable($documents, $filtredIds));

        $aggregations = $this->aggregationBuilder->build($request, $table, $documents);
        $response     = [
            'documents'    => $documents,
            'aggregations' => $aggregations,
        ];

        return $this->responseFactory->create($response);
    }

    /**
     * @param array $sphinxResult
     * @param RequestInterface $request
     * @return mixed
     */
    public function filterResults($sphinxResult, $request)
    {
        $filtredIds = [];
        if (count($this->httpRequest->getParams()) > 1) {
            $ids = $this->getSphinxResultIds($sphinxResult);

            $select = $this->indexBuilder->build($request);
            $select->reset(\Zend_Db_Select::COLUMNS)
                   ->columns(['entity_id'])
                   ->where('`search_index`.`entity_id` IN(?)', $ids);

            $filtredIds = $this->loadFiltresIds($select);
        }

        return $filtredIds;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getSphinxResultIds($data)
    {
        $ids = [];

        if (isset($data['matches'])) {
            foreach ($data['matches'] as $match) {
                $ids[] = $match['attrs']['entity_id'];
            }
        }

        return $ids;
    }

    /**
     * @param Zend_Db_Select $select
     * @return array
     */
    private function loadFiltresIds($select)
    {
        $result     = [];
        $connection = $this->resourceConnection->getConnection();
        $rawResult  = $connection->fetchAll($select);

        foreach ($rawResult as $ids) {
            foreach ($ids as $entity => $id) {
                $result[] = $id;
            }
        }

        return $result;
    }

    /**
     * @param \SphinxClient $connection
     * @param string $requestQuery
     * @return mixed
     */
    protected function getSphinxResult($connection, $requestQuery)
    {
        $query           = '*' . $requestQuery . '*';
        $storeId         = $this->storeManager->getStore()->getId();
        $sphinxIndexName = $this->resourceConnection->getTableName($this->helperSphinx->getIndexName($storeId));
        $sphinxIndexName .= ' delta_' . $sphinxIndexName;
        $connection->SetRankingMode($this->helperData->getRankingMode());
        $connection->SetSortMode(SphinxClient::SPH_SORT_RELEVANCE);
        $connection->SetLimits(0, 1000, 1000);
        $connection->SetMatchMode($this->helperData->getMatchMode());
        $connection->SetFilter('old', [0]);

        return $connection->Query($query, $sphinxIndexName);
    }

    /**
     * Open Sphinx connection
     *
     * @return \SphinxClient
     */
    protected function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = $this->helperSphinx->getInstance();
        }

        return $this->connection;
    }

    /**
     * Get search query text
     *
     * @retrun string
     */
    private function getRequestQuery()
    {
        return $this->httpRequest->getParam(QueryFactory::QUERY_VAR_NAME) ? $this->httpRequest->getParam(
            QueryFactory::QUERY_VAR_NAME
        ) : '';
    }

    /**
     * Create Documents based on Sphinx result
     *
     * @return array
     */
    private function getDocuments($data, $filtredIds)
    {
        $documents = [];
        if (isset($data['matches'])) {
            $matches = $data['matches'];

            foreach ($matches as $match) {
                if (count($this->httpRequest->getParams()) > 1) {
                    if (array_search($match['attrs']['entity_id'], $filtredIds) !== false) {
                        $documents[$match['attrs']['entity_id']] = [
                            'entity_id' => $match['attrs']['entity_id'],
                            'score'     => $match['weight']
                        ];
                    }
                } else {
                    $documents[$match['attrs']['entity_id']] = [
                        'entity_id' => $match['attrs']['entity_id'],
                        'score'     => $match['weight']
                    ];
                }
            }
        }

        return $documents;
    }

    /**
     * @param $data
     * @param $filtredIds
     * @return \Magento\Framework\Api\Search\Document[]
     */
    private function prepareDataForTable($data, $filtredIds)
    {
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $entityMetadata = $objectManager->create('Magento\Framework\Search\EntityMetadata', ['entityId' => 'id']);
        $idKey          = $entityMetadata->getEntityId();

        /** @var \Magento\Framework\Search\Adapter\Mysql\DocumentFactory $documentFactory */
        $documentFactory = $objectManager->create(
            'Magento\Framework\Search\Adapter\Mysql\DocumentFactory',
            ['entityMetadata' => $entityMetadata]
        );

        /** @var \Magento\Framework\Api\Search\Document[] $documents */
        $documents = [];


        foreach ($data as $match) {
            if (count($this->httpRequest->getParams()) > 1) {
                if (array_search($match['entity_id'], $filtredIds) !== false) {
                    $rawDocument = [
                        $idKey  => $match['entity_id'],
                        'score' => $match['score']
                    ];
                    $documents[] = $documentFactory->create($rawDocument);
                }
            } else {
                $rawDocument = [
                    $idKey  => $match['entity_id'],
                    'score' => $match['score']
                ];
                $documents[] = $documentFactory->create($rawDocument);
            }
        }

        return $documents;
    }
}
