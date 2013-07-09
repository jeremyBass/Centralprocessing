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
 * HSS iframe block
 *
 * @category   Mage
 * @package    Wsu_Centralprocessing
 * @author     jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Centralprocessing_Block_Iframe extends Mage_Payment_Block_Form
{
    /**
     * Whether the block should be eventually rendered
     *
     * @var bool
     */
    protected $_shouldRender = false;

    /**
     * Order object
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_paymentMethodCode;

    /**
     * Current iframe block instance
     *
     * @var Mage_Payment_Block_Form
     */
    protected $_block;

    /**
     * Internal constructor
     * Set info template for payment step
     *
     */
    protected function _construct()
    {
        
        parent::_construct();
        $paymentCode = $this->_getCheckout()
            ->getQuote()
            ->getPayment()
            ->getMethod();
        if (in_array($paymentCode, $this->helper('centralprocessing/hss')->getHssMethods())) {
            $this->_paymentMethodCode = $paymentCode;
            $templatePath = str_replace('_', '', $paymentCode);
            $templateFile = "centralprocessing/{$templatePath}/iframe.phtml";
            if (file_exists(Mage::getDesign()->getTemplateFilename($templateFile))) {
                $this->setTemplate($templateFile);
            } else {
                $this->setTemplate('centralprocessing/hss/iframe.phtml');
            }
        }
    }

    /**
     * Get current block instance
     *
     * @return Wsu_Centralprocessing_Block_Iframe
     */
    protected function _getBlock()
    {
        
        if (!$this->_block) {
            $this->_block = $this->getAction()
                ->getLayout()
                ->createBlock('centralprocessing/'.$this->_paymentMethodCode.'_iframe');
            if (!$this->_block instanceof Wsu_Centralprocessing_Block_Iframe) {
                Mage::throwException('Invalid block type');
            }
        }

        return $this->_block;
    }

    /**
     * Get order object
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        
        if (!$this->_order) {
            $incrementId = $this->_getCheckout()->getLastRealOrderId();
            $this->_order = Mage::getModel('sales/order')
                ->loadByIncrementId($incrementId);
        }
        return $this->_order;
    }

    /**
     * Get frontend checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Before rendering html, check if is block rendering needed
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        
        if ($this->_getOrder()->getId() &&
            $this->_getOrder()->getQuoteId() == $this->_getCheckout()->getLastQuoteId() &&
            $this->_paymentMethodCode) {
            $this->_shouldRender = true;
        }

        if ($this->getGotoSection() || $this->getGotoSuccessPage()) {
            $this->_shouldRender = true;
        }

        return parent::_beforeToHtml();
    }

    /**
     * Render the block if needed
     *
     * @return string
     */
    protected function _toHtml()
    {
        
        if ($this->_isAfterPaymentSave()) {
            $this->setTemplate('centralprocessing/hss/js.phtml');
            return parent::_toHtml();
        }
        if (!$this->_shouldRender) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Check whether block is rendering after save payment
     *
     * @return bool
     */
    protected function _isAfterPaymentSave()
    {
        
        $quote = $this->_getCheckout()->getQuote();
        if ($quote->getPayment()->getMethod() == $this->_paymentMethodCode &&
            $quote->getIsActive() &&
            $this->getTemplate() &&
            $this->getRequest()->getActionName() == 'savePayment') {
            return true;
        }

        return false;
    }

    /**
     * Get iframe action URL
     *
     * @return string
     */
    public function getFrameActionUrl()
    {
        
        return $this->_getBlock()->getFrameActionUrl();
    }

    /**
     * Get secure guid
     *
     * @return string
     */
    public function getSecureGuid()
    {
        
        return $this->_getBlock()->getSecureGuid();
    }

    /**
     * Get secure guid ID
     *
     * @return string
     */
    public function getSecureGuidId()
    {
        
        return $this->_getBlock()->getSecureGuidId();
    }

    /**
     * Get payflow transaction URL
     *
     * @return string
     */
    public function getTransactionUrl()
    {
        
        return $this->_getBlock()->getTransactionUrl();
    }

    /**
     * Check sandbox mode
     *
     * @return bool
     */
    public function isTestMode()
    {
        
        return $this->_getBlock()->isTestMode();
    }
}
