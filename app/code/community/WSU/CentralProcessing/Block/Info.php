<?php
/**
 * @category   Cybersource
 * @package    Wsu_CentralProcessing
 */
class Wsu_CentralProcessing_Block_Info extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        //$this->setTemplate('wsu/centralprocessing/info.phtml');
    }

    public function getMethodCode() {
        return $this->getInfo()->getMethodInstance()->getCode();
    }
	
    protected function _prepareSpecificInformation($transport = null){
        if ($this->_paymentSpecificInformation !== null) {
            return $this->_paymentSpecificInformation;
        }

        $info = $this->getInfo();

        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        $transport->addData(array(
            Mage::helper('payment')->__('Response Return Code') => $info->getResponseReturnCode(),
            Mage::helper('payment')->__('GUID') => $info->getResponseGuid(),
			Mage::helper('payment')->__('Approval Code') => $info->getApprovalCode(),
			Mage::helper('payment')->__('Card Type') => $info->getCardType(),
			Mage::helper('payment')->__('Masked CC Number') => '############'.$info->getMaskedCcNumber()
        ));
        return $transport;
    }

	
	
}