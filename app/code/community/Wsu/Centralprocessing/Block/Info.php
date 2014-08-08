<?php
/**
 * @category   Cybersource
 * @package    Wsu_Centralprocessing
 */
class Wsu_Centralprocessing_Block_Info extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        //$this->setTemplate('wsu/centralprocessing/info.phtml');
    }

    public function getMethodCode() {
        return $this->getInfo()->getMethodInstance()->getCode();
    }
	
    protected function _prepareSpecificInformation($transport = null){
		$helper				= Mage::helper('centralprocessing');
        if ($this->_paymentSpecificInformation !== null) {
            return $this->_paymentSpecificInformation;
        }
		
        $info = $this->getInfo();

        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
		
		$transData=array();

		$transData[Mage::helper('payment')->__('Card Type')]=$helper->getCardType($info->getCardType());
		$transData[Mage::helper('payment')->__('Masked CC Number')]='############'.$info->getMaskedCcNumber();
		
		
		$GUID = $info->getResponseGuid();
		$mode = $info->getCcMode();
		$GUIDinfo=$helper->getResponseGuidInfo($GUID,($mode=="live"?1:0));

		$transData[Mage::helper('payment')->__('Response Return Code')]="".$info->getResponseReturnCode();
		$transData[Mage::helper('payment')->__('GUID')]=$GUID.$GUIDinfo;
		$transData[Mage::helper('payment')->__('Approval Code')]=$info->getApprovalCode();
		$transData[Mage::helper('payment')->__('CC Mode')]=$mode;

        $transport->addData($transData);

        return $transport;
    }

	
	
}