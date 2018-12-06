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
        //$product = $obj->get('\Magento\Catalog\Model\Product')->load($productId);
        $product = $_productRepository->getById($productId,false,0);
        $NjmName = $product->getNjmName();
        $Name = $product->getName();
        $Description = $product->getDescription();
        $metatitle = (isset($NjmName) && $NjmName !="")?$NjmName:$Name;
        $product->setMetaTitle($metatitle);
        $product->setMetaKeyword($Name);
        $product->setMetaDescription($Name.", ".$Description);
        $product->save();
        echo "\nproductId: ".$productId." = ".$metatitle."";
        /*if ($product->getOptions()) {
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