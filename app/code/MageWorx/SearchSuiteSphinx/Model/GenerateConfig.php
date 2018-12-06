<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Model;

use \MageWorx\SearchSuiteSphinx\Helper\Data as SearchSuiteSphinxHelper;
use \MageWorx\SearchSuiteSphinx\Helper\Sphinx as HelperSphinx;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\ResourceConnection;

/**
 * Prepare data for sphinx.conf
 */
class GenerateConfig
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * SearchSuiteSphinx Data helper
     *
     * @var \MageWorx\SearchSuiteSphinx\Helper\Data
     */
    protected $searchSuiteSphinxHelper;

    /**
     * SearchSuiteSphinx Sphinx helper
     *
     * @var \MageWorx\SearchSuiteSphinx\Helper\Sphinx
     */
    protected $helperSphinx;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Default settings array used in sphinx.conf
     *
     * @var array
     */
    protected $defaultSettings = [
        '{$sql_port}' => 3306,
        '{$index_files_path}' => '/var/lib/sphinxsearch/index/',
        '{$sphinx_port}' => 9312,
        '{$pid_file_path}' => '/etc/sphinxsearch/',
        '{$log_files_path}' => '/var/log/sphinxsearch/',
        '{$binlog_files_path}' => '/var/lib/sphinxsearch/'
    ];

    /**
     * Configured settings array used in sphinx.conf
     *
     * @var array
     */
    protected $configuredSettings = array();

    /**
     * GenerateConfig constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param SearchSuiteSphinxHelper $searchSuiteSphinxHelper
     * @param HelperSphinx $helperSphinx
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $sesourceConnection
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        SearchSuiteSphinxHelper $searchSuiteSphinxHelper,
        HelperSphinx $helperSphinx,
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection
    ) {
    
        $this->objectManager = $objectManager;
        $this->searchSuiteSphinxHelper = $searchSuiteSphinxHelper;
        $this->helperSphinx = $helperSphinx;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
        
        $this->setConfigData();
    }

    protected function setConfigData()
    {
        $userSettings = array_merge(
            $this->getDbConnectionConfig(),
            $this->getIndexConfig(),
            $this->getSearchdConfig()
        );
        $userSettings = $this->optimizeArray($userSettings);

        $this->configuredSettings = array_merge(
            $this->defaultSettings,
            $userSettings
        );
    }
    
    /**
     * Get configuredSettings array
     *
     * @return array
     */
    public function getConfigData()
    {
        return $this->configuredSettings;
    }

    /**
     * Get settings used in database connection source
     *
     * @return array
     */
    protected function getDbConnectionConfig()
    {
        $dbConfig = $this->resourceConnection->getConnection('default_setup')->getConfig();
        
        $dbInfo = [
            '{$sql_host}' => $dbConfig['host'],
            '{$sql_user}' => $dbConfig['username'],
            '{$sql_password}' => $dbConfig['password'],
            '{$sql_database_name}' => $dbConfig['dbname'],
            '{$sql_port}' => $this->searchSuiteSphinxHelper->getSqlPort()
        ];

        return $dbInfo;
    }

    /**
     * Get settings used in index section
     *
     * @return array
     */
    protected function getIndexConfig()
    {
        $indexInfo = '';
        $indexFilePath = $this->helperSphinx->getSphinxConfigIndexTemplatePath();

        foreach ($this->helperSphinx->getAllIndexesName() as $storeId => $index) {
            $indexData = [
                '{$index_name}'       => $index,
                '{$store_id}'         => $storeId,
                '{$index_files_path}' => $this->searchSuiteSphinxHelper->getSphinxIndexPath()
            ];
            $indexInfo .= strtr(file_get_contents($indexFilePath), $indexData);
        }

        return ['{$indexies}' => $indexInfo];
    }

    /**
     * Get settings used in searchd section
     *
     * @return array
     */
    protected function getSearchdConfig()
    {
        $searchdInfo = [
            '{$sphinx_host}' => $this->searchSuiteSphinxHelper->getSphinxHost(),
            '{$sphinx_port}' => $this->searchSuiteSphinxHelper->getSphinxPort(),
            '{$sphinx_port_mysql}' => $this->searchSuiteSphinxHelper->getSphinxPort() + 1,
            '{$pid_file_path}' => $this->searchSuiteSphinxHelper->getSphinxPidFilePath(),
            '{$log_files_path}' => $this->searchSuiteSphinxHelper->getSphinxLogFilesPath(),
            '{$binlog_files_path}' => $this->searchSuiteSphinxHelper->getSphinxBinlogFilesPath()
        ];

        return $searchdInfo;
    }

    /**
     * Return array without empty elements
     *
     * @param $array
     * @return array
     */
    protected function optimizeArray($array)
    {
        $array = array_map('trim', $array);
        $array = array_filter($array);

        return $array;
    }
}
