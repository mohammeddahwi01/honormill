<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend');
$fileCsv = $obj->get('\Magento\Framework\File\Csv');
$file = 'desc-update.csv';

if (file_exists($file)) {
    $csvDt = $fileCsv->getData($file);
    if(count($csvDt)) {
        $tmpArr = [];
        $tmpCount = 0;
        foreach ($csvDt as $ind => $val) {
            /*echo "<pre>";
            print_r($val);
            echo "</pre>";exit();*/
            if ($ind == 0) {
                continue;
            }
            $sku = '';
            $title = '';
            $valCount = '';
            $desc = '';

            $sku = $val[0];
            $title = $val[1];
            $valCount = $val[2];
            $desc = $val[3];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('mageworx_optionfeatures_option_description');
            $productId = $obj->create('\Magento\Catalog\Model\Product')->getIdBySku($sku);
            $product = $obj->create('\Magento\Catalog\Model\Product')->load($productId);
            echo "<br/>".$product->getUrlKey()."<br/>";
            foreach ($product->getOptions() as $opt) {
                $potitle = $opt->getTitle();
                if ($potitle == 'I have my own material/leather (COM/COL)') {
                    $potitle = 'I Have My Own Material / Leather';
                } elseif ($potitle == 'Need It Faster? Rush Delivery Available.') {
                    $potitle = 'Need It Faster? Rush Delivery';
                }
                if ($potitle == $title && count($opt->getValues()) == $valCount && $opt->getDescription() == '' && $desc != '') {
                    $sql = $connection->select()
                          ->from($tableName)      
                          ->where('mageworx_option_id = ?', $opt->getMageworxOptionId());
                    $result = $connection->fetchAll($sql); 
                    if (count($result)) {
                        $sql = "UPDATE " . $tableName . " SET description ='".$desc."' WHERE mageworx_option_id='".$opt->getMageworxOptionId()."'";
                        $connection->query($sql);
                        echo $title." == ".$desc." Updated <br/> \n";
                    } else {
                        $sql = "INSERT INTO " . $tableName . " (option_description_id, mageworx_option_id, store_id, description) VALUES ('', '".$opt->getMageworxOptionId()."', 0, '".$desc."')";
                        $connection->query($sql);
                        echo $title." == ".$desc." Updated <br/> \n";
                    }
                }
            }
        }
    }
}
function recreateString($value='')
{   
    $newval = '';
    if ($value != '') {
        $newval = preg_replace("/[^a-zA-Z]+/", "", $value);
    }
    return $newval;

}
?>