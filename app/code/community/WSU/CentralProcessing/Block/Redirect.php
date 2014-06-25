<?php
/**
 * @category   Cybersource
 * @package    Wsu_CentralProcessing
 */
class Wsu_CentralProcessing_Block_Redirect extends Mage_Core_Block_Abstract {
	protected function _toHtml() {
	$standard 	= $this->getOrder()->getPayment()->getMethodInstance();
	$helper				= Mage::helper('centralprocessing');
        $form 		= new Varien_Data_Form();
        $form->setAction($helper->getCentralProcessingUrl())
            ->setId('centralprocessing_payment_checkout')
            ->setName('centralprocessing_payment_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
		$formFields = $standard->getFormFields();
		foreach ($formFields as $field => $value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }




		//url-ify the data for the POST
		$fields_string="";
		$url = trim($helper->getCentralProcessingUrl(),'/');
		$url .= DS.($helper->getAuthorizationType()=="AUTHCAP"?"AuthCapRequestWithCancelURL":"AuthRequestWithCancelURL");
		
		
		foreach($formFields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
		
		
		$wrapper = fopen('php://temp', 'r+');
		
		//open connection
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_STDERR, $wrapper);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($formFields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		
		//execute post
		$result = curl_exec($ch);
		if($result === false) {
			echo 'Curl error: ' . curl_error($ch);
		}
		
		
		//close connection
		curl_close($ch);
		var_dump($url);
		var_dump($fields_string);
		var_dump($result);die();


		



        $merchant_id = Mage::getStoreConfig('payment/centralprocessing/merchant_id');
        //$formFields = $standard->getFormFields();
        $idSuffix = Mage::helper('core')->uniqHash();
        $submitButton = new Varien_Data_Form_Element_Submit(array(
            'value'    => $this->__('Click here if you are not redirected within 10 seconds...'),
        ));
        $id = "submit_to_centralprocessing_button_{$idSuffix}";
        $submitButton->setId($id);
		$form->addElement($submitButton);
		

        // session id -> order id
        //$session_id = $reference_number = $formFields['reference_number'];
/*	
        $org_id = '';
        if(!Mage::getStoreConfig('payment/centralprocessing/mode')) {
            // test mode
            $org_id = '1snn5n9w';
        } else {
            // live mode
            $org_id = 'k8vif92e';
        }   
*/
 $html = '';
        //$html .= '<h1> There would be a form that would alot post</h1>';
/*
        $html .= '<p style="background:url(https://h.online-metrix.net/fp/clear.png?org_id=' . $org_id . '&session_id=' . $merchant_id . $session_id . '&m=1)"></p>';
        $html .= '<img style="display:none;" src="https://h.online-metrix.net/fp/clear.png?org_id=' . $org_id . '&session_id=' . $merchant_id . $session_id . '&m=2" alt="">';
        $html .= '<object type="application/x-shockwave-flash" data="https://h.online-metrix.net/fp/fp.swf?org_id=' . $org_id . '&session_id=' . $merchant_id . $session_id . '" width="1" height="1" id="thm_fp"> <param name="movie" value="https://h.online-metrix.net/fp/fp.swf?org_id=' . $org_id . '&session_id=' . $merchant_id . $session_id . '" /> <div></div> </object>';
        $html .= '<script src="https://h.online-metrix.net/fp/check.js?org_id=' . $org_id . '&session_id=' . $merchant_id . $session_id . '" type="text/javascript"> </script>';
*/
        $html.= $this->__('You will be redirected to CyberSource Secure Acceptance WM in a few seconds.');
		$html.= $form->toHtml();
		
		//var_dump($html);die();
		
		
		// die($html);
/*        $html.= '<script type="text/javascript">document.getElementById("centralprocessing_payment_checkout").submit();</script>';
		
        $html.= '';
*/
		return $html;
    }
}