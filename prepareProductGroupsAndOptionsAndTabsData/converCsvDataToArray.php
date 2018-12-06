<?php
$tabsCsv = explode("\n", file_get_contents('product_stock_and_custom_tabs_all_with_optionsku.csv'));
$tabsData = array();
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
echo "<pre>";
print_r($tabsHeader);
print_r($tabsData2);
echo "</pre>";
?>