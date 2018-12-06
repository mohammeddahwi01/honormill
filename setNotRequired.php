<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$obj = $bootstrap->getObjectManager();
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend'); 

$storeManager = $obj->get("\Magento\Store\Model\StoreManagerInterface");
$productCollection = $obj->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
$collection = $productCollection->addAttributeToSelect('*')->load();
foreach ($collection as $product){
    $id = $product->getId();
    $product = $obj->create('Magento\Catalog\Api\ProductRepositoryInterface')->getById($id, false, 0);
    foreach ($product->getOptions() as $option) {
        $option->setIsRequire(0);
        $option->save();
    }
    echo $product->getName()."\n<br/>";
    $product->setCanSaveCustomOptions(true);
    $product->save();
}
echo "All Products Updated!!!!";
exit();
?>