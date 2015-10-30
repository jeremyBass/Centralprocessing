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
require_once(Mage::getModuleDir('controllers','Mage_Checkout').DS.'MultishippingController.php');
class Wsu_Centralprocessing_MultishippingController extends Mage_Checkout_MultishippingController{//Mage_Checkout_Controller_Action {


    /**
     * Multishipping checkout after the overview page
     */
    public function overviewPostAction()
    {
		    
        if (!$this->_validateFormKey()) {
            $this->_forward('backToAddresses');
            return;
        }

        if (!$this->_validateMinimumAmount()) {
            return;
        }

        try {
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
			var_dump($paymentInstance);
			die("here");

			$orderIds = array();
			$this->_validate();
			$shippingAddresses = $this->getQuote()->getAllShippingAddresses();
			$orders = array();
	
			if ($this->getQuote()->hasVirtualItems()) {
				$shippingAddresses[] = $this->getQuote()->getBillingAddress();
			}
	
			try {
				foreach ($shippingAddresses as $address) {
					$order = $this->_prepareOrder($address);
	
					$orders[] = $order;
					Mage::dispatchEvent(
						'checkout_type_multishipping_create_orders_single',
						array('order'=>$order, 'address'=>$address)
					);
				}
	
				foreach ($orders as $order) {
					$order->place();
					$order->save();
					if ($order->getCanSendNewEmailFlag()){
						$order->queueNewOrderEmail();
					}
					$orderIds[$order->getId()] = $order->getIncrementId();
				}
	
				Mage::getSingleton('core/session')->setOrderIds($orderIds);
				Mage::getSingleton('checkout/session')->setLastQuoteId($this->getQuote()->getId());
	
				$this->getQuote()
					->setIsActive(false)
					->save();
	
				Mage::dispatchEvent('checkout_submit_all_after', array('orders' => $orders, 'quote' => $this->getQuote()));
	
				return $this;
			} catch (Exception $e) {
				Mage::dispatchEvent('checkout_multishipping_refund_all', array('orders' => $orders));
				throw $e;
			}

			
            $this->_getCheckout()->createOrders();
            $this->_getState()->setActiveStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_SUCCESS
            );
            $this->_getState()->setCompleteStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_OVERVIEW
            );
            $this->_getCheckout()->getCheckoutSession()->clear();
            $this->_getCheckout()->getCheckoutSession()->setDisplaySuccess(true);
            $this->_redirect('*/*/success');
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if ( !empty($message) ) {
                $this->_getCheckoutSession()->addError($message);
            }
            $this->_redirect('*/*/billing');
        } catch (Mage_Checkout_Exception $e) {
            Mage::helper('checkout')
                ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckout()->getCheckoutSession()->clear();
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/cart');
        }
        catch (Mage_Core_Exception $e) {
            Mage::helper('checkout')
                ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/billing');
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')
                ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckoutSession()->addError($this->__('Order place error.'));
            $this->_redirect('*/*/billing');
        }
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

}
