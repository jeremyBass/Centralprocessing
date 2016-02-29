<?php
/**
 * @category   Cybersource
 * @package    Wsu_Centralprocessing
 */
class Wsu_Centralprocessing_Model_System_Config_Source_Cards_Type
{
    public function toOptionArray()
    {
        return array(
            '001'    => Mage::helper('centralprocessing')->__('Visa'),
            '002'    => Mage::helper('centralprocessing')->__('MasterCard'),
        );
    }
}
