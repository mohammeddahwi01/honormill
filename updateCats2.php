<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
// Set the state (not sure if this is neccessary)
$obj = $bootstrap->getObjectManager();
$_categoryFactory = $obj->get('Magento\Catalog\Model\CategoryFactory');
$fileCsv = $obj->get('\Magento\Framework\File\Csv');

$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend'); 
$productRepository = $obj->get('\Magento\Catalog\Model\ProductRepository');

$i = 0;
$updatedCount = 0;
$file = '20feb18-830-working-784to830.csv';
if (file_exists($file)) {
    $data = $fileCsv->getData($file);
    if(count($data)) {
    	foreach ($data as $rowNum => $row) {
    		if($rowNum) {
	    		echo "rowNum: ".$rowNum;
	    		if(is_array($row) && count($row)) {
	    			foreach ($row as $colNum => $col) {
	    				if($colNum==0 || $colNum==6) {
	    					//echo "<br/>colNum: ".$colNum." (".$col.")";
	    					$catIds = array();
	    					if($colNum==0) {
	    						$sku = $col;
	    					}
	    					if($colNum==6) {
	    						$catsPath = $col;
	    						echo "<br/>sku: ".$sku;
	    						echo "<br/>catsPath: ".$catsPath;
	    						$catsPathArray = explode(',', $catsPath);
								if(count($catsPathArray)) {
									foreach ($catsPathArray as $item) {
										$exploded = explode('/', $item);
										echo "<br/>categoryTitle: ".$categoryTitle = end($exploded);
										$collection = $_categoryFactory->create()->getCollection()->addAttributeToFilter('name',$categoryTitle)->setPageSize(1);
										if ($collection->getSize()) {
										    $categoryId = $collection->getFirstItem()->getId();
										    echo " (".$categoryId.")";
										    $catIds[] = $categoryId;
										}
									}
									echo "<br/>";
									var_dump($catIds);
									if(count($catIds) && $sku) {
										//if($sku=='ae-000005') {
											$_product = $productRepository->get($sku);
											$diff=array_diff($catIds,$_product->getCategoryIds());
											if(count($diff)) {
												echo "<br/>before";
												var_dump($_product->getCategoryIds());
												$_product->setCategoryIds($catIds);
												$ret = $_product->save();
												if($ret) {
													$updatedCount++;
													echo "<br/><b style='color:green'>Updated successfully.</b>";
												}
												echo "<br/>after";
												var_dump($_product->getCategoryIds());
											}
										//}
									}
								}
	    					}
	    				}
	    			}
	    		}
	    		echo "<br/><br/>";
	    	}
    	}
    }
}
echo "<br/><b style='color:green'>Total ".$updatedCount." products updated successfully.</b>";

// $category_id= array(6,17,28);
// $catsPath = 'Default Category/Modern Furniture,Default Category/Modern Furniture/Classic Chairs,Default Category/Modern Furniture/Classic Chairs/Living Room Chair';
// $catsPathArray = explode(',', $catsPath);
// if(count($catsPathArray)) {
// 	foreach ($catsPathArray as $item) {
// 		$exploded = explode('/', $item);
// 		echo "<br/>categoryTitle: ".$categoryTitle = end($exploded);
// 		$collection = $_categoryFactory->create()->getCollection()->addAttributeToFilter('name',$categoryTitle)->setPageSize(1);
// 		if ($collection->getSize()) {
// 		    $categoryId = $collection->getFirstItem()->getId();
// 		    echo " (".$categoryId.")"; 
// 		}
// 	}
// }

// if($_product->getSku()=='ae-000005') {
// 	echo "<pre>";
// 	print_r($_product->getCategoryIds());
// 	echo "</pre>";
// 	$_product->setCategoryIds($category_id); // Product Category
// 	$_product->save();
// 	echo "<pre>";
// 	print_r($_product->getCategoryIds());
// 	echo "</pre>";
// }
?>