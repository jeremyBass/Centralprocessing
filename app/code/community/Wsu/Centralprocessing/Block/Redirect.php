<?php
/**
 * @category   Cybersource
 * @package    Wsu_Centralprocessing
 */
class Wsu_Centralprocessing_Block_Redirect extends Mage_Core_Block_Abstract {
	
	



	
	
	
	protected function _toHtml() {
		$standard 	= $this->getOrder()->getPayment()->getMethodInstance();
		$helper				= Mage::helper('centralprocessing');
        $form 		= new Varien_Data_Form();
        $form->setAction($helper->getCentralprocessingUrl())
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
		$url = trim($helper->getCentralprocessingUrl(),'/');
		$url .= DS.($helper->getAuthorizationType()=="AUTHCAP"?"AuthCapRequestWithAddress":"AuthCapRequestWithAddress");//AuthRequestWithCancelURL
		
		
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

		ob_start();
		var_dump($url);
		var_dump($fields_string);
		var_dump($result);
		$log = ob_get_clean();

		Mage::log($log,Zend_Log::NOTICE,"redirect-result.txt");
		
		
		/**/
		$nodes = new SimpleXMLElement($helper->removeResponseXMLNS($result));
		//$code = $nodes->RequestReturnCode;  // put in just in case
		$urlRedirect = $nodes->WebPageURLAndGUID;
		$guid = $nodes->RequestGUID;
		$state = json_decode($formFields['ApplicationStateData']);
		$order = Mage::getModel('sales/order')->load($state->roid,'increment_id');
		$payment = $order->getPayment();
		$payment->setResponseGuid($guid);
		$payment->setCcMode($helper->getConfig('mode')>0?"live":"test");
		$payment->save();
				

		ob_start();
		var_dump($urlRedirect);
		$log = ob_get_clean();		

		Mage::log($log,Zend_Log::NOTICE,"redirect.txt");
		
		
		/**/
		header("Location: ".$urlRedirect);
		exit();
    }
}