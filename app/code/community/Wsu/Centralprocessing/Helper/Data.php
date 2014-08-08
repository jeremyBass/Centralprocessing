<?php
/**
 * @category   Cybersource
 * @package    Wsu_Centralprocessing
 */
class Wsu_Centralprocessing_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getConfig($field, $default = null) {
        $value = Mage::getStoreConfig('payment/centralprocessing/' . $field);
        if(!isset($value) or trim($value) == ''){
            return $default;
        }else{
            return $value;
        }
    }

    public function log($data) {
		if(!$this->getConfig('enable_log')){
			return;
		}
		$separator = "===================================================================";
        Mage::log($separator, null, 'centralprocessing.log', true);
        Mage::log($data, null, 'centralprocessing.log', true);
    }
	
	public function getResponseGuidInfo($GUID,$mode){
		$html="";
		if($GUID!=""){
			$fields_string="RequestGUID=".$GUID;
			$url = trim($this->getCentralprocessingUrl($mode),'/');
			$url .= DS.($this->getAuthorizationType()=="AUTHCAP"?"AuthCapResponse":"AuthCapResponse");		

			$wrapper = fopen('php://temp', 'r+');
			
			//open connection
			$ch = curl_init($url);
			
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_STDERR, $wrapper);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count(1));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			
			//execute post
			$result = curl_exec($ch);
			if($result === false) {
				echo 'Curl error: ' . curl_error($ch);
			}
			//close connection
			curl_close($ch);

			$html=sprintf("<a href='#' id='showGuidInfo'>record info</a><div id='guidInfo' style='display:none;'>%s</div><script>(function($){ $(document).ready(function(){ $('#showGuidInfo').on('click',function(){ if( $('#guidInfo').is(':visible')){ $('#guidInfo').hide(); }else{ $('#guidInfo').show(); } });   }); })(jQuery);</script>",$result);
		}
		return $html;
	}
	
	
	
	
	public function removeResponseXMLNS($input) { 
		// Remove XML response namespaces one by one 
		$input = str_replace(' xmlns="webservice.it.wsu.edu"','',$input); 
		$input = str_replace(' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"','',$input); 
		return str_replace(' xmlns:xsd="http://www.w3.org/2001/XMLSchema"','',$input); 
	} 
	
	public function getHashSign($params, $signedField = 'signed_field_names') {
		$signedFieldNames = explode(",", $params[$signedField]);
        foreach ($signedFieldNames as &$field) {
           $dataToSign[] = $field . "=" . $params[$field];
        }
        $data      =  implode(",", $dataToSign);
		$secretKey = $this->getConfig('secret_key');
		$hashSign = base64_encode(hash_hmac('sha256', $data, $secretKey, true));
		return $hashSign;
	}
	
	public function getInfo(){
		$message = $this->getConfig('checkout_info');
		return $this->__($message);
	}

	public function getAuthorizationType(){ 
		return $this->getConfig('authorization_type');
	}

	public function getIssuerUrls() {
		return array("live" => $this->getConfig('live_hop_url'),
					 "test" => $this->getConfig('test_hop_url'));

	}

	public function getCentralprocessingUrl($mode=0) {
		$setIssuerUrls 	= $this->getIssuerUrls();
		if($this->getConfig('mode') || $mode==1){
			return $setIssuerUrls["live"];
		}else{
			return $setIssuerUrls["test"];
		}
	}

	public function getPostbackUrl() {
		return $this->getIpnUrl();
	}

	public function getReturnURL() {
		return Mage::getUrl( ( $this->getConfig('use_return_url') ? $this->getConfig('return_url') : 'processing/process/router' ), array('_secure' => true) ); 
	}

	public function getCancelUrl() {
		return Mage::getUrl( ( $this->getConfig('use_cancel_url') ? $this->getConfig('cancel_url') : 'processing/process/cancel' ), array('_secure' => true) );
	}

    protected function getIpnUrl() {
        return Mage::getUrl('processing/process/ipn', array('_secure' => true));
    }
	
	public function getCardType($code){
		$cards=Mage::getModel('centralprocessing/system_config_source_cards_type')->toOptionArray();
		return isset($cards[$code])?$cards[$code]:"unset";
	}
	
	
	
	
	
	
	

}