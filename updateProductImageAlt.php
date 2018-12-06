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
        try
        {
            $productId = intval($_product->getId());
            //$product = $obj->get('\Magento\Catalog\Model\Product')->load($productId);
            $product = $_productRepository->getById($productId,false,0);
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
            $NjmName = $product->getNjmName();
            foreach ($existingMediaGalleryEntries as $key => $entry) {
                $entry->setLabel($NjmName);
            }
            $product->setMediaGalleryEntries($existingMediaGalleryEntries);
            //$this->productRepository->save($product);
            $product->save();
            echo "\n Success: ".$productId." = ".$product->getName()."";
        } catch(Exception $e) {
            echo "\n Fail : ".$productId." = ".$product->getName()."";
        }
        
    }
}

?>