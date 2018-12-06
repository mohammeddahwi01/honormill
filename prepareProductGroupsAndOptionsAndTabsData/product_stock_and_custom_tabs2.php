<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/../app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

error_reporting(0);
header('Content-Encoding: UTF-8');
header('Content-Type: text/csv; charset=utf-8' );
header(sprintf( 'Content-Disposition: attachment; filename=product_stock_and_custom_tabs_all_with_optionsku.csv' ) );
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
$file2 = fopen( 'php://output', 'w' );

$_categoryFactory = $obj->get('Magento\Catalog\Model\CategoryFactory');
$fileCsv = $obj->get('\Magento\Framework\File\Csv');
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend'); 
$productRepository = $obj->get('\Magento\Catalog\Model\ProductRepository');
$productFactory = $obj->get('\Magento\Catalog\Model\ProductFactory');
$i = 0;
$updatedCount = 0;
$file = 'product_stock_and_custom_tabs2.csv';
if (file_exists($file)) {
    $data = $fileCsv->getData($file);
    if(count($data)) {
    	foreach ($data as $rowNum => $row)
    	{
    		//if($rowNum) {
	    		//echo "rowNum: ".$rowNum;
	    		if(is_array($row) && count($row))
	    		{
	    			$values = array();
	    			$aanddmodern_product_id = '';
	    			foreach ($row as $colNum => $col) {
	    				$values[] = $col;
						if($colNum==8) {
		    				$collection = $obj->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
							            ->addAttributeToSelect('*')
							            ->addAttributeToFilter('njm_sku',['eq'=>$col]);
							if(count($collection)) {
								$_product = $collection->getFirstItem();
								//echo "<br/>new_product_id: ".$_product->getId();
								$aanddmodern_product_id = $_product->getId();
							}
							else
								$aanddmodern_product_id = '';
						}
	    			}
	    			$values[] = $aanddmodern_product_id;
	    			fputcsv($file2,$values);
	    		}
    	}
    }
}
?>