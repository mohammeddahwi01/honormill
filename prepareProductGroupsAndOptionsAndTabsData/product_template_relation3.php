<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/../app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('adminhtml');
//adminhtml
//admin
//frontend

error_reporting(0);

$_fileCsv = $obj->get('\Magento\Framework\File\Csv');
$_resource = $obj->get('Magento\Framework\App\ResourceConnection');
$_optionHelper = $obj->get('Codazon\ThemeOptions\Helper\Data');
$file = 'custom_options_relation_aanddmodern2.csv';
$matchFound = false;
$aanddmodern_product_ids = array();
$tabs_relation_count = 0;
$updateTabsTableName = 'catalog_product_option_type_value';
$tempCount = 0;

$connection = $_resource->getConnection();
$tableName = $_resource->getTableName('catalog_product_option');

$loader = $obj->get('Magento\Catalog\Model\ProductFactory');
$request = $obj->get('\Magento\Framework\App\RequestInterface');
$_baseHelper = $obj->get('MageWorx\OptionBase\Helper\Data');
$AddGroupOptionToProductObserver = $obj->get('MageWorx\OptionTemplates\Model\Observer\AddGroupOptionToProductObserver');
$_eventManager = $obj->get('Magento\Framework\Event\ManagerInterface');
$_optionSaver = $obj->get('\MageWorx\OptionTemplates\Model\OptionSaver');
$_groupCollectionFactory = $obj->get('MageWorx\OptionTemplates\Model\ResourceModel\Group\CollectionFactory');
$_groupFactory = $obj->get('MageWorx\OptionTemplates\Model\GroupFactory');
$group = $_groupFactory->create();
$resource = $group->getResource();

$templateArray = array(
    "none" => "None",
    87 => "01A Aeon Cow Pony Hide Leather (premium)",
    88 => "01A Aeon Fiberglass",
    89 => "01A Aeon Grade B",
    90 => "01A Aeon Grade T",
    91 => "01A Aeon painted beech",
    92 => "01A Aeon plywood veneer",
    93 => "01A Aeon solid wood matte finish",
    94 => "01A Aeon solid wood wax finish",
    95 => "01A Aeon Standard Leather",
    96 => "03C Control Brand Parma",
    97 => "03C-S Control Brand Silnovo 07 Wing Chair",
    98 => "08F Finemod Imports Egg",
    99 => "11I Innovation cotton poly",
    100 => "11I Innovation faux leather",
    101 => "11I Innovation poly linen mix",
    102 => "11I Innovation polyester",
    103 => "14M Modern Furniture MO Boucle",
    104 => "14M Modern Furniture MO-MJ-Tweed",
    105 => "15M Modloft Prince Bed",
    106 => "16M Modway Fabrics B (Christopher)",
    107 => "16M Modway Furniture Fabric (loft)",
    108 => "16M Modway Furniture Fabric Series",
    109 => "16M Modway Furniture Goto fabrics",
    110 => "16M Modway Furniture Groovy Fabrics",
    111 => "16M Modway Furniture LCW",
    112 => "16M Modway Furniture Leather Series",
    113 => "16M Modway Furniture plastics",
    114 => "16M Modway Furniture retro (response) fabric",
    115 => "16M Modway Furniture Tulip Fabrics",
    116 => "16M Modway Furniture Tulip Vinyl",
    117 => "16M Modway Furniture Vinyl",
    118 => "16M Modway Furniture Wood",
    119 => "16M Modway Furniture Wool Fabric (loft)",
    120 => "19P Phillips Collection Seat Belt",
    121 => "19P Phillips Collection Stone Tables",
    122 => "22S Shenzhen Brother 1: Cashmere blend",
    123 => "22S Shenzhen Brother 2: Cashmere",
    124 => "22S Shenzhen Brother 3: Camira",
    125 => "22S Shenzhen Brother 4: Camira Hemp",
    126 => "22S Shenzhen Brother 5: Italian leather (standard)",
    127 => "22S Shenzhen Brother 6: Aniline leather (premium)",
    128 => "22S Shenzhen Brother 7: Waxy leather (premium)",
    129 => "22S Shenzhen Brother 8: Cow Pony Hide leather (premium)",
    130 => "23S Shenzhen MJ Boucle",
    131 => "23S Shenzhen MJ Cashmere",
    132 => "23S Shenzhen MJ Leathers",
    133 => "23S Shenzhen MJ Rugi",
    134 => "27W Wholesale Interiors",
    135 => "27W Wholesale Interiors beige gray",
    136 => "28Y Younger Furniture Grade B-C",
    137 => "28Y Younger Furniture Grade D",
    138 => "28Y Younger Furniture Grade E",
    139 => "28Y Younger Furniture Grade F-I",
    140 => "28Y Younger Furniture Grade J-Q",
    141 => "28Y Younger Furniture Leather",
    142 => "28Y Younger Furniture Quick-Ship",
    143 => "29Z Bertoia cushion",
    144 => "30K Korean situ chair mesh",
    145 => "31T TemaHome abbey (london) shelve finishes",
    146 => "31T TemaHome marble (dune) fabric",
    147 => "33Y Ytoom florence tables",
    148 => "All - Bed sizes K, cK",
    149 => "All - Bed sizes Q, K",
    150 => "All - black and white",
    151 => "All - leatherette",
    152 => "All - left and right",
    153 => "All - marble",
    154 => "All - size options",
    155 => "COM & RUSH",
    156 => "Glass",
    157 => "Hair-on hide"
);

