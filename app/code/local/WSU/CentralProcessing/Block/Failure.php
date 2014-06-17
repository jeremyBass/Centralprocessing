<?php
/**
 * @category   Cybersource
 * @package    Wsu_CentralProcessing
 */
class Wsu_CentralProcessing_Block_Failure extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('centralprocessing/failure.phtml');
    }

    /**
     * Get continue shopping url
     */
    public function getContinueShoppingUrl()
    {
        return Mage::getUrl('checkout/cart');
    }
}