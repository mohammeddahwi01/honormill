<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend');
$fileCsv = $obj->get('\Magento\Framework\File\Csv');
// $file = 'product-options.csv';
// $file = 'product-options-with-images.csv';
$file = 'updated-honormill-options.csv';

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
            if ($val[0] !== '') {
                $tmpCount++;
                $pname = $val[0];
                $psku = $val[1];
                $templatename = $val[2];
                $opttitle = $val[3];
            }
            $opId = $val[4];
            $opValId = $val[5];
            $tmpArr[$tmpCount]['values'][$opValId] = [
                'poption_id' => $val[4],
                'pval_id' => $val[5],
                'pval_isstocktab' => $val[14],
                'pval_iscustomtab' => $val[15],
            ];
        }

        if (count($tmpArr) > 0) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('catalog_product_option_type_value');
            foreach ($tmpArr as $value) {
                foreach ($value['values'] as $val) {
                    $id = $val['pval_id']; // table row id to update
                    $sql = "UPDATE " . $tableName . " SET is_stocktab ='".$val['pval_isstocktab']."', is_customtab = '".$val['pval_iscustomtab']."' WHERE option_type_id = " . $id;
                    $connection->query($sql);
                    echo $id." Updated <br/> \n";
                }
            }
        }
    }
}
?>