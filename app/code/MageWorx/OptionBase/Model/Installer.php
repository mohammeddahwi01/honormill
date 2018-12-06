<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model;

use \Magento\Framework\App\ResourceConnection;
use \Magento\Framework\Module\Manager as ModuleManager;
use MageWorx\OptionBase\Helper\Data as OptionBaseHelper;

/**
 * Class Installer. Install custom fields from all APO package modules.
 * @package MageWorx\OptionBase\Model
 */
class Installer
{
    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    /**
     * Array of InstallSchema models from APO package.
     *
     * @var array
     */
    protected $installSchema;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Installer constructor.
     *
     * @param OptionBaseHelper $helper
     * @param array $installSchema
     * @param ResourceConnection $resource
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        OptionBaseHelper $helper,
        ResourceConnection $resource,
        ModuleManager $moduleManager,
        $installSchema = []
    ) {
        $this->helper = $helper;
        $this->resource = $resource;
        $this->moduleManager = $moduleManager;
        $this->installSchema = $installSchema;
    }

    /**
     * This method adds full table structure to database.
     */
    public function install()
    {
        foreach ($this->installSchema as $instance) {
            $this->validate($instance);

            $this->processData($instance);
            $this->processIndexes($instance);
            $this->processForeignKeys($instance);
        }
    }

    /**
     * Check if module's class implements InstallSchemaInterface
     *
     * @param mixed $instance
     */
    private function validate($instance)
    {
        if (!$instance instanceof \MageWorx\OptionBase\Model\InstallSchemaInterface) {
            $msg = __(
                'Expected \MageWorx\OptionBase\Model\InstallSchemaInterface, got: %1',
                get_class($instance)
            );
            throw new \Exception($msg);
        }
    }

    /**
     * Create tables and/or setup column data
     *
     * @param mixed $instance
     */
    private function processData($instance)
    {
        $installer = $this->resource;
        $connection = $installer->getConnection();

        $data = $instance->getData();

        foreach ($data as $item) {
            // install to Magento
            // Do not store the data in the catalog_product_entity
            if ($item['table_name'] != 'catalog_product_entity') {
                $tableName = $installer->getTableName($item['table_name']);
                if ($connection->isTableExists($tableName)) {
                    if ($this->isTableColumnMissed($tableName, $item)) {
                        $connection->addColumn(
                            $tableName,
                            $item['field_name'],
                            $item['params']
                        );
                    } elseif ($this->isNeedToRemoveTableColumn($tableName, $item)) {
                        $connection->dropColumn($tableName, $item['field_name']);
                    }
                } elseif (!$this->isMarkedToDrop($item)) {
                    $table = $connection
                        ->newTable($tableName)
                        ->addColumn(
                            $item['field_name'],
                            $item['params']['type'],
                            $this->getColumnValue($item['params'], 'length'),
                            $this->getColumnOptions($item['params']),
                            $this->getColumnValue($item['params'], 'comment')
                        );
                    $connection->createTable($table);
                }
            }

            // install to MageWorx OptionTemplates
            if ($this->moduleManager->isEnabled('MageWorx_OptionTemplates')) {
                if (!empty($item['skip_add_to_template'])) {
                    continue;
                }
                $mageworxTableName = $this->getOptionTemplateTableName($instance, $item['table_name']);
                $mageworxTableName = $installer->getTableName($mageworxTableName);

                if ($connection->isTableExists($mageworxTableName)) {
                    if ($this->isTableColumnMissed($mageworxTableName, $item)) {
                        $connection->addColumn(
                            $installer->getTableName($mageworxTableName),
                            $item['field_name'],
                            $item['params']
                        );
                    } elseif ($this->isNeedToRemoveTableColumn($mageworxTableName, $item)) {
                        $connection->dropColumn($mageworxTableName, $item['field_name']);
                    }
                } elseif (!$this->isMarkedToDrop($item)) {
                    $table = $connection
                        ->newTable($mageworxTableName)
                        ->addColumn(
                            $item['field_name'],
                            $item['params']['type'],
                            $this->getColumnValue($item['params'], 'length'),
                            $this->getColumnOptions($item['params']),
                            $this->getColumnValue($item['params'], 'comment')
                        );
                    $connection->createTable($table);
                }
            }
        }
    }

    public function clearUnusedData($data)
    {
        $installer = $this->resource;

        $field1 = $data['field1'];
        $field2 = $data['field2'];
        $table1 = $installer->getTableName($data['table1']);
        $table2 = $installer->getTableName($data['table2']);

        // create connection
        $connection = $installer->getConnection();

        // Select all $field2 from $table2
        $subSql = $connection->select()
            ->from(
                $table2,
                $field2
            );

        // Select $field1 which is not in the selected $field2 from the $subSql.
        $sql = $connection->select()
            ->from(
                ['main' => $table1],
                $field1
            )->where('main.'.$field1.' NOT IN (?)', new \Zend_Db_Expr($subSql->assemble()));

        // Drop all $field1 from the $table1 selected in the $sql
        $deleteSql = 'DELETE FROM ' . $table1 . ' WHERE ' . $field1 . ' IN (SELECT * FROM (' . $sql . ') as fordelete)';
        $connection->query($deleteSql);
    }

    /**
     * Setup indexes to tables
     *
     * @param mixed $instance
     */
    private function processIndexes($instance)
    {
        $installer = $this->resource;
        $connection = $installer->getConnection();

        $indexes = $instance->getIndexes();
        foreach ($indexes as $item) {
            // install to Magento
            $tableName = $installer->getTableName($item['table_name']);
            if ($connection->isTableExists($tableName)) {
                if ($this->isTableIndexMissed($tableName, $item)) {
                    $this->addIndex($item, $tableName);
                } elseif ($this->isNeedToRemoveTableIndex($tableName, $item)) {
                    $this->dropIndex($item, $tableName);
                }
            }

            // install to MageWorx OptionTemplates
            if ($this->moduleManager->isEnabled('MageWorx_OptionTemplates')) {
                $mageworxTableName = $this->getOptionTemplateTableName($instance, $item['table_name']);
                $mageworxTableName = $installer->getTableName($mageworxTableName);
                if ($connection->isTableExists($mageworxTableName)) {
                    if ($this->isTableIndexMissed($mageworxTableName, $item)) {
                        $this->addIndex($item, $mageworxTableName);
                    } elseif ($this->isNeedToRemoveTableIndex($mageworxTableName, $item)) {
                        $this->dropIndex($item, $mageworxTableName);
                    }
                }
            }
        }
    }

    /**
     * Check if index already exist
     *
     * @param array $item
     * @param string $tableName
     * @return bool $skipFlag
     */
    private function isIndexExist($item, $tableName)
    {
        $installer = $this->resource;
        $connection = $installer->getConnection();

        $skipFlag = 0;
        $indexList = $connection->getIndexList($tableName);
        $indexType = $item['index_type'] ? $item['index_type'] :
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX;
        foreach ($indexList as $index) {
            if ($index['KEY_NAME'] ==
                $installer->getIdxName(
                    $tableName,
                    $item['field_name'],
                    $indexType
                )
            ) {
                $skipFlag = 1;
                break;
            }
        }
        return $skipFlag;
    }

    /**
     * Add index
     *
     * @param array $item
     * @param string $tableName
     */
    private function addIndex($item, $tableName)
    {
        $installer = $this->resource;
        $connection = $installer->getConnection();

        if ($connection->isTableExists($tableName) &&
                $this->isColumnExist($item['field_name'], $tableName)
        ) {
            $indexType = $item['index_type'] ? $item['index_type'] :
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX;
            $connection->addIndex(
                $tableName,
                $installer->getIdxName($tableName, $item['field_name'], $indexType),
                $item['field_name'],
                $indexType
            );
        }
    }

    /**
     * Drop index
     *
     * @param array $item
     * @param string $tableName
     */
    private function dropIndex($item, $tableName)
    {
        $installer = $this->resource;
        $connection = $installer->getConnection();

        if ($connection->isTableExists($tableName) &&
            $this->isColumnExist($item['field_name'], $tableName)
        ) {
            $indexType = $item['index_type'] ? $item['index_type'] :
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX;
            $connection->dropIndex(
                $tableName,
                $installer->getIdxName($tableName, $item['field_name'], $indexType)
            );
        }
    }

    /**
     * Setup foreign keys to tables
     *
     * @param mixed $instance
     */
    private function processForeignKeys($instance)
    {
        $installer = $this->resource;
        $connection = $installer->getConnection();

        $foreignKeys = $instance->getForeignKeys();
        foreach ($foreignKeys as $item) {
            // install to Magento
            $tableName = $installer->getTableName($item['table_name']);
            if ($connection->isTableExists($tableName)) {
                $isDropFk = isset($item['remove']);
                if ($isDropFk) {
                    $this->dropForeignKey($item, $tableName);
                }

                if (!$this->isForeignKeyExist($item, $tableName) && !$isDropFk) {
                    if (isset($item['callback'])) {
                        foreach ($item['callback'] as $action => $params) {
                            $this->{$action}($params);
                        }
                    }

                    $this->addForeignKey($item, $tableName);
                }
            }

            // install to MageWorx OptionTemplates
            if ($this->moduleManager->isEnabled('MageWorx_OptionTemplates')) {
                $mageworxTableName = $this->getOptionTemplateTableName($instance, $item['table_name']);
                $mageworxTableName = $installer->getTableName($mageworxTableName);
                if ($connection->isTableExists($mageworxTableName)) {
                    if (!$this->isForeignKeyExist($item, $mageworxTableName)) {
                        if (isset($item['callback'])) {
                            foreach ($item['callback'] as $action => $params) {
                                $this->{$action}($params);
                            }
                        }

                        $this->addForeignKey($item, $mageworxTableName);
                    }
                }
            }
        }
    }

    /**
     * Check if foreign key already exist
     *
     * @param array $item
     * @param string $tableName
     * @return bool $skipFlag
     */
    private function isForeignKeyExist($item, $tableName)
    {
        $installer = $this->resource;
        $connection = $installer->getConnection();

        $referenceTableName = $installer->getTableName($item['reference_table_name']);

        $skipFlag = 0;

        if (!$connection->isTableExists($tableName) ||
            !$connection->isTableExists($referenceTableName) ||
            !$connection->tableColumnExists($tableName, $item['column_name']) ||
            !$connection->tableColumnExists($referenceTableName, $item['reference_column_name'])
        ) {
            $skipFlag = 1;
            return $skipFlag;
        }

        $fkList = $connection->getForeignKeys($tableName);
        foreach ($fkList as $fk) {
            if ($fk['TABLE_NAME'] == $tableName &&
                $fk['COLUMN_NAME'] == $item['column_name'] &&
                $fk['REF_TABLE_NAME'] == $referenceTableName &&
                $fk['REF_COLUMN_NAME'] == $item['reference_column_name']
            ) {
                $skipFlag = 1;
                break;
            }
        }
        return $skipFlag;
    }

    /**
     * Add foreign key
     *
     * @param array $item
     * @param string $tableName
     */
    private function addForeignKey($item, $tableName)
    {
        $installer = $this->resource;
        $connection = $installer->getConnection();

        $referenceTableName = $installer->getTableName($item['reference_table_name']);

        $fkk = $installer->getFkName(
            $tableName,
            $item['column_name'],
            $referenceTableName,
            $item['reference_column_name']
        );
        $connection->addForeignKey(
            $fkk,
            $tableName,
            $item['column_name'],
            $referenceTableName,
            $item['reference_column_name'],
            $item['on_delete']
        );
    }

    /**
     * Drop foreign key
     *
     * @param array $item
     * @param string $tableName
     */
    private function dropForeignKey($item, $tableName)
    {
        $installer = $this->resource;
        $connection = $installer->getConnection();

        $referenceTableName = $installer->getTableName($item['reference_table_name']);

        $fkk = $installer->getFkName(
            $tableName,
            $item['column_name'],
            $referenceTableName,
            $item['reference_column_name']
        );
        $connection->query('SET FOREIGN_KEY_CHECKS=0;');
        $connection->dropForeignKey(
            $tableName,
            $fkk
        );
        $connection->query('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Generate mageworx option template table name by deleting module name prefix
     *
     * @param mixed $instance
     * @param string $tableName
     * @return string $mageworxTableName
     */
    private function getOptionTemplateTableName($instance, $tableName)
    {
        if (strpos($tableName, 'mageworx_optiontemplates') !== false) {
            return $tableName;
        }
        if ($tableName == 'mageworx_optionfeatures_product_attributes') {
            return 'mageworx_optiontemplates_group';
        }
        $mageworxTableName = str_replace(
            'catalog_product_',
            'mageworx_optiontemplates_group_',
            $tableName
        );
        if ($instance->getModuleTablePrefix()) {
            $mageworxTableName = str_replace(
                $instance->getModuleTablePrefix() . '_',
                'mageworx_optiontemplates_group_',
                $mageworxTableName
            );
        }

        if ($mageworxTableName == 'mageworx_optiontemplates_group_entity') {
            $mageworxTableName = 'mageworx_optiontemplates_group';
        }

        return $mageworxTableName;
    }

    /**
     * Get column value from column params array
     *
     * @param array $params
     * @param string $key
     * @return mixed $value
     */
    private function getColumnValue($params, $key)
    {
        if (isset($params[$key])) {
            return $params[$key];
        }
        return null;
    }

    /**
     * Get column options from column params array
     *
     * @param array $params
     * @return array $options
     */
    private function getColumnOptions($params)
    {
        $options = [];
        if (isset($params['unsigned'])) {
            $options['unsigned'] = $params['unsigned'];
        }
        if (isset($params['nullable'])) {
            $options['nullable'] = $params['nullable'];
        }
        if (isset($params['default'])) {
            $options['default'] = $params['default'];
        }
        if (isset($params['primary'])) {
            $options['primary'] = $params['primary'];
        }
        if (isset($params['identity'])) {
            $options['identity'] = $params['identity'];
        }
        return $options;
    }

    /**
     * Check if column(s) exist
     *
     * @param array|string $column
     * @param string $tableName
     * @return bool
     */
    private function isColumnExist($column, $tableName)
    {
        $installer = $this->resource;
        $connection = $installer->getConnection();

        if (empty($column)) {
            return false;
        }
        if (is_array($column)) {
            foreach ($column as $fieldNameItem) {
                if (!$connection->tableColumnExists($tableName, $fieldNameItem)) {
                    return false;
                }
            }
            return true;
        } else {
            return $connection->tableColumnExists($tableName, $column);
        }
    }

    /**
     * Check if item marked to be dropped
     *
     * @param array $item
     * @return bool
     */
    private function isMarkedToDrop($item)
    {
        return !empty($item['drop']) && $item['drop'] === true;
    }

    /**
     * Check if table column must be added
     *
     * @param array $tableName
     * @param array $item
     * @return bool
     */
    private function isTableColumnMissed($tableName, $item)
    {
        return !$this->resource->getConnection()->tableColumnExists($tableName, $item['field_name']) &&
            !$this->isMarkedToDrop($item);
    }

    /**
     * Check if table column must be removed
     *
     * @param array $tableName
     * @param array $item
     * @return bool
     */
    private function isNeedToRemoveTableColumn($tableName, $item)
    {
        return $this->resource->getConnection()->tableColumnExists($tableName, $item['field_name']) &&
            $this->isMarkedToDrop($item);
    }

    /**
     * Check if table index must be added
     *
     * @param array $tableName
     * @param array $item
     * @return bool
     */
    private function isTableIndexMissed($tableName, $item)
    {
        return !$this->isIndexExist($item, $tableName) && !$this->isMarkedToDrop($item);
    }

    /**
     * Check if table index must be removed
     *
     * @param array $tableName
     * @param array $item
     * @return bool
     */
    private function isNeedToRemoveTableIndex($tableName, $item)
    {
        return $this->isIndexExist($item, $tableName) && $this->isMarkedToDrop($item);
    }
}
