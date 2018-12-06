<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend');
$fileCsv = $obj->get('\Magento\Framework\File\Csv');
// $file = 'product-options.csv';
$file = 'product-options-with-images-1.csv';

if (file_exists($file)) {
    $csvDt = $fileCsv->getData($file);
    if(count($csvDt)) {
        $tmpArr = [];
        $tmpCount = 0;
        foreach ($csvDt as $ind => $val) {
            if ($ind == 0) {
                continue;
            }
            if ($val[0] !== '') {
                $tmpCount++;
                $pname = $val[0];
                $psku = $val[1];
                $templatename = $val[2];
                $opttitle = $val[4];
                $tmpArr[$val[5]]['psku'] = $psku;
                $tmpArr[$val[5]]['sort_order'] = 0;
                $tmpArr[$val[5]]['title'] = $val[4];
                $tmpArr[$val[5]]['price_type'] = "fixed";
                $tmpArr[$val[5]]['price'] = "";
                $tmpArr[$val[5]]['type'] = $val[3];
                $tmpArr[$val[5]]['is_require'] = 0;
                $tmpArr[$val[5]]['values'] = array();
            }
            $opId = $val[4];
            $opValId = $val[6];
            $record_number = count($tmpArr[$val[5]]['values']);
            $tmpArr[$val[5]]['values'][$record_number] = [
                'record_id'=> $record_number,
                'title' => $val[7],
                'price' => $val[8],
                'price_type' => $val[9],
                'sku' => $val[10],
                'qty' => $val[11],
                'manage_stock' => $val[12],
                'cost' => $val[13],
                'description' => $val[14],
                'is_stocktab' => $val[15],
                'is_customtab' => $val[16],
                'is_delete' => 0,
            ];
        }
        $tmpArr1 = array();
        if (count($tmpArr) > 0) {
            foreach($tmpArr as $opt) {
                $sku = $opt['psku'];
                $productId = $obj->create('\Magento\Catalog\Model\Product')->getIdBySku($sku);
                $product = $obj->create('Magento\Catalog\Api\ProductRepositoryInterface')->getById($productId, false, 0);
                $product->setCanSaveCustomOptions(true);
                $product->save();
                echo "Updated Success Fully ".$sku."<br/>";
            }
        }
        echo "All Done!";
        exit;
    }
}
?>