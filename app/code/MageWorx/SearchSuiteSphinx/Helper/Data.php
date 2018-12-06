<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * SearchSuiteSphinx config data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * XML config path sphinx host
     */
    const XML_PATH_HOST = 'mageworx_searchsuite/searchsuitesphinx_main/host';

    /**
     * XML config path sphinx port
     */
    const XML_PATH_PORT = 'mageworx_searchsuite/searchsuitesphinx_main/port';

    /**
     * XML config path sql port
     */
    const XML_PATH_SQL_PORT = 'mageworx_searchsuite/searchsuitesphinx_main/sql_port';

    /**
     * XML config path sphinx index path
     */
    const XML_PATH_INDEX_PATH = 'mageworx_searchsuite/searchsuitesphinx_main/index_path';

    /**
     * XML config path log files path
     */
    const XML_PATH_LOG_FILES_PATH = 'mageworx_searchsuite/searchsuitesphinx_main/log_files_path';

    /**
     * XML config path binlog files path
     */
    const XML_PATH_BINLOG_FILES_PATH = 'mageworx_searchsuite/searchsuitesphinx_main/binlog_files_path';

    /**
     * XML config path timout
     */
    const XML_PATH_TIMEOUT = 'mageworx_searchsuite/searchsuitesphinx_main/timeout';

    /**
     * XML config path ranking mode
     */
    const XML_PATH_MAX_PRODUCT_COUNT_IN_DELTA = 'mageworx_searchsuite/searchsuitesphinx_main/max_product_count_in_delta';

    /**
     * XML config path ranking mode
     */
    const XML_PATH_RANKING_MODE = 'mageworx_searchsuite/searchsuitesphinx_main/ranking_mode';

    /**
     * XML config path match mode
     */
    const XML_PATH_MATCH_MODE = 'mageworx_searchsuite/searchsuitesphinx_main/match_mode';

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * Data constructor.
     *
     * @param DirectoryList $directoryList
     * @param Context $context
     */
    public function __construct(
        DirectoryList $directoryList,
        Context $context
    ) {
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    /**
     * Retrieve Sphinx host
     *
     * @param int $storeId
     * @return string
     */
    public function getSphinxHost($storeId = null)
    {
        $host = $this->scopeConfig->getValue(
            self::XML_PATH_HOST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($host)) {
            $host = '127.0.0.1';
        }

        return $host;
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isLocalSphinxServer($storeId = null)
    {
        $local = ['127.0.0.1', 'localhost'];

        return in_array(trim($this->getSphinxHost($storeId)), $local);
    }

    /**
     * Retrieve Sphinx port
     *
     * @param int $storeId
     * @return int
     */
    public function getSphinxPort($storeId = null)
    {
        $port = (int)$this->scopeConfig->getValue(
            self::XML_PATH_PORT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($port)) {
            $port = 9312;
        }

        return $port;
    }

    /**
     * Retrieve Sql port
     *
     * @param int $storeId
     * @return int
     */
    public function getSqlPort($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_SQL_PORT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Sphinx index path
     *
     * @param int $storeId
     * @return string
     */
    public function getSphinxIndexPath($storeId = null)
    {
        $path = $this->scopeConfig->getValue(
            self::XML_PATH_INDEX_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($path)) {
            $path = 'var/mageworx_sphinxsearch/';
        }

        return $this->addMagentoRootPath($path);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function addMagentoRootPath($path)
    {
        return $this->directoryList->getRoot() . '/' . $path;
    }

    /**
     * Retrieve Sphinx pid file path
     *
     * @param int $storeId
     * @return string
     */
    public function getSphinxPidFilePath($storeId = null)
    {
        return $this->addMagentoRootPath('app/etc/');
    }

    /**
     * Retrieve Sphinx log files path
     *
     * @param int $storeId
     * @return string
     */
    public function getSphinxLogFilesPath($storeId = null)
    {
        $path = $this->scopeConfig->getValue(
            self::XML_PATH_LOG_FILES_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($path)) {
            $path = 'var/mageworx_sphinxsearch/';
        }

        return $this->addMagentoRootPath($path);
    }

    /**
     * Retrieve Sphinx binlog files path
     *
     * @param int $storeId
     * @return string
     */
    public function getSphinxBinlogFilesPath($storeId = null)
    {
        $path = $this->scopeConfig->getValue(
            self::XML_PATH_BINLOG_FILES_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($path)) {
            $path = 'var/mageworx_sphinxsearch/';
        }

        return $this->addMagentoRootPath($path);
    }

    /**
     * Retrieve Sphinx timeout
     *
     * @param int $storeId
     * @return int
     */
    public function getSphinxTimeout($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_TIMEOUT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Sphinx ranking mode
     *
     * @param int $storeId
     * @return int
     */
    public function getRankingMode($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_RANKING_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Sphinx match mode
     *
     * @param int $storeId
     * @return int
     */
    public function getMatchMode($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MATCH_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Sphinx match mode
     *
     * @param int $storeId
     * @return int
     */
    public function getMaxProductCountInDeltaIndex($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MAX_PRODUCT_COUNT_IN_DELTA,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
