<?php
use Magento\Framework\App\Bootstrap; 
require DIR . '/../app/bootstrap.php'; 
$bootstrap = Bootstrap::create(BP, $_SERVER); 
$obj = $bootstrap->getObjectManager(); 
$state = $obj->get('Magento\Framework\App\State'); 
$state->setAreaCode('frontend');
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();


if(isset($_POST['prod_id'])){

	//$product = $objectManager->create("Magento\Catalog\Model\Product")->load($_POST['prod_id']);

	$salableQty = 0;
	
	$StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
	$qty = $StockState->execute($_POST['prod_id']);

	if(!empty($qty)){
		foreach($qty as $_qty){
			$salableQty += $_qty['qty'];
		}
	}	
	
	if($salableQty > 0){
		echo $salableQty;
		exit();
	}else{
		echo 0;
		exit();
	}

}

?>