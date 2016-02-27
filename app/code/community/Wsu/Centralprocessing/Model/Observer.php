<?php
/**
 * Call actions after configuration is saved
 */
class Wsu_Centralprocessing_Model_Observer {

	/**
	 * After store view is saved
	 */
	public function checkMultishippingLinkVisiblity(Varien_Event_Observer $observer) {
		$block = $observer->getBlock();
		
		if ( $block instanceof Mage_Checkout_Block_Multishipping_Link ) {	
			//var_dump($block);
			$payments = Mage::getSingleton('payment/config')->getActiveMethods();
			$methods = array( array( 'value'=>'', 'label'=>Mage::helper('adminhtml')->__('–Please Select–') ) );
			$multishippmentOk = false;
			$quote = Mage::getModel('checkout/session')->getQuote();
			$quoteData= $quote->getData();
			$grandTotal=$quoteData['grand_total'];
	
			foreach($payments as $paymentCode=>$paymentModel) {
				//var_dump($paymentCode);
				if( !$multishippmentOk && $paymentModel->canUseForMultishipping() && ( "free" !== $paymentCode || ( "free" === $paymentCode && !($grandTotal>0) ) ) ){
						$multishippmentOk = true;
				}
			}

			if( false === $multishippmentOk ) {
				$transport = $observer->getTransport();
				$transport->setHtml("");
			}
		}
	}

}
