<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend');
$fileCsv = $obj->get('\Magento\Framework\File\Csv');
$file = 'exported-products-options1.csv';
$productRepository = $obj->get('\Magento\Catalog\Api\ProductRepositoryInterface');

$resource = $obj->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();

$templateNames = array("01A Aeon Cow Pony Hide Leather (premium)"=>"ae | 01A Aeon 4: Cow Pony Hide Leather (premium)",
"01A Aeon Fiberglass"=>"ae | 01A Aeon 9: Fiberglass",
"01A Aeon Grade B"=>"ae | 01A Aeon 1: Grade B",
"01A Aeon Grade T"=>"ae | 01A Aeon 2: Grade T",
"01A Aeon painted beech"=>"ae | 01A Aeon 8: painted beech",
"01A Aeon plywood veneer"=>"ae | 01A Aeon 7: plywood veneer",
"01A Aeon solid wood matte finish"=>"ae | 01A Aeon 5: solid wood matte finish",
"01A Aeon solid wood wax finish"=>"ae | 01A Aeon 6: solid wood wax finish",
"01A Aeon Standard Leather"=>"ae | 01A Aeon 3: Standard Leather",
"03C Control Brand Parma"=>"cb | 03C Control Brand: Parma",
"03C-S Control Brand Silnovo 07 Wing Chair"=>"cb | 03C-S Control Brand Silnovo: 07 Wing Chair",
"08F Finemod Imports Egg"=>"fm | 08F Finemod Imports: Egg",
"11I Innovation cotton poly"=>"il | 11I Innovation: cotton poly (redundant)",
"11I Innovation faux leather"=>"il | 11I Innovation: faux leather",
"11I Innovation poly linen mix"=>"il | 11I Innovation: poly linen mix",
"11I Innovation polyester"=>"il | 11I Innovation: polyester",
"14M Modern Furniture MO Boucle"=>"mo | 14M Modern Furniture MO: Boucle (redundant)",
"14M Modern Furniture MO-MJ-Tweed"=>"mo | 14M Modern Furniture MO-MJ: Tweed (redundant)",
"15M Modloft Prince Bed"=>"md | 15M Modloft: Prince Bed",
"16M Modway Fabrics B (Christopher)"=>"mf | 16M Modway Furniture: Fabrics B (Christopher)",
"16M Modway Furniture Fabric (loft)"=>"mf | 16M Modway Furniture: Fabric (loft/Florence/Geneva)",
"16M Modway Furniture Fabric Series"=>"mf | 16M Modway Furniture: Fabric (redundant)",
"16M Modway Furniture Goto fabrics"=>"mf | 16M Modway Furniture: Goto fabrics (redundant)",
"16M Modway Furniture Groovy Fabrics"=>"mf | 16M Modway Furniture: Groovy Fabrics",
"16M Modway Furniture LCW"=>"mf | 16M Modway Furniture: LCW",
"16M Modway Furniture Leather Series"=>"mf | 16M Modway Furniture: Leather",
"16M Modway Furniture plastics"=>"mf | 16M Modway Furniture: plastics",
"16M Modway Furniture retro (response) fabric"=>"mf | 16M Modway Furniture: retro (response) fabric",
"16M Modway Furniture Tulip Fabrics"=>"mf | 16M Modway Furniture: Tulip Fabrics",
"16M Modway Furniture Tulip Vinyl"=>"mf | 16M Modway Furniture: Tulip Vinyl",
"16M Modway Furniture Vinyl"=>"mf | 16M Modway Furniture: Vinyl",
"16M Modway Furniture Wood"=>"mf | 16M Modway Furniture: Wood",
"16M Modway Furniture Wool Fabric (loft)"=>"mf | 16M Modway Furniture: Wool Fabric (loft)",
"19P Phillips Collection Seat Belt"=>"pc | 19P Phillips Collection: Seat Belt",
"19P Phillips Collection Stone Tables"=>"pc | 19P Phillips Collection: Stone Tables",
"22S Shenzhen Brother 1: Cashmere blend"=>"sb | 22S Shenzhen Brother 1: Cashmere blend (cashmere blend 80% wool)",
"22S Shenzhen Brother 2: Cashmere"=>"sb | 22S Shenzhen Brother 2: Cashmere (100% Wool Imported Cashmere)",
"22S Shenzhen Brother 3: Camira"=>"sb | 22S Shenzhen Brother 3: Camira (Wool | Wool blend)",
"22S Shenzhen Brother 4: Camira Hemp"=>"sb | 22S Shenzhen Brother 4: Camira Hemp",
"22S Shenzhen Brother 5: Italian leather (standard)"=>"sb | 22S Shenzhen Brother 5: Italian leather (standard)",
"22S Shenzhen Brother 6: Aniline leather (premium)"=>"sb | 22S Shenzhen Brother 6: Aniline leather (premium)",
"22S Shenzhen Brother 7: Waxy leather (premium)"=>"sb | 22S Shenzhen Brother 7: Waxy leather (premium)",
"22S Shenzhen Brother 8: Cow Pony Hide leather (premium)"=>"sb | 22S Shenzhen Brother 8: Cow Pony Hide leather (premium)",
"23S Shenzhen MJ Boucle"=>"sbx | 23S Shenzhen MJ: Boucle (redundant)",
"23S Shenzhen MJ Cashmere"=>"sbx | 23S Shenzhen MJ: Cashmere (redundant)",
"23S Shenzhen MJ Leathers"=>"sbx | 23S Shenzhen MJ: Leathers (redundant)",
"23S Shenzhen MJ Rugi"=>"sbx | 23S Shenzhen MJ: Rugi (redundant)",
"27W Wholesale Interiors"=>"wi | 27W Wholesale Interiors (redundant)",
"27W Wholesale Interiors beige gray"=>"wi | 27W Wholesale Interiors: beige gray",
"28Y Younger Furniture Grade B-C"=>"yf | 28Y Younger Furniture: Grade B-C",
"28Y Younger Furniture Grade D"=>"yf | 28Y Younger Furniture: Grade D",
"28Y Younger Furniture Grade E"=>"yf | 28Y Younger Furniture: Grade E",
"28Y Younger Furniture Grade F-I"=>"yf | 28Y Younger Furniture: Grade F-I",
"28Y Younger Furniture Grade J-Q"=>"yf | 28Y Younger Furniture: Grade J-Q",
"28Y Younger Furniture Leather"=>"yf | 28Y Younger Furniture: Leather (redundant)",
"28Y Younger Furniture Quick-Ship"=>"yf | 28Y Younger Furniture: Quick-Ship fabrics",
"29Z Bertoia cushion"=>"zm | 29Z Zuo Modern: Bertoia cushion",
"30K Korean situ chair mesh"=>"ks | 30K Korean Supplier: situ chair mesh",
"31T TemaHome abbey (london) shelve finishes"=>"th | 31T TemaHome: abbey (london) shelve finishes",
"31T TemaHome marble (dune) fabric"=>"th | 31T TemaHome: marble (dune) fabric",
"33Y Ytoom florence tables"=>"yt | 33Y Ytoom 6: florence table tops",
"All - Bed sizes K, cK"=>"aa | Bed sizes: K, cK",
"All - Bed sizes Q, K"=>"aa | Bed sizes: Q, K",
"All - black and white"=>"aa | black and white",
"All - leatherette"=>"aa | leatherette",
"All - left and right"=>"aa | left and right",
"All - marble"=>"aa | marble & stone",
"All - size options"=>"aa | size options: S/M/L",
"COM & RUSH"=>"aa | COM & RUSH",
"Glass"=>"aa | Glass (redundant)",
"Hair-on hide"=>"aa | Hair-on hide (redundant)");

