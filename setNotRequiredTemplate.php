<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$obj = $bootstrap->getObjectManager();
$appState = $obj->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend'); 

$storeManager = $obj->get("\Magento\Store\Model\StoreManagerInterface");
$templateCollection = $obj->create('MageWorx\OptionTemplates\Model\Group')->getCollection();
foreach ($templateCollection as $temaplte){
	foreach ($temaplte->getOptions() as $opt) {
		$opt->setIsRequire(0);
		$opt->save();
	}
	echo $temaplte->getTitle().'<br/>';
}
echo "All Products Updated!!!!";
exit();
?>