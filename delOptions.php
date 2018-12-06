<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend');

$optionRepository = $obj->create('\Magento\Catalog\Api\ProductCustomOptionRepositoryInterface');

$productsCollection = $obj->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create()->addAttributeToSelect('*');

foreach ($productsCollection as $product) {
    foreach ($optionRepository->getProductOptions($product) as $option) {
        $ttl = $option->getTitle();
        $optionRepository->delete($option);
        echo $ttl.' options deleted from '.$product->getName().'<br/>';
    }
}
exit();
?>