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
		return $this->getIpnUrl();//use_cancelurl cancelurl   use_return_url return_url
	}

	public function getReturnURL() {
		return Mage::getUrl( ( $this->getConfig('use_return_url') ? $this->getConfig('return_url') : 'checkout/cart' ), array('_secure' => true)); 
	}

	public function getCancelUrl() {
		return Mage::getUrl( ( $this->getConfig('use_cancel_url') ? $this->getConfig('cancel_url') : 'checkout/cart' ), array('_secure' => true));
	}

    public function getOrderPlaceRedirectUrl() {
          return Mage::getUrl('centralprocessing/process/redirect');
    }

    protected function getSuccessUrl() {
		return Mage::getUrl('centralprocessing/process/success', array('_secure' => true));
	}

	protected function getFailureUrl() {
        return Mage::getUrl('centralprocessing/process/failure', array('_secure' => true));
    }

    protected function getCancelUrl() {
        return Mage::getUrl('centralprocessing/process/cancel', array('_secure' => true));
    }

    protected function getIpnUrl() {
        return Mage::getUrl('centralprocessing/process/ipn', array('_secure' => true));
    }

}