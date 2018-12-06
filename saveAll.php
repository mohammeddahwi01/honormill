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

foreach ($collection as $product1){
	// $id = $product->getId();
 //    $product1 = $obj->create('Magento\Catalog\Api\ProductRepositoryInterface')->getById($id, false, 0);
    if ($product1->getNjmName() != '') {
        $dimention = '';
        $desc = '';
        $short_desc = '';
        $metaDesc = '';
        if ($product1->getDescription()) {
            $desc = str_replace("A+D Modern","Honormill",$product1->getDescription());
            $desc = str_replace("A&D Modern","Honormill",$desc);
            $desc = str_replace("a+d modern","Honormill",$desc);
            $desc = str_replace("a&d modern","Honormill",$desc);
            $desc = str_replace("a+d Modern","Honormill",$desc);
            $desc = str_replace("a&d Modern","Honormill",$desc);
            $desc = str_replace("A and D Modern","Honormill",$desc);
        }

        if ($product1->getShortDescription()) {
            $short_desc = str_replace("A+D Modern","Honormill",$product1->getShortDescription());
            $short_desc = str_replace("A&D Modern","Honormill",$short_desc);
            $short_desc = str_replace("a+d modern","Honormill",$short_desc);
            $short_desc = str_replace("a+d Modern","Honormill",$short_desc);
            $short_desc = str_replace("a&d modern","Honormill",$short_desc);
            $short_desc = str_replace("a&d Modern","Honormill",$short_desc);
            $short_desc = str_replace("A and D Modern","Honormill",$short_desc);
        }

    	if ($product1->getDimensions()) {
            $dimention = str_replace("A+D Modern","Honormill",$product1->getDimensions());
            $dimention = str_replace("A&D Modern","Honormill",$dimention);
            $dimention = str_replace("a+d modern","Honormill",$dimention);
            $dimention = str_replace("a&d modern","Honormill",$dimention);
            $dimention = str_replace("a+d Modern","Honormill",$dimention);
            $dimention = str_replace("a&d Modern","Honormill",$dimention);
            $dimention = str_replace("A and D Modern","Honormill",$dimention);
        }

        if ($product1->getMetaDescription()) {
            $metaDesc = str_replace("A+D Modern","Honormill",$product1->getMetaDescription());
            $metaDesc = str_replace("A&D Modern","Honormill",$metaDesc);
            $metaDesc = str_replace("a+d modern","Honormill",$metaDesc);
            $metaDesc = str_replace("a&d modern","Honormill",$metaDesc);
            $metaDesc = str_replace("a+d Modern","Honormill",$metaDesc);
            $metaDesc = str_replace("a&d Modern","Honormill",$metaDesc);
            $metaDesc = str_replace("A and D Modern","Honormill",$metaDesc);
        }
        if ($dimention) {
            $product1->setDimensions($dimention);
        }
        if ($metaDesc) {
            $product1->setMetaDescription($metaDesc);
        }
        if ($short_desc) {
            $product1->setShortDescription($short_desc);
        }
        if ($desc) {
            $product1->setDescription($desc);
        }
        $product1->setCanSaveCustomOptions(true);
		$product1->save();
    	echo $product1->getName().' Updated Successfully!<br/>'."\n";
	}
}
echo "All Nj Products Updated!!!!";
exit();
?>