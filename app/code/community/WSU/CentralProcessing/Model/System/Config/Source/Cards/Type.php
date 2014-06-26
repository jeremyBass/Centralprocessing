<?php
/**
 * @category   Cybersource
 * @package    Wsu_CentralProcessing
 */
class Wsu_CentralProcessing_Model_System_Config_Source_Card_Type {
    public function toOptionArray() {
        return array(
            '001'    => Mage::helper('centralprocessing')->__('Visa'),
            '002'    => Mage::helper('centralprocessing')->__('MasterCard'),
        );
    }
}