<?php
/**
 * Call actions after configuration is saved
 */
class Wsu_Centralprocessing_Model_Observer
{

    /**
     * After store view is saved
     */
    public function checkMultishippingLinkVisiblity(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();

        if ( $block instanceof Mage_Checkout_Block_Multishipping_Link ) {
            $payments = Mage::getSingleton('payment/config')->getActiveMethods();
            $multishippmentOk = false;
            $quote = Mage::getModel('checkout/session')->getQuote();
            $quoteData= $quote->getData();
            $grandTotal=$quoteData['grand_total'];

            foreach($payments as $paymentCode=>$paymentModel) {
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
	
	/*
	 *
	 */
	public function testModeNotice(){
		$active = Mage::getStoreConfig('payment/centralprocessing/active');
		if( 0 != $active){
			$mode = Mage::getStoreConfig('payment/centralprocessing/mode');
			if( 0 == $mode ){
				if( null === Mage::registry('test_notice_set') ){
					Mage::register('test_notice_set', true);
					$helper		= Mage::helper('centralprocessing');
					$message = "IMPORTANT:: the credit card gateway is in TEST MODE.  All transactions are not real till it is out of the TEST MODE on.";
					Mage::getSingleton( ($helper->isAdmin()?'adminhtml':'core').'/session')->addNotice($message);
				}
			}
		}
	}

}
