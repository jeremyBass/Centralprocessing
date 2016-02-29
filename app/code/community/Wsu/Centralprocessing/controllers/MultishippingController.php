<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping checkout controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once(Mage::getModuleDir('controllers','Mage_Checkout').DS.'MultishippingController.php'); // check to remove this
class Wsu_Centralprocessing_MultishippingController extends Mage_Checkout_MultishippingController
{

    /**
     * Multishipping checkout after the overview page
     */
    public function overviewPostAction()
    {
        Mage::getSingleton("customer/session")->setIsMultishippment(true);
        $helper	= Mage::helper('centralprocessing');
        if (!$this->_validateFormKey()) {
            $this->_forward('backToAddresses');
            return;
        }

        if (!$this->_validateMinimumAmount()) {
            return;
        }

       //try back here
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $this->_getCheckoutSession()->addError($this->__('Please agree to all Terms and Conditions before placing the order.'));
                    $this->_redirect('*/*/billing');
                    return;
                }
            }

            $payment = $this->getRequest()->getPost('payment');
            $paymentInstance = $this->_getCheckout()->getQuote()->getPayment();
            if($paymentInstance->getMethod()!="centralprocessing"){
                return parent::overviewPostAction();
            }

            if (isset($payment['cc_number'])) {
                $paymentInstance->setCcNumber($payment['cc_number']);
            }
            if (isset($payment['cc_cid'])) {
                $paymentInstance->setCcCid($payment['cc_cid']);
            }


            $multishippingModel = Mage::getModel('Mage_Checkout_Model_Type_Multishipping');
            $orderIds = array();
            $this->_validate();
            $shippingAddresses = $multishippingModel->getQuote()->getAllShippingAddresses();
            $orders = array();

            if ($multishippingModel->getQuote()->hasVirtualItems()) {
                $shippingAddresses[] = $multishippingModel->getQuote()->getBillingAddress();
            }

            foreach ($shippingAddresses as $address) {
                $order = $this->_prepareOrder($address);
                $orders[] = $order;
                Mage::dispatchEvent(
                    'checkout_type_multishipping_create_orders_single',
                    array('order'=>$order, 'address'=>$address)
                );
            }

            foreach ($orders as $order) {
                $order->save();
                $orderIds[$order->getId()] = $order->getIncrementId();
                // get totals and merge for redirect
                // get an array of the items to send to redirect
            }

            Mage::getSingleton("customer/session")->setMultishippmentOrders($orderIds);

            $this->_redirect('*/*/redirect');

    }
    public function redirectAction()
    {
        $orders = Mage::getSingleton("customer/session")->getMultishippmentOrders();
        $_orders = array();
        foreach($orders as $order_id=>$inc){
            $_orders[] = Mage::getModel('sales/order')->load($inc,'increment_id');
        }
        foreach ($_orders as $order) {
            $order->addStatusToHistory(
                $order->getStatus(),
                $this->__('Customer was redirected to Cybersource.')
            );
            $order->save();
        }

        $block = $this->getLayout()->createBlock('centralprocessing/redirect')->setOrders($_orders);

        $redict_page = $block->toHtml();
        $this->getResponse()->setBody($redict_page);
    }

    /**
     * Multishipping checkout success page
     */
    public function successAction()
    {
        if (!$this->_getState()->getCompleteStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_OVERVIEW)) {
            $this->_redirect('*/*/addresses');
            return $this;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $ids = $this->_getCheckout()->getOrderIds();
        Mage::dispatchEvent('checkout_multishipping_controller_success_action', array('order_ids' => $ids));
        $this->renderLayout();
    }

    /**
     * Validate quote data
     *
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    protected function _validate()
    {
        $multishippingModel = Mage::getModel('Mage_Checkout_Model_Type_Multishipping');
        $quote = $multishippingModel->getQuote();
        if (!$quote->getIsMultiShipping()) {
            Mage::throwException(Mage::helper('checkout')->__('Invalid checkout type.'));
        }

        /** @var $paymentMethod Mage_Payment_Model_Method_Abstract */
        $paymentMethod = $quote->getPayment()->getMethodInstance();
        if (!empty($paymentMethod) && !$paymentMethod->isAvailable($quote)) {
            Mage::throwException(Mage::helper('checkout')->__('Please specify payment method.'));
        }

        $addresses = $quote->getAllShippingAddresses();
        foreach ($addresses as $address) {
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                Mage::throwException(Mage::helper('checkout')->__('Please check shipping addresses information.'));
            }
            $method= $address->getShippingMethod();
            $rate  = $address->getShippingRateByCode($method);
            if (!$method || !$rate) {
                Mage::throwException(Mage::helper('checkout')->__('Please specify shipping methods for all addresses.'));
            }
        }
        $addressValidation = $quote->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            Mage::throwException(Mage::helper('checkout')->__('Please check billing address information.'));
        }
        return $this;
    }


    /**
     * Prepare order based on quote address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Order
     * @throws  Mage_Checkout_Exception
     */
    protected function _prepareOrder(Mage_Sales_Model_Quote_Address $address)
    {
        $multishippingModel = Mage::getModel('Mage_Checkout_Model_Type_Multishipping');
        $quote = $multishippingModel->getQuote();
        $quote->unsReservedOrderId();
        $quote->reserveOrderId();
        $quote->collectTotals();

        $convertQuote = Mage::getSingleton('sales/convert_quote');
        $order = $convertQuote->addressToOrder($address);
        $order->setQuote($quote);
        $order->setBillingAddress(
            $convertQuote->addressToOrderAddress($quote->getBillingAddress())
        );

        if ($address->getAddressType() == 'billing') {
            $order->setIsVirtual(1);
        } else {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($address));
        }

        $order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));
        if (Mage::app()->getStore()->roundPrice($address->getGrandTotal()) == 0) {
            $order->getPayment()->setMethod('free');
        }

        foreach ($address->getAllItems() as $item) {
            $_quoteItem = $item->getQuoteItem();
            if (!$_quoteItem) {
                throw new Mage_Checkout_Exception(Mage::helper('checkout')->__('Item not found or already ordered'));
            }
            $item->setProductType($_quoteItem->getProductType())
                ->setProductOptions(
                    $_quoteItem->getProduct()->getTypeInstance(true)->getOrderOptions($_quoteItem->getProduct())
                );
            $orderItem = $convertQuote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        return $order;
    }
}
