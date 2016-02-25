<?php
/**
 * @category   Cybersource
 * @package    Wsu_Centralprocessing
 */
class Wsu_Centralprocessing_Block_Redirect extends Mage_Core_Block_Abstract {
	
	protected function _toHtml() {
		$helper		= Mage::helper('centralprocessing');
		
		$order = $this->getOrder();
		if(!empty($order)){
			$helper->order_obj = $order;
		}elseif(!empty($orders)){
			$orders = $this->getOrders();
		}else{
			Mage::throwException('Empty order');
		}
		
		$urlRedirect = $helper->makeGatewayRequest();

		header("Location: ".$urlRedirect);
		exit();

    }
}