<?php
/**
 * @category   Cybersource
 * @package    Wsu_CentralProcessing
 */
class Wsu_CentralProcessing_Model_System_Config_Source_Modes {
    public function toOptionArray() {
        return array(
            0    => Mage::helper('centralprocessing')->__('Test'),
            1    => Mage::helper('centralprocessing')->__('Live'),
        );
    }
}