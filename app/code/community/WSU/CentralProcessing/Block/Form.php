<?php
/**
 * @category   Cybersource
 * @package    Wsu_CentralProcessing
 */
class Wsu_CentralProcessing_Block_Form extends Mage_Payment_Block_Form {
	protected function _construct() {
        $this->setTemplate('wsu/centralprocessing/form.phtml');
        parent::_construct();
    }
}