$_productRepository = $obj->get('\Magento\Catalog\Api\ProductRepositoryInterface');
$_optionHelper = $obj->get('Codazon\ThemeOptions\Helper\Data');
$deletedProductIds = array();
$groupIds = array();
$currentPid = 0;
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/addProductGroupOptions_30mar18.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$tabsCsv = explode("\n", file_get_contents('product_stock_and_custom_tabs_all_with_optionsku.csv'));
$tabsData = array();
$tabsData2 = array();
foreach ($tabsCsv as $key => $line) {
    if($key) {
        $tabsData[] = str_getcsv($line);
        $array = str_getcsv($line);
        $pid = intval(end($array));
        if($pid)
            $tabsData2[$pid][] = str_getcsv($line);
    }
}
$tabsHeader = array(
    'option_id',
    'option_type_id',
    'image_path',
    'optionSku',
    'custom_tab',
    'stock_tab',
    'title',
    'product_id',
    'productSku',
    'aanddmodern_product_id'
);
$allProducts = array();
$successProducts = array();
$failedProducts = array();
if (file_exists($file))
{
    $csvData = $_fileCsv->getData($file);
    if(count($csvData))
    {
        $tempArray = array();
        foreach ($csvData as $rowNum => $row)
        {
            if($rowNum)
            {
                if(is_array($row) && count($row))
                {
                    $pid = 0;
                    $title = '';
                    $option_id = 0;
                    $option_type_id = 0;
                    $stock_tab = 0;
                    $custom_tab = 0;
                    foreach ($row as $colNum => $col)
                    {
                        if($colNum==1) {
                            $title = $col;
                        }
                        if($colNum==4) {
                            $pid = intval($col);
                        }
                    }
                    if($title && $pid && !in_array($pid, $allProducts))
                        $allProducts[$pid] = $title;
                    if($title)
                    {
                        /*echo "<br/>========================================================";
                        echo "<br/>(title=".$title.") (pid=".$pid.") (currentPid=".$currentPid.")";
                        $logger->info("========================================================");
                        $logger->info("(title=".$title.") (pid=".$pid.") (currentPid=".$currentPid.")");*/

                        //if($currentPid!=0 && $pid!=$currentPid && $currentPid==398)
                        //if($currentPid!=0 && $pid!=$currentPid && $currentPid>=391 && $currentPid<=400)
                        if($currentPid!=0 && $pid!=$currentPid)
                        {
                            echo "<br/>========================================================";
                            echo "<br/>(title=".$title.") (pid=".$pid.") (currentPid=".$currentPid.")";
                            //$logger->info("========================================================");
                            //$logger->info("(title=".$title.") (pid=".$pid.") (currentPid=".$currentPid.")");

                            $fast_ship = $_optionHelper::FAST_SHIP_NO;
                            $custom_order = $_optionHelper::CUSTOM_ORDER_NO;

                            $groupIds = (isset($groupIdsArray[$currentPid]['ids'])) ? $groupIdsArray[$currentPid]['ids'] : array();
                            if (count($groupIds))
                            {
                                if(!in_array(155, $groupIds))
                                    $groupIds[] = 155;
                                /*echo "<pre>groupIds<br/>";
                                print_r($groupIds);
                                echo "</pre>";*/
                                $collection = $_groupCollectionFactory->create()->addFieldToFilter('group_id', $groupIds);
                                $modProductOptions = [];
                                $index=1;
                                foreach ($collection as $group) {
                                    $modProductOptions = $_optionSaver->addNewOptionProcess($modProductOptions, $group);
                                    $modProductOptions[$index++]['template_id'] = $group->getId();
                                    if($group->getId()==155)
                                        $modProductOptions[$index++]['template_id'] = $group->getId();
                                }
                                /*echo "<pre>modProductOptions<br/>";
                                print_r($modProductOptions);
                                echo "</pre>";
                                exit();*/
                                //compatibility for 2.2.x
                                if ($_baseHelper->checkModuleVersion('101.0.10')) {
                                    foreach ($modProductOptions as $optionKey => $optionData) {
                                        $modProductOptions[$optionKey]['id'] = null;
                                        $modProductOptions[$optionKey]['option_id'] = null;
                                        if (!empty($optionData['values'])) {
                                            $values = [];
                                            foreach ($optionData['values'] as $valueKey => $value) {
                                                $value['option_type_id'] = null;
                                                $values[$valueKey] = $value;
                                            }
                                            $modProductOptions[$optionKey]['values'] = $values;
                                        }
                                    }
                                }
                                //$logger->info("count(modProductOptions): ".count($modProductOptions));
                                if(count($modProductOptions))
                                {
                                    //UNSET ALL GROUP IDS
                                    $collectionByProduct = $_groupCollectionFactory->create();
                                    $delGroupIds = $collectionByProduct->addProductFilter($currentPid)->getAllIds();
                                    /*echo "<pre>delGroupIds<br/>";
                                    print_r($delGroupIds);
                                    echo "</pre>";*/
                                    //$logger->log(1,"delGroupIds: ".print_r($delGroupIds,true));
                                    foreach ($delGroupIds as $groupId) {
                                        $resource->deleteProductRelation($groupId, $currentPid);
                                    }
                                    //SET RELATED GROUP IDS AGAIN
                                    echo "<pre>addGroupIds<br/>";
                                    print_r($groupIds);
                                    echo "</pre>";
                                    /*$logger->log(1,"addGroupIds: ".print_r($groupIds,true));*/
                                    foreach ($groupIds as $groupId) {
                                        $resource->addProductRelation($groupId, $currentPid);
                                    }
                                    $customOptions = [];
                                    $customOptionFactory = $obj->create('Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory');
                                    foreach ($modProductOptions as $key => $options)
                                    {
                                        $currentTemplateId = (isset($options['template_id']) && $options['template_id']) ? $options['template_id'] : 0;
                                        if(isset($options['values']) && is_array($options['values']))
                                        {
                                            foreach ($options['values'] as $key2 => $value2)
                                            {
                                                $currentOptionSku = $value2['sku'];
                                                $currentOptionTitle = $value2['title'];
                                                $images_data = (isset($value2['images_data']) && $value2['images_data']) ? $value2['images_data'] : '';
                                                $currentImagePath = '';
                                                if($images_data) {
                                                    $tempParse = explode(',', $images_data)[0];
                                                    $tempParse2 = explode(':', $tempParse)[1];
                                                    $tempParse3 = trim($tempParse2,'"');
                                                    $firstPart = explode('/', $tempParse3)[1];
                                                    $lastPart = end(explode('/', $tempParse3));
                                                    $currentImagePath = str_replace('/'.$firstPart.'/', '', $tempParse3);
                                                    $currentImagePath = str_replace('/'.$lastPart, '', $currentImagePath);
                                                }

                                                if($options['title']=='I have my own material/leather (COM/COL)')
                                                    $currentOptionTitle = 'Yes';
                                                $is_stocktab = 0;
                                                $is_customtab = 0;
                                                if($currentTemplateId==155) {
                                                    $is_stocktab = 1;
                                                    $is_customtab = 1;
                                                }
                                                elseif(count($tabsData2[$currentPid]) && count($tabsData2[$currentPid]))
                                                {
                                                    $searchByArray = array('sku','image_path','title');
                                                    $matched = false;
                                                    foreach ($searchByArray as $searchBy) {
                                                        if(!$matched)
                                                        {
                                                            if($searchBy=='sku' && !$currentOptionSku)
                                                                continue;
                                                            if($searchBy=='image_path' && !$currentImagePath)
                                                                continue;
                                                            if($searchBy=='title' && !$currentOptionTitle)
                                                                continue;
                                                            $retData = matchTabsData($tabsData2, $currentPid, $currentOptionSku, $currentOptionTitle, $currentImagePath, $searchBy);
                                                            $matched = $retData['matched'];
                                                            $is_stocktab = $retData['is_stocktab'];
                                                            $is_customtab = $retData['is_customtab'];
                                                            $optionSkuParam = $retData['optionSkuParam'];
                                                            if(!$matched)
                                                                break;
                                                        }
                                                    }
                                                    /*$retData = matchTabsData($tabsData2, $currentPid, $currentOptionSku, $currentOptionTitle, $searchBy='sku');
                                                    $matched = $retData['matched'];
                                                    $is_stocktab = $retData['is_stocktab'];
                                                    $is_customtab = $retData['is_customtab'];
                                                    if(!$matched) {
                                                        $retData = matchTabsData($tabsData2, $currentPid, $currentOptionSku, $currentOptionTitle, $searchBy='title');
                                                        $matched = $retData['matched'];
                                                        $is_stocktab = $retData['is_stocktab'];
                                                        $is_customtab = $retData['is_customtab'];
                                                        $optionSkuParam = $retData['optionSkuParam'];
                                                    }*/
                                                }
                                                if($is_stocktab)
                                                    $fast_ship = $_optionHelper::FAST_SHIP_YES;
                                                if($is_customtab)
                                                    $custom_order = $_optionHelper::CUSTOM_ORDER_YES;

                                                $options['values'][$key2]['is_stocktab'] = $is_stocktab;
                                                $options['values'][$key2]['is_customtab'] = $is_customtab;
                                                
                                                if($images_data) {
                                                    $parse = explode(',', $images_data)[0];
                                                    $parse2 = explode(':', $parse)[1];
                                                    $parse3 = trim($parse2,'"');
                                                    $imageName = end(explode('/', $parse3));
                                                    //$parse4 = str_replace($imageName, strtolower($imageName), $parse3);
                                                    if($parse3) {
                                                        $options['values'][$key2]['tooltip_image'] = $parse3;
                                                        $options['values'][$key2]['tooltip_image_type'] = 'image';
                                                        $options['values'][$key2]['base_image'] = $parse3;
                                                        $options['values'][$key2]['base_image_type'] = 'image';
                                                    }
                                                }
                                            }
                                        }
                                        /*echo "<pre>options($key)<br/>";
                                        print_r($options);
                                        echo "</pre>";*/
                                        //$logger->log(1,"options($key): ".print_r($options,true));
                                        //$_product = $obj->get('Magento\Catalog\Model\Product')->load($currentPid);
                                        $_product = $_productRepository->getById($currentPid);
                                        $_product->setStoreId(0);
                                        $customOption = $customOptionFactory->create(['data' => $options]);
                                        $customOption->setProductSku($_product->getSku());
                                        $customOptions[] = $customOption;                                        
                                    }
                                    if (!empty($customOptions) && count($customOptions)) {
                                        try {
                                            $_product->unsetOptions();
                                            $_product->setHasOptions(1);
                                            $_product->setCanSaveCustomOptions(true);
                                            $_product->setData('fast_ship',strval($fast_ship));
                                            $_product->setData('custom_order',strval($custom_order));
                                            $_product->setOptions($customOptions)->save();

                                            //$_optionSaver->saveOptionsInProduct($_product);
                                            //$_optionSaver->updateProductData($_product);
                                            $successProducts[$currentPid] = $title;
                                            echo count($customOptions)." options created successfully for product ".$currentPid;
                                            //$logger->info(count($customOptions)." options created successfully for product ".$currentPid);
                                        } catch(Exception $e) {
                                            echo 'Exception Message: ' .$e->getMessage();
                                            $failedProducts[$currentPid] = $title;
                                            echo "<br/>options created failed for product ".$currentPid;
                                            //$logger->info("options created failed for product ".$currentPid);
                                        }
                                    }
                                    else {
                                        $failedProducts[$currentPid] = $title;
                                        echo "<br/>options created failed for product ".$currentPid;
                                        //$logger->info("options created failed for product ".$currentPid);
                                    }
                                    //exit();
                                }
                            }
                        }
                        $currentPid = $pid;
                        $groupId = array_search($title, $templateArray);
                        if($groupId)
                        {
                            if(!in_array($groupId, $groupIdsArray[$pid]['ids'])) {
                                $groupIdsArray[$pid]['ids'][] = $groupId;
                                $groupIdsArray[$pid]['title'][] = $title;
                            }
                        }
                    }
                }
            }
        }
    }
}
function matchTabsData($tabsData2, $currentPid, $currentOptionSku, $currentOptionTitle, $currentImagePath, $searchBy='sku')
{
    foreach ($tabsData2[$currentPid] as $row => $rowData)
    {
        $optionSku=$optionTitle='';
        $optionCustomTab=$optionStockTab=0;
        $optionIdd=$optionTypeIdd=0;
        $matched = false;
        if(is_array($rowData) && !empty($rowData))
        {
            if(isset($rowData[0]) && $rowData[0])
                $optionIdd = $rowData[0];
            if(isset($rowData[1]) && $rowData[1])
                $optionTypeIdd = $rowData[1];
            if(isset($rowData[2]) && $rowData[2])
                $imagePath = $rowData[2];
            if(isset($rowData[3]) && $rowData[3])
                $optionSku = $rowData[3];
            if(isset($rowData[4]) && $rowData[4])
                $optionCustomTab = intval($rowData[4]);
            if(isset($rowData[5]) && $rowData[5])
                $optionStockTab = intval($rowData[5]);
            if(isset($rowData[6]) && $rowData[6])
                $optionTitle = $rowData[6];
            //if($optionTitle=='Tan') {
                /*echo "<br/>(optionId=$optionIdd) (optionTypeId=$optionTypeIdd) optionTitle: ".$optionTitle;
                echo "<br/>optionCustomTab: ";
                var_dump(intval($rowData[4]));
                echo "<br/>optionStockTab: ";
                var_dump(intval($rowData[5]));*/
            //}
            if($optionSku && $currentOptionSku && $optionSku==$currentOptionSku && $searchBy=='sku') {
                $matched = true;
            }
            elseif($imagePath && $currentImagePath && $imagePath==$currentImagePath && $searchBy=='image_path') {
                $matched = true;
            }
            elseif($optionTitle && $currentOptionTitle && $optionTitle==$currentOptionTitle && $searchBy=='title') {
                $matched = true;
            }
            $is_stocktab = $optionStockTab;
            $is_customtab = $optionCustomTab;
            if($matched)
                break;
        }
    }
    if(!$matched) {
        $is_stocktab = 0;
        $is_customtab = 0;
    }
    return array(
        'matched'=>$matched,
        'is_stocktab'=>$is_stocktab,
        'is_customtab'=>$is_customtab,
        'optionSkuParam'=>$optionSku
    );
}
$logger->log(1,"allProducts: ".print_r($allProducts,true));
$logger->log(1,"successProducts: ".print_r($successProducts,true));
$logger->log(1,"failedProducts: ".print_r($failedProducts,true));
?>