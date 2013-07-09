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
 * Centralprocessing Standard Checkout Controller
 *
 * @category   Mage
 * @package    Wsu_Centralprocessing
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Centralprocessing_StandardController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;

    /**
     *  Get order
     *
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        
        if ($this->_order == null) {
        }
        return $this->_order;
    }

    /**
     * Send expire header to ajax response
     *
     */
    protected function _expireAjax()
    {
        
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    /**
     * Get singleton with centralprocessing strandard order transaction information
     *
     * @return Wsu_Centralprocessing_Model_Standard
     */
    public function getStandard()
    {
        
        return Mage::getSingleton('centralprocessing/standard');
    }

    /**
     * When a customer chooses Centralprocessing on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        
        $session = Mage::getSingleton('checkout/session');
        $session->setCentralprocessingStandardQuoteId($session->getQuoteId());
        $this->getResponse()->setBody($this->getLayout()->createBlock('centralprocessing/standard_redirect')->toHtml());
        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }

    /**
     * When a customer cancel payment from centralprocessing.
     */
    public function cancelAction()
    {
        
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getCentralprocessingStandardQuoteId(true));
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * when centralprocessing returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN.
     */
    public function  successAction()
    {
        
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getCentralprocessingStandardQuoteId(true));
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }
}
