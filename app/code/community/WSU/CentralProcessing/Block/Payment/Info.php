<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Wsu
 * @package     Wsu_Centralprocessing
 * @copyright   Copyright (c) 2012+ Wsu. (http://wsu.edu)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Centralprocessing common payment info block
 * Uses default templates
 */
class Wsu_Centralprocessing_Block_Payment_Info extends Mage_Payment_Block_Info_Cc
{
    /**
     * Don't show CC type for non-CC methods
     *
     * @return string|null
     */
    public function getCcTypeName()
    {
        if (Wsu_Centralprocessing_Model_Config::getIsCreditCardMethod($this->getInfo()->getMethod())) {
            return parent::getCcTypeName();
        }
    }

    /**
     * Prepare Centralprocessing-specific payment information
     *
     * @param Varien_Object|array $transport
     * return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $centralprocessingInfo = Mage::getModel('centralprocessing/info');
        if (!$this->getIsSecureMode()) {
            $info = $centralprocessingInfo->getPaymentInfo($payment, true);
        } else {
            $info = $centralprocessingInfo->getPublicPaymentInfo($payment, true);
        }
        return $transport->addData($info);
    }
}
