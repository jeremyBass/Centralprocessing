<?php
/**
 * @category   Cybersource
 * @package    Wsu_Centralprocessing
 */
class Wsu_Centralprocessing_Model_System_Config_Source_Authorization_Type {
    public function toOptionArray() {
        return array(
            'AUTH'    => Mage::helper('centralprocessing')->__('Authorization'),
            'AUTHCAP'    => Mage::helper('centralprocessing')->__('Authorization and Capture'),
        );
    }
}