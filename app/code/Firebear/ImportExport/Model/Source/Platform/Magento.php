<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Source\Platform;

use Magento\Catalog\Model\Product\Visibility;
use Firebear\ImportExport\Model\Import\Product;
use Magento\Backend\Model\Session;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory;
use Magento\Eav\Model\Entity\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\ClassModelFactory;

class Magento extends AbstractPlatform
{

    /**
     * Magento constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     * @param ReadFactory $readFactory
     * @param Csv $csvProcessor
     * @param ClassModelFactory $taxFactory
     * @param Visibility $visibility
     * @param CollectionFactory $attributeSetCollectionFactory
     * @param Product $importProduct
     * @param Context $context
     * @param EavSetupFactory $eavSetupFactory
     * @param StoreManagerInterface $storeManager
     * @param Attribute $attributeFactory
     * @param ResourceModelFactory $resourceFactory
     * @param Session $session
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        ReadFactory $readFactory,
        Csv $csvProcessor,
        ClassModelFactory $taxFactory,
        Visibility $visibility,
        CollectionFactory $attributeSetCollectionFactory,
        Product $importProduct,
        Context $context,
        EavSetupFactory $eavSetupFactory,
        StoreManagerInterface $storeManager,
        Attribute $attributeFactory,
        ResourceModelFactory $resourceFactory,
        Session $session
    ) {
        parent::__construct(
            $scopeConfig,
            $filesystem,
            $readFactory,
            $csvProcessor,
            $taxFactory,
            $visibility,
            $attributeSetCollectionFactory,
            $importProduct,
            $context,
            $eavSetupFactory,
            $storeManager,
            $attributeFactory,
            $resourceFactory,
            $session
        );

        $this->unsetColumns = [
            '_root_category',
            '_category',
            '_store',
        ];
    }

    /**
     * Prepare Rows
     *
     * @param $rowData
     *
     * @return mixed
     */
    public function prepareRow($rowData)
    {
        /*tax phase*/
        if (isset($rowData['tax_class_id'])) {
            $rowData['tax_class_name'] = $this->getTaxClassName($rowData['tax_class_id']);
        }
        /*visibility phase*/
        if (isset($rowData['visibility'])) {
            $rowData['visibility'] = $this->getVisibilityText($rowData['visibility']);
        }
        if (isset($rowData['gift_message_available'])) {
            $rowData['gift_message_available'] = $this->getAttrValues('gift_message_available',$rowData['gift_message_available']);
        }
        /*attribute set phase*/
        if (isset($rowData['_attribute_set'])) {
            $rowData['_attribute_set'] = $this->getAttributeSetName($rowData['_attribute_set']);
        }
        /*store view phase*/
        //if (isset($rowData['_store'])) {
        //     $rowData['store_view_code'] = $rowData['_store'];
        // }
        if (isset($rowData['_root_category'])) {
            if (isset($rowData['categories'])) {
                $rowData['categories'] = $rowData['_root_category'] . "/" . $rowData['categories'];
            } else {
                $rowData['categories'] = $rowData['_root_category'];
            }
        }

        if (isset($rowData['price']) && !$rowData['price']) {
            $rowData['price'] = 0;
        }

        /*bundle phase*/
        if (isset($rowData['bundle_configurations']) && isset($rowData['bundle_values'])) {
            if ($rowData['bundle_configurations']) {
                $bundleConfigurations = explode(',', $rowData['bundle_configurations']);
                foreach ($bundleConfigurations as $bundleConfigData) {
                    $bundleConfigData = explode('=', $bundleConfigData);
                    $rowData[$bundleConfigData[0]] = $bundleConfigData[1];
                }
            } else {
                $rowData['bundle_price_type'] = '';
                $rowData['bundle_sku_type'] = '';
                $rowData['bundle_price_view'] = '';
                $rowData['bundle_weight_type'] = '';
            }
        }

        $rowData = $this->unsetColumns($rowData, $this->unsetColumns);

        $rowData['custom_options'] = $this->formatCustomOptions($rowData);
        if (isset($rowData['_store'])) {
            $rowData['store_view_code'] = $rowData['_store'];
        } else {
            $rowData['store_view_code'] = '';
        }

        return $rowData;
    }

