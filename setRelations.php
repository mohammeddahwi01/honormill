<?php

use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get('Magento\Framework\App\State');
$_productRepository = $obj->get('\Magento\Catalog\Api\ProductRepositoryInterface');
$_productCollectionFactory = $obj->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
$state->setAreaCode('adminhtml');

$resource = $obj->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();
$sql = "SELECT * FROM `mageworx_optiontemplates_group_option_type_value`";
$values = $connection->fetchAll($sql); 
if(count($values))
{
    foreach ($values as $value)
    {
    	$psql = "SELECT * FROM catalog_product_option_type_value WHERE group_option_value_id = ".$value['option_type_id'];
		$pValue = $connection->fetchAll($psql);
        $mageworxOptionTypeId = $value['option_type_id'];
        $value_identifier = uniqid($mageworxOptionTypeId);
        $updateSql  ="UPDATE `mageworx_optiontemplates_group_option_type_value` SET `value_identifier`= '".$value_identifier."' WHERE `option_type_id` = '".$mageworxOptionTypeId."'";
        $connection->query($updateSql);
        echo $value_identifier."<br/>";
        if (count($pValue) > 0) {
            foreach ($pValue as $valueData) {
                $catalogOptionTypeId = $valueData['option_type_id'];
                $optionId = $valueData['option_id'];
                $optSql = "SELECT * FROM catalog_product_option WHERE option_id = ".$optionId;
                $optValue = $connection->fetchAll($optSql);
                if (count($optValue) > 0) {
                    $productId = $optValue[0]['product_id'];
                    $updateSql1 = "UPDATE `catalog_product_option_type_value` SET `value_identifier`= '".$productId."-".$value_identifier."' WHERE `option_type_id` = '".$catalogOptionTypeId."'";
                    $connection->query($updateSql1);
                    echo $productId."-".$value_identifier."<br/>";
                    echo "============================================<br/><br/>";
                }
            }
            
        }
    }
}

?>