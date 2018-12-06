<?php namespace Magebees\DealerInquiry\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('magebees_dealer_inquiry'))
            ->addColumn(
                'dealer_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Dealer ID'
            )
            ->addColumn('first_name', Table::TYPE_TEXT, 25, ['nullable' => false])
            ->addColumn('last_name', Table::TYPE_TEXT, 25, ['nullable' => false])
            ->addColumn('email', Table::TYPE_TEXT, 100, ['nullable' => false])
            ->addColumn('company', Table::TYPE_TEXT, 100, ['nullable' => false])
            ->addColumn('taxvat', Table::TYPE_TEXT, 25, ['nullable' => false])
            ->addColumn('address', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('city', Table::TYPE_TEXT, 100, ['nullable' => false])
            ->addColumn('state', Table::TYPE_TEXT, 100, ['nullable' => false])
            ->addColumn('country', Table::TYPE_TEXT, 100, ['nullable' => false])
            ->addColumn('zip', Table::TYPE_TEXT, 100, ['nullable' => false])
            ->addColumn('phone', Table::TYPE_TEXT, 20, ['nullable' => false])
            ->addColumn('website', Table::TYPE_TEXT, 50, ['nullable' => false])
            ->addColumn('bus_desc', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('is_cust_created', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'])
            ->addColumn('website_id', Table::TYPE_INTEGER, null, ['nullable' => false])
            ->addColumn('store_id', Table::TYPE_INTEGER, null, ['nullable' => false])
            ->addColumn('creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
            ->addColumn('update_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Update Time')
            ->addColumn('date_time', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('extra_one', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('extra_two', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('extra_three', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->setComment('Magebees Dealer Inquiry Details');

        $installer->getConnection()->createTable($table);
        
        $table = $installer->getConnection()
            ->newTable($installer->getTable('magebees_dealer_inquiry_files'))
            ->addColumn(
                'dealer_file_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Dealer Files ID'
            )
            ->addColumn('dealer_id', Table::TYPE_INTEGER, null, ['nullable' => false])
            ->addColumn('file_name', Table::TYPE_TEXT, 255, ['nullable' => false])
            
            ->setComment('Magebees Dealer Inquiry Uploaded Files');

        $installer->getConnection()->createTable($table);
        
        $installer->endSetup();
    }
}