if (file_exists($file)) {
    $csvDt = $fileCsv->getData($file);
    if(count($csvDt)) {
        $collArr = [];
        $cnt = 0;
        $optId = '';
        foreach ($csvDt as $key => $value) {
            if ($key !== 0) {
                $product = $obj->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create()->addAttributeToSelect('*')->addAttributeToFilter('njm_sku',array('eq' => $value[1]));
                if (is_object($product)) {
                    $_prod = $product->getFirstItem();
                    if (is_object($_prod) && !empty($_prod->getData())) {
                        $optionsInterface = $obj->get('Magento\Catalog\Api\ProductCustomOptionRepositoryInterface');
                        foreach ($optionsInterface->getProductOptions($_prod) as $opt) {
                            $tableName = $resource->getTableName('mageworx_optiontemplates_group_option');
                            $sql = $connection->select()
                                  ->from($tableName)      
                                  ->where('option_id = ?', $opt->getGroupOptionId());
                            $result = $connection->fetchRow($sql);
                            if ($result) {
                                $tableName1 = $resource->getTableName('mageworx_optiontemplates_group');
                                $sql1 = $connection->select()
                                      ->from($tableName1)      
                                      ->where('group_id = ?', $result['group_id']);
                                $result1 = $connection->fetchRow($sql1); 
                                $groupName = $result1['title'];
                                if ($templateNames[$value[3]] == $groupName) {
                                    if ($value[4] == $opt->getTitle()) {
                                        foreach ($opt->getValues() as $vl) {
                                            if ($value[5] == $vl->getData('title')) {
                                                if ($optId == '' || $optId != $opt->getOptionId()) {
                                                    $collArr[$cnt]['product_name'] = $_prod->getName();
                                                    $collArr[$cnt]['product_sku'] = $_prod->getSku();
                                                    $collArr[$cnt]['product_option_tempalte_name'] = $groupName;
                                                    $collArr[$cnt]['product_option_title'] = $opt->getDefaultTitle();
                                                    $collArr[$cnt]['product_option_id'] = $opt->getOptionId();
                                                    $collArr[$cnt]['product_val_id'] = $vl->getOptionTypeId();
                                                    $collArr[$cnt]['product_val_title'] = $vl->getTitle();
                                                    $collArr[$cnt]['product_val_price'] = $vl->getPrice();
                                                    $collArr[$cnt]['product_val_pricetype'] = $vl->getPriceType();
                                                    $collArr[$cnt]['product_val_sku'] = $vl->getSku();
                                                    $collArr[$cnt]['product_val_qty'] = $vl->getQty();
                                                    $collArr[$cnt]['product_val_managestock'] = $vl->getManageStock();
                                                    $collArr[$cnt]['product_val_cost'] = $vl->getCost();
                                                    $collArr[$cnt]['product_val_desc'] = $vl->getDescription();
                                                    $collArr[$cnt]['product_val_isstocktab'] = $value[8];
                                                    $collArr[$cnt]['product_val_iscustomtab'] = $value[7];
                                                    $optId = $opt->getOptionId();
                                                } else {
                                                    $collArr[$cnt]['product_name'] = "";
                                                    $collArr[$cnt]['product_sku'] = "";
                                                    $collArr[$cnt]['product_option_tempalte_name'] = "";
                                                    $collArr[$cnt]['product_option_title'] = "";
                                                    $collArr[$cnt]['product_option_id'] = $opt->getOptionId();
                                                    $collArr[$cnt]['product_val_id'] = $vl->getOptionTypeId();
                                                    $collArr[$cnt]['product_val_title'] = $vl->getTitle();
                                                    $collArr[$cnt]['product_val_price'] = $vl->getPrice();
                                                    $collArr[$cnt]['product_val_pricetype'] = $vl->getPriceType();
                                                    $collArr[$cnt]['product_val_sku'] = $vl->getSku();
                                                    $collArr[$cnt]['product_val_qty'] = $vl->getQty();
                                                    $collArr[$cnt]['product_val_managestock'] = $vl->getManageStock();
                                                    $collArr[$cnt]['product_val_cost'] = $vl->getCost();
                                                    $collArr[$cnt]['product_val_desc'] = $vl->getDescription();
                                                    $collArr[$cnt]['product_val_isstocktab'] = $value[8];
                                                    $collArr[$cnt]['product_val_iscustomtab'] = $value[7];
                                                }
                                                $cnt++;
                                            }
                                        }
                                    } else if($value[4] == 'Have your own materials?' && $opt->getTitle() == 'I have my own material/leather (COM/COL)') {
                                        foreach ($opt->getValues() as $vl) {
                                            if ($value[5] == "Yes" && $vl->getData('title') == "Yes, I'd like to use my own material") {
                                                if ($optId == '' || $optId != $opt->getOptionId()) {
                                                    $collArr[$cnt]['product_name'] = $_prod->getName();
                                                    $collArr[$cnt]['product_sku'] = $_prod->getSku();
                                                    $collArr[$cnt]['product_option_tempalte_name'] = $groupName;
                                                    $collArr[$cnt]['product_option_title'] = $opt->getDefaultTitle();
                                                    $collArr[$cnt]['product_option_id'] = $opt->getOptionId();
                                                    $collArr[$cnt]['product_val_id'] = $vl->getOptionTypeId();
                                                    $collArr[$cnt]['product_val_title'] = $vl->getTitle();
                                                    $collArr[$cnt]['product_val_price'] = $vl->getPrice();
                                                    $collArr[$cnt]['product_val_pricetype'] = $vl->getPriceType();
                                                    $collArr[$cnt]['product_val_sku'] = $vl->getSku();
                                                    $collArr[$cnt]['product_val_qty'] = $vl->getQty();
                                                    $collArr[$cnt]['product_val_managestock'] = $vl->getManageStock();
                                                    $collArr[$cnt]['product_val_cost'] = $vl->getCost();
                                                    $collArr[$cnt]['product_val_desc'] = $vl->getDescription();
                                                    $collArr[$cnt]['product_val_isstocktab'] = $value[8];
                                                    $collArr[$cnt]['product_val_iscustomtab'] = $value[7];
                                                    $optId = $opt->getOptionId();
                                                } else {
                                                    $collArr[$cnt]['product_name'] = "";
                                                    $collArr[$cnt]['product_sku'] = "";
                                                    $collArr[$cnt]['product_option_tempalte_name'] = "";
                                                    $collArr[$cnt]['product_option_title'] = "";
                                                    $collArr[$cnt]['product_option_id'] = $opt->getOptionId();
                                                    $collArr[$cnt]['product_val_id'] = $vl->getOptionTypeId();
                                                    $collArr[$cnt]['product_val_title'] = $vl->getTitle();
                                                    $collArr[$cnt]['product_val_price'] = $vl->getPrice();
                                                    $collArr[$cnt]['product_val_pricetype'] = $vl->getPriceType();
                                                    $collArr[$cnt]['product_val_sku'] = $vl->getSku();
                                                    $collArr[$cnt]['product_val_qty'] = $vl->getQty();
                                                    $collArr[$cnt]['product_val_managestock'] = $vl->getManageStock();
                                                    $collArr[$cnt]['product_val_cost'] = $vl->getCost();
                                                    $collArr[$cnt]['product_val_desc'] = $vl->getDescription();
                                                    $collArr[$cnt]['product_val_isstocktab'] = $value[8];
                                                    $collArr[$cnt]['product_val_iscustomtab'] = $value[7];
                                                }
                                                $cnt++;
                                            }
                                        }
                                    } else if($value[4] == 'Rush Services' && $opt->getTitle() == 'Need It Faster? Rush Delivery Available.') {
                                        foreach ($opt->getValues() as $vl) {
                                            if ($value[5] == $vl->getData('title')) {
                                                if ($optId == '' || $optId != $opt->getOptionId()) {
                                                    $collArr[$cnt]['product_name'] = $_prod->getName();
                                                    $collArr[$cnt]['product_sku'] = $_prod->getSku();
                                                    $collArr[$cnt]['product_option_tempalte_name'] = $groupName;
                                                    $collArr[$cnt]['product_option_title'] = $opt->getDefaultTitle();
                                                    $collArr[$cnt]['product_option_id'] = $opt->getOptionId();
                                                    $collArr[$cnt]['product_val_id'] = $vl->getOptionTypeId();
                                                    $collArr[$cnt]['product_val_title'] = $vl->getTitle();
                                                    $collArr[$cnt]['product_val_price'] = $vl->getPrice();
                                                    $collArr[$cnt]['product_val_pricetype'] = $vl->getPriceType();
                                                    $collArr[$cnt]['product_val_sku'] = $vl->getSku();
                                                    $collArr[$cnt]['product_val_qty'] = $vl->getQty();
                                                    $collArr[$cnt]['product_val_managestock'] = $vl->getManageStock();
                                                    $collArr[$cnt]['product_val_cost'] = $vl->getCost();
                                                    $collArr[$cnt]['product_val_desc'] = $vl->getDescription();
                                                    $collArr[$cnt]['product_val_isstocktab'] = $value[8];
                                                    $collArr[$cnt]['product_val_iscustomtab'] = $value[7];
                                                    $optId = $opt->getOptionId();
                                                } else {
                                                    $collArr[$cnt]['product_name'] = "";
                                                    $collArr[$cnt]['product_sku'] = "";
                                                    $collArr[$cnt]['product_option_tempalte_name'] = "";
                                                    $collArr[$cnt]['product_option_title'] = "";
                                                    $collArr[$cnt]['product_option_id'] = $opt->getOptionId();
                                                    $collArr[$cnt]['product_val_id'] = $vl->getOptionTypeId();
                                                    $collArr[$cnt]['product_val_title'] = $vl->getTitle();
                                                    $collArr[$cnt]['product_val_price'] = $vl->getPrice();
                                                    $collArr[$cnt]['product_val_pricetype'] = $vl->getPriceType();
                                                    $collArr[$cnt]['product_val_sku'] = $vl->getSku();
                                                    $collArr[$cnt]['product_val_qty'] = $vl->getQty();
                                                    $collArr[$cnt]['product_val_managestock'] = $vl->getManageStock();
                                                    $collArr[$cnt]['product_val_cost'] = $vl->getCost();
                                                    $collArr[$cnt]['product_val_desc'] = $vl->getDescription();
                                                    $collArr[$cnt]['product_val_isstocktab'] = $value[8];
                                                    $collArr[$cnt]['product_val_iscustomtab'] = $value[7];
                                                }
                                                $cnt++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $fileFactory = $obj->get('Magento\Framework\App\Response\Http\FileFactory');
        
        return $fileFactory->create('product-options-new.csv', getCsvFile($collArr), 'var');
    }
}

function getCsvFile($content = array())
{
    $bootstrap = Bootstrap::create(BP, $_SERVER);
    $obj = $bootstrap->getObjectManager();
    $filesystem = $obj->get('\Magento\Framework\Filesystem');
    $directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
    $name = md5(microtime());
    $file = 'export/mpreport' . $name . '.csv';
    $directory->create('export');
    $stream = $directory->openFile($file, 'w+');
    $stream->lock();
    $headerData = array(__("Product Name"),__("Product SKU"),__("Template Name"),__("Option Title"),__("Option ID"),__("Option Value ID"),__("Option Value Title"),__("Price"),__("Price Type"),__("Option Value SKU"),__("Option Value Qty"),__("Option Value Manage Stock"),__("Option Value Cost"),__("Option Value Description"),__("Fast Ship"),__("Custom Order"));
    $stream->writeCsv($headerData);
    $last_rate = 0;
    $totalAmount = 0; 
    $totalComm = 0; 
    $cnt = 0;
    foreach ($content as $value) {
        $stream->writeCsv($value);
    }
    $stream->unlock();
    $stream->close();
    return [
        'type' => 'filename',
        'value' => $file,
        'rm' => true  // can delete file after use
    ];
}
?>