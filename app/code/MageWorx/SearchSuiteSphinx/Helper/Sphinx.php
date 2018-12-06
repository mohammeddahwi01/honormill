<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Helper;

use Magento\Setup\Exception;
use \Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use \MageWorx\SearchSuiteSphinx\Helper\Data as SearchsuiteHelper;
use \Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use \Magento\Framework\Exception\FileSystemException;
use MageWorx\SearchSuiteSphinx\Model\GenerateConfigFactory;
use Magento\Framework\Filesystem\Io\File;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Store\Model\StoreManagerInterface;
use MageWorx\SearchSuiteSphinx\Model\ResourceModel\ProductDeltaIndex;
use Magento\Framework\Exception\InputException;
use Magento\Store\Model\Store;

/**
 * SearchSuiteSphinx Sphinx data helper
 */
class Sphinx extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_FILE_NAME = 'sphinx.conf';
    const STATUS_ERROR     = 0;
    const STATUS_SUCCESS   = 1;
    const SEARCH_TABLE     = 'catalogsearch_fulltext_scope';
    /**
     * SearchSuite data helper
     *
     * @var \MageWorx\SearchSuiteSphinx\Helper\Data
     */
    protected $searchsuiteHelper;

    /**
     * Sphinx client object
     *
     * @var SphinxClient
     */
    protected $_instance = null;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \SphinxClient
     */
    protected $sphinxClient;

    /**
     * @var \MageWorx\SearchSuiteSphinx\Model\GenerateConfigFactory
     */
    protected $generateConfigFactory;

    /**
     * @var WriteInterface
     */
    protected $directory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var File
     */
    protected $io;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductDeltaIndex
     */
    protected $productDeltaIndex;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceProduct;

    /**
     * Sphinx constructor.
     *
     * @param ResourceProduct $resourceProduct
     * @param \Magento\Framework\Registry $registry
     * @param ProductDeltaIndex $productDeltaIndex
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resourceConnection
     * @param File $io
     * @param GenerateConfigFactory $generateConfigFactory
     * @param Filesystem $filesystem
     * @param ObjectManagerInterface $objectManager
     * @param Data $searchsuiteHelper
     * @param \Sphinx\SphinxClient $sphinxClient
     * @throws FileSystemException
     */
    public function __construct(
        ResourceProduct $resourceProduct,
        \Magento\Framework\Registry $registry,
        ProductDeltaIndex $productDeltaIndex,
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection,
        File $io,
        GenerateConfigFactory $generateConfigFactory,
        Filesystem $filesystem,
        ObjectManagerInterface $objectManager,
        SearchsuiteHelper $searchsuiteHelper,
        \Sphinx\SphinxClient $sphinxClient
    ) {
        $this->resourceProduct       = $resourceProduct;
        $this->registry              = $registry;
        $this->productDeltaIndex     = $productDeltaIndex;
        $this->storeManager          = $storeManager;
        $this->resourceConnection    = $resourceConnection;
        $this->io                    = $io;
        $this->objectManager         = $objectManager;
        $this->searchsuiteHelper     = $searchsuiteHelper;
        $this->sphinxClient          = $sphinxClient;
        $this->filesystem            = $filesystem;
        $this->directory             = $filesystem->getDirectoryWrite(DirectoryList::APP);
        $this->generateConfigFactory = $generateConfigFactory;
    }

    /**
     * Return Sphinx connection
     *
     * @return \SphinxClient object
     */
    public function getInstance()
    {
        if (!$this->_instance) {
            $this->_instance = $this->sphinxClient;
            $this->_instance->SetServer(
                $this->searchsuiteHelper->getSphinxHost(),
                $this->searchsuiteHelper->getSphinxPort()
            );
            $this->_instance->SetConnectTimeout($this->searchsuiteHelper->getSphinxTimeout());
            $this->_instance->SetArrayResult(true);
        }

        return $this->_instance;
    }

    /**
     * @return string
     */
    public function getSphinxConfigTemplatePath()
    {
        return $this->getPath('sphinx.conf');
    }

    /**
     * @return string
     */
    public function getSphinxConfigIndexTemplatePath()
    {
        return $this->getPath('index.conf');
    }

    /**
     * @param $pathTo
     * @return string
     */
    protected function getPath($pathTo)
    {
        return $this->objectManager
                ->get('\Magento\Framework\Module\Dir\Reader')
                ->getModuleDir(
                    \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                    'MageWorx_SearchSuiteSphinx'
                ) . '/conf/' . $pathTo;
    }

    /**
     * @return string
     */
    public function getPreparedConfigFileFullPath()
    {
        return $this->getSphinxDirectoryFullPath() . self::CONFIG_FILE_NAME;
    }

    /**
     * @return string
     */
    public function getSphinxDirectoryFullPath()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::CONFIG)->getAbsolutePath();
    }

    /**
     * @return array
     */
    public function createSphinxConfig()
    {
        $configFilePath = $this->getSphinxConfigTemplatePath();
        $result         = ['status' => self::STATUS_ERROR, 'msg' => ''];

        if (file_exists($configFilePath)) {
            $configModel = $this->generateConfigFactory->create();
            $configData  = $configModel->getConfigData();

            $configContent = strtr(file_get_contents($configFilePath), $configData);
            try {
                $this->stream = $this->directory->openFile('etc/' . self::CONFIG_FILE_NAME);

                $this->stream->write($configContent);
                $this->stream->close();

                $result['status'] = self::STATUS_SUCCESS;

                $result['msg'] = __('Config file successfully created in %1', $this->getPreparedConfigFileFullPath());

                if (!$this->searchsuiteHelper->isLocalSphinxServer()) {
                    $result['msg'] .= '<br>' .
                        __('You should move config file to Sphinx server manually.');
                }
            } catch (FileSystemException $e) {
                $result['msg'] = $e->getMessage();
            }
        } else {
            $result['msg'] = __('Template for config file %1 doesn\'t exist', $configFilePath);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function checkSphinxStatus()
    {
        if ($this->searchsuiteHelper->isLocalSphinxServer()) {
            if (!file_exists($this->getPreparedConfigFileFullPath())) {
                $status = self::STATUS_ERROR;
                $msg    = __('Config file does\'t exist. Please check the settings below and generate config file.');

                return ['status' => $status, 'msg' => $msg];
            }
            $result = $this->checkLocalSphinxSearch();
            if ($result['status'] == self::STATUS_SUCCESS) {
                return $result;
            }
        }

        $instance = $this->getInstance();
        $status   = $instance->Status();
        $msg      = htmlentities($instance->GetLastError());

        if ($status !== false) {
            $msg .= ' ' . __('Sphinx Search is running.');
        }

        return ['status' => $status, 'msg' => $msg];
    }

    /**
     * @return array
     */
    public function runSphinxReindex()
    {
        $this->productDeltaIndex->emptyProductDelta();

        if (!$this->createSphinxFolders()) {
            $msg    = __("Can't create directory for indexes. Permission denied.");
            $status = self::STATUS_ERROR;

            return ['status' => $status, 'msg' => $msg];
        }

        $runScript = 'indexer --config ' . $this->getPreparedConfigFileFullPath() . ' --rotate --all';
        $reindex   = $this->runExec($runScript);

        if ($reindex['status'] == self::STATUS_SUCCESS) {
            $reindex['msg'] = __('Sphinx search reindex ran successfully.');
        }

        return $reindex;
    }

    /**
     * @param $ids
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateSphinxDeltaReindex($ids)
    {
        if (!isset($ids) || empty($ids)) {
            throw new InputException(__("We can't rebuild the index for an undefined product."));
        }
        try {
            $this->productDeltaIndex->saveNewData($ids);
            $this->runSphinxDeltaReindex();
            $this->setOldFlagsInMainIndex($ids);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @return array
     */
    protected function runSphinxDeltaReindex()
    {
        $runScript = 'indexer --config ' . $this->getPreparedConfigFileFullPath() . ' ';
        foreach ($this->getAllIndexesName() as $indexName) {
            $runScript .= ' delta_' . $indexName . ' ';
        }

        $runScript .= ' --rotate --sighup-each';

        return $this->runExec($runScript);
    }

    /**
     * @param $ids
     */
    protected function setOldFlagsInMainIndex($ids)
    {
        $registryData = $this->registry->registry('mageworx_sphinxsearch_product_update');

        foreach ($ids as $id) {
            if (!empty($registryData[$id])) {
                $dataToUpdate = [];
                foreach ($registryData[$id] as $row) {
                    $documentId                                  = $this->getSphinxDocumentId(
                        $row['entity_id'],
                        $row['attribute_id']
                    );
                    $dataToUpdate[$row['store_id']][$documentId] = [1];
                }

                $instance = $this->getInstance();
                foreach ($this->getAllIndexesName() as $storeId => $indexName) {
                    if (!empty($dataToUpdate[$storeId])) {
                        $instance->updateAttributes($indexName, ['old'], $dataToUpdate[$storeId]);
                    }
                    if (!empty($dataToUpdate[Store::DEFAULT_STORE_ID])) {
                        $instance->updateAttributes($indexName, ['old'], $dataToUpdate[Store::DEFAULT_STORE_ID]);
                    }
                }
            }
        }

    }

    /**
     * @param $productId
     * @param $attrId
     * @return float|int
     */
    public function getSphinxDocumentId($productId, $attrId)
    {
        return $productId * 100000 + $attrId;
    }

    /**
     * @return array
     */
    public function runSphinxSearch()
    {
        if ($this->searchsuiteHelper->isLocalSphinxServer()) {
            $check = $this->checkSphinxStatus();

            if ($check['status'] == self::STATUS_SUCCESS) {
                $stop = $this->stopSphinxSearch();
                if ($stop['status'] !== self::STATUS_SUCCESS) {
                    return $stop;
                }
            }

            $runScript = 'searchd --config ' . $this->getPreparedConfigFileFullPath();

            if (!$this->createSphinxFolders()) {
                $msg    = __("Can't create directory for indexes. Permission denied.");
                $status = self::STATUS_ERROR;

                return ['status' => $status, 'msg' => $msg];
            }

            $run = $this->runExec($runScript);

            if ($run['status'] == self::STATUS_SUCCESS) {
                $run['msg'] = __('Sphinx search ran successfully.');
            }

            return $run;
        }

        $msg    = __('You should run Sphinx on remote server manually.');
        $status = self::STATUS_ERROR;

        return ['status' => $status, 'msg' => $msg];
    }

    /**
     * @return int
     */
    public function createSphinxFolders()
    {
        try {
            if (!file_exists($this->searchsuiteHelper->getSphinxPidFilePath())) {
                $this->io->mkdir($this->searchsuiteHelper->getSphinxPidFilePath());
            }

            if (!file_exists($this->searchsuiteHelper->getSphinxBinlogFilesPath())) {
                $this->io->mkdir($this->searchsuiteHelper->getSphinxBinlogFilesPath());
            }

            if (!file_exists($this->searchsuiteHelper->getSphinxIndexPath())) {
                $this->io->mkdir($this->searchsuiteHelper->getSphinxIndexPath());
            }

            if (!file_exists($this->searchsuiteHelper->getSphinxLogFilesPath())) {
                $this->io->mkdir($this->searchsuiteHelper->getSphinxLogFilesPath());
            }
        } catch (Exception $e) {
            return self::STATUS_ERROR;
        }

        return self::STATUS_SUCCESS;

    }

    /**
     * @return array
     */
    public function stopSphinxSearch()
    {
        $runScript = 'searchd --config ' . $this->getPreparedConfigFileFullPath() . ' --stopwait';

        $stop = $this->runExec($runScript);
        if ($stop['status'] == self::STATUS_SUCCESS) {
            $stop['msg'] = __('Sphinx search stopped successfully.');
        }

        return $stop;
    }

    /**
     * @return array
     */
    public function checkLocalSphinxSearch()
    {
        $runScript = 'searchd --config ' . $this->getPreparedConfigFileFullPath() . ' --status';

        $run = $this->runExec($runScript);
        if ($run['status'] == self::STATUS_SUCCESS) {
            $run['msg'] = __('Sphinx search is running.');
        }

        return $run;
    }

    /**
     * Result array [
     *     'status' => status value,
     *     'msg'    => messages from exec
     * ]
     *
     * @param $result
     * @return array
     */
    public function getInfoByExecResult($result)
    {
        $msg              = '';
        $result['status'] = self::STATUS_SUCCESS;
        if (is_array($result['msg'])) {
            foreach ($result['msg'] as $row) {
                if (strpos($row, 'FATAL') !== false) {
                    $msg              .= '<li>' . substr($row, strpos($row, 'FATAL')) . '</li>';
                    $result['status'] = self::STATUS_ERROR;
                    if (strpos($row, 'no valid indexes to serve') !== false) {
                        $msg .= '<b>' . __('Run Sphinx search reindex to resolve this issue.') . '</b>';
                    }
                    if (strpos($row, 'failed to connect to daemon') !== false) {
                        $msg .= '<b>' . __('Run Sphinx search to resolve this issue.') . '</b>';
                    }
                    if (strpos($row, 'stop: failed to read valid pid') !== false) {
                        $msg .= '<b>' . __('Sphinx Search may already be stopped.') . '</b>';
                    }
                    if (strpos($row, 'does not exist or is not readable') !== false) {
                        $msg .= '<b>' . __('Sphinx Search may already be stopped.') . '</b>';
                    }
                } elseif (strpos($row, 'WARNING') !== false) {
                    $msg .= '<li>' . substr($row, strpos($row, 'WARNING')) . '</li>';
                } elseif (strpos($row, 'ERROR') !== false) {
                    $msg              .= '<li>' . substr($row, strpos($row, 'ERROR')) . '</li>';
                    $result['status'] = self::STATUS_ERROR;
                }
            }
        }

        return ['status' => $result['status'], 'msg' => $msg];
    }

    /**
     * @param string $runScript
     * @return array
     */
    public function runExec($runScript)
    {
        $status = self::STATUS_SUCCESS;

        if (function_exists('exec')) {
            exec($runScript, $msg, $status);

            return $this->getInfoByExecResult(['status' => $status, 'msg' => $msg]);
        } else {
            $msg = __(
                    'Running "exec" function is not allowed on this server. 
                Change the settings or run this command manually: '
                ) . '<b>' . $runScript . '</b>';
            $status = self::STATUS_ERROR;

            return ['status' => $status, 'msg' => $msg];
        }
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getIndexName($storeId)
    {
        $index = $this->resourceConnection->getTableName(self::SEARCH_TABLE . $storeId);

        if (!$this->resourceConnection->getConnection()->isTableExists($index)) {
            return false;
        }

        return $index;
    }

    /**
     * @return array
     */
    public function getAllIndexesName()
    {
        $indexes  = [];
        $storeIds = array_keys($this->storeManager->getStores());

        foreach ($storeIds as $storeId) {
            $table = $this->getIndexName($storeId);

            if (!$table) {
                continue;
            }

            $indexes[$storeId] = $table;
        }

        return $indexes;
    }

    /**
     * @param array $products
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllStoreIdsByProducts($products)
    {
        $websites         = $this->storeManager->getWebsites();
        $stores           = [];
        foreach ($websites as $website) {
            $stores[$website->getId()] = $website->getStoreIds();
        }

        $productsWebsites = $this->resourceProduct->getWebsiteIdsByProductIds($products);
        $productsStoreIds = [];
        foreach ($productsWebsites as $productId => $websiteIds) {
            $productsStoreIds[$productId] = [];
            foreach ($websiteIds as $websiteId) {
                $productsStoreIds[$productId] = array_merge(
                    $productsStoreIds[$productId],
                    $stores[$websiteId]
                );
            }
        }

        return $productsStoreIds;
    }
}
