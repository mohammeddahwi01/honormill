<?php

use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get('Magento\Framework\App\State');
$_productRepository = $obj->get('\Magento\Catalog\Api\ProductRepositoryInterface');
$_productCollectionFactory = $obj->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
$state->setAreaCode('adminhtml');
//adminhtml
//admin
//frontend

error_reporting(0);
$collection = $_productCollectionFactory->create();
$collection->addAttributeToSelect('*');
if(count($collection))
{
    foreach ($collection as $_product)
    {
        $productId = intval($_product->getId());
        $_product = $_productRepository->getById($productId);
        $_product->setStoreId(0);
        $_product->unsetOptions();
        $_product->setCanSaveCustomOptions(true);
        $_product->setHasOptions(0)->save();
        echo "\nproductId: ".$productId." options deleted";
        /*$product = $obj->get('\Magento\Catalog\Model\Product')->load($productId);
        if ($product->getOptions()) {
            foreach ($product->getOptions() as $opt) {
                $opt->delete();
            }
            echo "\nproductId: ".$productId." options deleted";
            //$product->setHasOptions(0)->save();
        } else {
            echo "\nproductId: ".$productId." not found";
        }*/
    }
}

?>