    /**
     * @param $rowData
     * @return mixed
     */
    public function prepareColumns($rowData)
    {
        /*tax phase*/
        if (in_array('tax_class_id', $rowData)) {
            $key = array_search('tax_class_id', $rowData);
            $rowData[$key] = 'tax_class_name';
        }

        if (in_array('_store', $rowData)) {
            //  $key = array_search('_store', $rowData);
            //   $rowData[$key] = 'store_view_code';
        }
        if (in_array('_root_category', $rowData) || in_array('_category', $rowData)) {
            $key = null;
            if ($key = array_search('_root_category', $rowData)) {
                $rowData[$key] = 'categories';
            }
            $keySecond = array_search('_category', $rowData);

            if ($key && $keySecond) {
                unset($rowData[$keySecond]);
            } elseif (!$key && $keySecond) {
                $rowData[$keySecond] = 'categories';
            }
        }

        return $rowData;
    }

    /**
     * Unset unnecessary columns
     *
     * @param $data
     * @param $columnNames
     *
     * @return mixed
     */
    public function unsetColumns($data, $columnNames)
    {
        foreach ($columnNames as $column) {
            unset($data[$column]);
        }

        return $data;
    }

    /**
     * Get tax class name
     *
     * @param $taxId
     *
     * @return mixed|string
     */
    public function getTaxClassName($taxId)
    {
        if (!is_numeric($taxId)) {
            return '';
        }
        $taxInfo = $this->taxFactory->create()->load($taxId);

        if ($taxInfo->getClassName()) {
            return $taxInfo->getClassName();
        }

        return '';
    }

    /**
     * Get tax visibility label
     *
     * @param $visibilityId
     *
     * @return string
     */
    public function getVisibilityText($visibilityId)
    {
        if (!$visibilityId) {
            return '';
        }
        $optionText = $this->visibility->getOptionText($visibilityId);

        return $optionText
            ? (string)$optionText
            : (string)$this->visibility->getOptionText(
                Visibility::VISIBILITY_NOT_VISIBLE
            );
    }

    public function getAttrValues($name, $value)
    {
        if (!$value ){
            return '';
        }

        $newValue = '';
        $collection = $this->attributeFactory->getCollection()->addFieldToFilter('attribute_code', $name);
        if ($collection->getSize()) {
            $item = $collection->getFirstItem();
            foreach($item->getOptions() as $option) {
                if ($option->getValue() == $value) {
                    $newValue = $option->getLabel();
                }
            }
        }

        return $newValue;
    }

    /**
     * Get Attribute Set Name
     *
     * @param $setName
     *
     * @return mixed
     */
    public function getAttributeSetName($setName)
    {
        $attributeSetCollection = $this->attributeSetCollectionFactory->create();
        $attributeSetName =
            $attributeSetCollection->addFieldToFilter('attribute_set_name', $setName)->getFirstItem();
        if ($attributeSetName->getId()) {
            return $setName;
        }

        return $this->attributeSetCollectionFactory->create()->getFirstItem()->getAttributeSetName();
    }

    public function formatCustomOptions($data) {
        $str = "";
        if (isset($data['_custom_option_title']) && $data['_custom_option_title']) {
            //name=Custom Yoga Option,type=drop_down,required=0,price=10.0000,price_type=fixed,sku=,option_title=Gold
           $str .= "name=" . $data['_custom_option_title'];
           if ($data['_custom_option_type']) {
                $str  .= ",type=" . $data['_custom_option_type'];
           }
            if ($data['_custom_option_is_required']) {
                $str  .= ",required=" . $data['_custom_option_is_required'];
            } else {
                $str  .= ",required=0";
            }
            if ($data['_custom_option_sku']) {
                $str  .= ",sku=" . $data['_custom_option_sku'];
            } else {
                $str .=",sku=" . $data['sku'];
            }
            if ($data['_custom_option_price']) {
                $str  .= ",price=" . $data['_custom_option_price'] . ",price_type=fixed";
            }
            if ($data['_custom_option_row_title']) {
                $str  .= ",option_title=" . $data['_custom_option_row_title'];
            }

        }

        return $str;
    }
}
