<?php
/**
 * @category   Cybersource
 * @package    Wsu_CentralProcessing
 */
class Wsu_CentralProcessing_Block_Info extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('centralprocessing/info.phtml');
    }

    public function getMethodCode() {
        return $this->getInfo()->getMethodInstance()->getCode();
    }
}