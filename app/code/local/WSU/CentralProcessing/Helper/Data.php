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
}