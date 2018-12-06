<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/../app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$obj = $bootstrap->getObjectManager();
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend'); 

$productCollection = $obj->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
/** Apply filters here */
$collection = $productCollection->addAttributeToSelect('*')->load();
foreach ($collection as $product){
	$id = $product->getId();
    $product = $obj->create('Magento\Catalog\Model\Product')->load($id);
	$product->setCanSaveCustomOptions(true);
	$product->save();
	echo $product->getName()."/n";
}
echo "test Done";
?>