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
$file = 'product-options-with-images-1.csv';

if (file_exists($file)) {
    /*echo gen_uuid();
    exit();*/
    $csvDt = $fileCsv->getData($file);
    if(count($csvDt)) {
        $tmpArr = [];
        $tmpCount = 0;
        foreach ($csvDt as $ind => $val) {
            /*echo "<pre>";
            print_r($val);
            echo "</pre>";
            exit();*/
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
            $uuid = gen_uuid();
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
                'tooltip_image' => $val[17],
                'tooltip_image_type' => $val[18],
                'base_image' => $val[19],
                'base_image_type' => $val[20],
                'images_data' => $val[21],
                // 'mageworx_option_type_id' => $val[22],
                'mageworx_option_type_id' => $uuid,
                'is_delete' => 0,
            ];
            /*echo "Old: ".$val[22]."<br/>";
            echo "New: ".gen_uuid();
            exit();*/
            if ($val[21] != '') {
                $imagesData = json_decode($val[21]);
                $optValMageworxId = $uuid;
                foreach ($imagesData as $val) {
                    $model = $obj->create('MageWorx\OptionFeatures\Model\Image');
                    $model->addData([
                        "mageworx_option_type_id" => $optValMageworxId,
                        "value" => $val->value,
                        "title_text" => $val->title_text,
                        "sort_order" => $val->sort_order,
                        "base_image" => $val->base_image,
                        "replace_main_gallery_image" => $val->replace_main_gallery_image,
                        "custom_media_type" => $val->custom_media_type,
                        "color" => $val->color, 
                        "disabled" => $val->disabled,
                        "tooltip_image" => $val->tooltip_image,
                        ]);
                    $saveData = $model->save();
                }
            }
        }
        /*echo "<pre>";
        print_r($tmpArr);
        echo "</pre>";
        exit();*/
        if (count($tmpArr) > 0) {
            foreach($tmpArr as $opt) {
                $sku = $opt['psku'];
                if ($opt['type'] == 'drop_down') {
                    $opt['is_swatch'] = 1;
                } else if($opt['type'] == 'checkbox') {
                    $opt['sort_order'] = 1;
                }
                unset($opt['psku']);
                $productId = $obj->create('\Magento\Catalog\Model\Product')->getIdBySku($sku);
                $product = $obj->create('\Magento\Catalog\Model\Product')->load($productId);
                
                /*$optionRepository = $obj->create('Magento\Catalog\Api\ProductCustomOptionRepositoryInterface');
                foreach ($optionRepository->getProductOptions($product) as $option) {
                    $optionRepository->delete($option);
                }*/

                $product->setHasOptions(1);
                $product->setCanSaveCustomOptions(true);
                $option = $obj->create('\Magento\Catalog\Model\Product\Option')
                    ->setProductId($productId)
                    ->setStoreId(0)
                    ->addData($opt);
                $option->save();
                $product->addOption($option);
                echo "Updated Success Fully ".$sku."<br/>";
            }
        }
        echo "All Done!";
    }
}
function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}
?>