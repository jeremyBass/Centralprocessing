<?php
/**
 * @category   Cybersource
 * @package    Wsu_CentralProcessing
 */
class Wsu_CentralProcessing_Helper_Data extends Mage_Core_Helper_Abstract {
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

	public function getCentralProcessingUrl() {
		$setIssuerUrls 	= $this->getIssuerUrls();
		if($this->getConfig('mode')){
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

}