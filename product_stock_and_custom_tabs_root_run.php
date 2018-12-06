<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

error_reporting(0);

/*$_categoryFactory = $obj->get('Magento\Catalog\Model\CategoryFactory');
$fileCsv = $obj->get('\Magento\Framework\File\Csv');
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend'); 
$productRepository = $obj->get('\Magento\Catalog\Model\ProductRepository');
$productFactory = $obj->get('\Magento\Catalog\Model\ProductFactory');
$columnLables = array('option_id','option_type_id','image_path','stock_tab','custom_tab','title','product_id','sku','aanddmodern_product_id');
$i = 0;
$updatedCount = 0;
$file = 'product_stock_and_custom_tabs.csv';
if (file_exists($file)) {
    $data = $fileCsv->getData($file);
    if(count($data)) {
    	fputcsv($file2,$columnLables);
    	foreach ($data as $rowNum => $row) {
    		//if($rowNum) {
	    		//echo "rowNum: ".$rowNum;
	    		if(is_array($row) && count($row)) {
	    			$values = array();
	    			$aanddmodern_product_id = '';
	    			foreach ($row as $colNum => $col) {
	    				//echo "<br/>".$columnLables[$colNum].": ".$col;
						$values[] = $col;
						if($columnLables[$colNum]=='sku') {
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
	    		//echo "<br/><br/>";
	    	//}
	    	//else {
	    		//fputcsv($file2,$columnLables);
	    	//}
    	}
    }
}*/


		$_fileCsv = $obj->get('\Magento\Framework\File\Csv');
		$_resource = $obj->get('Magento\Framework\App\ResourceConnection');
        $file = 'product_stock_and_custom_tabs_all.csv';
        $matchFound = false;
        $aanddmodern_product_ids = array();
        $tabs_relation_count = 0;
        $updateTabsTableName = 'catalog_product_option_type_value';

        $connection = $_resource->getConnection();
        $tableName = $_resource->getTableName('catalog_product_option');

        if (file_exists($file)) {
            $csvData = $_fileCsv->getData($file);
            if(count($csvData)) {
                $tempArray = array();
                foreach ($csvData as $rowNum => $row) {
                    //echo "<br/>rowNum: ".$rowNum;
                    if($rowNum) {
                        if(is_array($row) && count($row)) {
                            $pid = 0;
                            $title = '';
                            $option_id = 0;
                            $option_type_id = 0;
                            $stock_tab = 0;
                            $custom_tab = 0;
                            foreach ($row as $colNum => $col) {
                                //echo "<br/>colNum: ".$colNum;
                                if($colNum==3) {
                                    $stock_tab = intval($col);
                                }
                                if($colNum==4) {
                                    $custom_tab = intval($col);
                                }
                                if($colNum==5) {
                                    $title = $col;
                                }
                                if($colNum==8) {
                                    $pid = intval($col);
                                }
                            }
                            //if($title && ($pid==812 || $pid==813)) {
                            if($title && $pid) {
                                $find = array();
                                $sql = "Select option_id FROM " . $tableName . " WHERE product_id=" . $pid;
                                $result = $connection->fetchOne($sql);
                                if($result) {
                                    $option_id = intval($result);
                                    $sql = "SELECT t1.option_type_id FROM catalog_product_option_type_value AS t1 INNER JOIN catalog_product_option_type_title AS t2 ON t1.option_id=".$option_id." AND t1.option_type_id = t2.option_type_id AND t2.store_id=0 AND t2.title='".$title."'";
                                    $result = $connection->fetchOne($sql);
                                    if($result) {
                                        $option_type_id = intval($result);
                                        $find['title'] = $title;
                                        $find['option_id'] = $option_id;
                                        $find['option_type_id'] = $option_type_id;
                                        if(!in_array($find, $tempArray)) {
	                                        $tempArray[$tabs_relation_count]['title'] = $title;
	                                        $tempArray[$tabs_relation_count]['option_id'] = $option_id;
	                                        $tempArray[$tabs_relation_count]['option_type_id'] = $option_type_id;
	                                        echo "<br/>title=".$title;
	                                        echo " (pid=".$pid.")";
	                                        echo " (stock_tab=".$stock_tab.")";
	                                        echo " (custom_tab=".$custom_tab.")";
	                                        echo " (option_id=".$option_id.")";
	                                        echo " (option_type_id=".$option_type_id.")";
	                                        $updateData = [
	                                            'is_stocktab' => $stock_tab,
	                                            'is_customtab' => $custom_tab,
	                                        ];
	                                        $sql = "option_id = ".$option_id." AND option_type_id = ".$option_type_id;
	                                        $connection->update($updateTabsTableName, $updateData, $sql);
		                                    $tabs_relation_count++;
	                                    }
                                    }
                                }
                            }
                        }
                    }
                    //echo "<br/><br/>pid: ".$pid;
                }
            }
        }
        echo "<br/><br/>".$tabs_relation_count." options updated successfully: ";
?>