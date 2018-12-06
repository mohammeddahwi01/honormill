<?php
use Magento\Framework\App\Bootstrap;
require '../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend');

$product = $obj->create('Magento\Catalog\Api\ProductRepositoryInterface')->getById('894');
$product->save();
echo "saved!";
?>