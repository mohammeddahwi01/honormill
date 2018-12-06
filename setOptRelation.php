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
$sql = "SELECT * FROM `mageworx_optiontemplates_group_option`";
$values = $connection->fetchAll($sql); 
if(count($values))
{
    foreach ($values as $value)
    {
    	$psql = "SELECT * FROM catalog_product_option WHERE group_option_id = ".$value['option_id'];
		$pValue = $connection->fetchAll($psql);
        $mageworxOptionTypeId = "opt".$value['option_id'];
        $opt_identifier = uniqid($mageworxOptionTypeId);
        $updateSql  ="UPDATE `mageworx_optiontemplates_group_option` SET `opt_identifier`= '".$opt_identifier."' WHERE `option_id` = '".$value['option_id']."'";
        $connection->query($updateSql);
        echo $opt_identifier."<br/>";
        if (count($pValue) > 0) {
            foreach ($pValue as $valueData) {
                $catalogOptionTypeId = $valueData['option_id'];
                $optionId = $valueData['option_id'];
                $productId = $valueData['product_id'];
                $updateSql1 = "UPDATE `catalog_product_option` SET `opt_identifier`= '".$productId."-".$opt_identifier."' WHERE `option_id` = '".$catalogOptionTypeId."'";
                $connection->query($updateSql1);
                echo $productId."-".$opt_identifier."<br/>";
                echo "============================================<br/><br/>";
            }
            
        }
    }
}

?>