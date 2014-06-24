<?php
/**
 * @category   Cybersource
 * @package    Wsu_CentralProcessing
 */
class Wsu_CentralProcessing_Block_Redirect extends Mage_Core_Block_Abstract {
	protected function _toHtml() {
		$standard 	= $this->getOrder()->getPayment()->getMethodInstance();

        $form 		= new Varien_Data_Form();
        $form->setAction($standard->getCentralProcessingUrl())
            ->setId('centralprocessing_payment_checkout')
            ->setName('centralprocessing_payment_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
/*
		foreach ($standard->getFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }

        $merchant_id = Mage::getStoreConfig('payment/centralprocessing/merchant_id');
        $formFields = $standard->getFormFields();
        // session id -> order id
        $session_id = $reference_number = $formFields['reference_number'];

        $org_id = '';
        if(!Mage::getStoreConfig('payment/centralprocessing/mode')) {
            // test mode
            $org_id = '1snn5n9w';
        } else {
            // live mode
            $org_id = 'k8vif92e';
        }   
*/
        $html = '<h1> There would be a form that would alot post</h1>';
/*
        $html .= '<p style="background:url(https://h.online-metrix.net/fp/clear.png?org_id=' . $org_id . '&session_id=' . $merchant_id . $session_id . '&m=1)"></p>';
        $html .= '<img style="display:none;" src="https://h.online-metrix.net/fp/clear.png?org_id=' . $org_id . '&session_id=' . $merchant_id . $session_id . '&m=2" alt="">';
        $html .= '<object type="application/x-shockwave-flash" data="https://h.online-metrix.net/fp/fp.swf?org_id=' . $org_id . '&session_id=' . $merchant_id . $session_id . '" width="1" height="1" id="thm_fp"> <param name="movie" value="https://h.online-metrix.net/fp/fp.swf?org_id=' . $org_id . '&session_id=' . $merchant_id . $session_id . '" /> <div></div> </object>';
        $html .= '<script src="https://h.online-metrix.net/fp/check.js?org_id=' . $org_id . '&session_id=' . $merchant_id . $session_id . '" type="text/javascript"> </script>';
*/
        $html.= $this->__('You will be redirected to CyberSource Secure Acceptance WM in a few seconds.');
		$html.= $form->toHtml();
/*		// die($html);
        $html.= '<script type="text/javascript">document.getElementById("centralprocessing_payment_checkout").submit();</script>';
		*/
        $html.= '';

		return $html;
    }
}