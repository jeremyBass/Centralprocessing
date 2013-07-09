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
 * Express Checkout Controller
 */
class Wsu_Centralprocessing_ExpressController extends Mage_Core_Controller_Front_Action
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = 'centralprocessing/config';

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = Wsu_Centralprocessing_Model_Config::METHOD_WPP_EXPRESS;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = 'centralprocessing/express_checkout';
	
	
    /**
     * @var Wsu_Centralprocessing_Model_Express_Checkout
     */
    protected $_checkout = null;

    /**
     * @var Wsu_Centralprocessing_Model_Config
     */
    protected $_config = null;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = false;

    /**
     * Instantiate config
     */
    protected function _construct()
    {
        
        parent::_construct();
        $this->_config = Mage::getModel($this->_configType, array($this->_configMethod));
    }

    /**
     * Start Express Checkout by requesting initial guid and dispatching customer to Centralprocessing
     */
    public function startAction()
    {
        try {
            $this->_initCheckout();

            if ($this->_getQuote()->getIsMultiShipping()) {
                $this->_getQuote()->setIsMultiShipping(false);
                $this->_getQuote()->removeAllAddresses();
            }

            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer && $customer->getId()) {
                $this->_checkout->setCustomerWithAddressChange(
                    $customer, $this->_getQuote()->getBillingAddress(), $this->_getQuote()->getShippingAddress()
                );
            }

            // billing agreement
            $isBARequested = (bool)$this->getRequest()
                ->getParam(Wsu_Centralprocessing_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
            if ($customer && $customer->getId()) {
                $this->_checkout->setIsBillingAgreementRequested($isBARequested);
            }

            // giropay
            $this->_checkout->prepareGiropayUrls(
                Mage::getUrl('checkout/onepage/success'),
                Mage::getUrl('centralprocessing/express/cancel'),
                Mage::getUrl('checkout/onepage/success')
            );

            $guid = $this->_checkout->start(Mage::getUrl('*/*/return'), Mage::getUrl('*/*/cancel'));
            if ($guid && $url = $this->_checkout->getRedirectUrl()) {
                $this->_initGuid($guid);
                $this->getResponse()->setRedirect($url);
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->__('Unable to start Express Checkout.'));
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * Return shipping options items for shipping address from request
     */
    public function shippingOptionsCallbackAction()
    {
        
        try {
            $quoteId = $this->getRequest()->getParam('quote_id');
            $this->_quote = Mage::getModel('sales/quote')->load($quoteId);
            $this->_initCheckout();
            $response = $this->_checkout->getShippingOptionsCallbackResponse($this->getRequest()->getParams());
            $this->getResponse()->setBody($response);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Cancel Express Checkout
     */
    public function cancelAction()
    {
        
        try {
            $this->_initGuid(false);
            // TODO verify if this logic of order cancelation is deprecated
            // if there is an order - cancel it
            $orderId = $this->_getCheckoutSession()->getLastOrderId();
            $order = ($orderId) ? Mage::getModel('sales/order')->load($orderId) : false;
            if ($order && $order->getId() && $order->getQuoteId() == $this->_getCheckoutSession()->getQuoteId()) {
                $order->cancel()->save();
                $this->_getCheckoutSession()
                    ->unsLastQuoteId()
                    ->unsLastSuccessQuoteId()
                    ->unsLastOrderId()
                    ->unsLastRealOrderId()
                    ->addSuccess($this->__('Express Checkout and Order have been canceled.'))
                ;
            } else {
                $this->_getCheckoutSession()->addSuccess($this->__('Express Checkout has been canceled.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->__('Unable to cancel Express Checkout.'));
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * Return from Centralprocessing and dispatch customer to order review page
     */
    public function returnAction()
    {
        try {
			
			$guid = isset($_GET['GUID'])?$_GET['GUID']:$this->_initGuid();// fix
			print($guid);
			//exit();die();
			
            $this->_initCheckout();
            $this->_checkout->returnFromCentralprocessing($guid);
            $this->_redirect('*/*/review');
            return;
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($this->__('Unable to process Express Checkout approval.'));
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Review order after returning from Centralprocessing
     */
    public function reviewAction()
    {
        
        try {
            $this->_initCheckout();
            $this->_checkout->prepareOrderReview($this->_initGuid());
            $this->loadLayout();
            $this->_initLayoutMessages('centralprocessing/session');
            $reviewBlock = $this->getLayout()->getBlock('centralprocessing.express.review');
			
			$quote=$this->_getQuote();
			var_dump($this);die();
            $reviewBlock->setQuote($quote);
            $reviewBlock->getChild('details')->setQuote($this->_getQuote());
            if ($reviewBlock->getChild('shipping_method')) {
                $reviewBlock->getChild('shipping_method')->setQuote($this->_getQuote());
            }
            $this->renderLayout();
            return;
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError(
                $this->__('Unable to initialize Express Checkout review.')
            );
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Dispatch customer back to Centralprocessing for editing payment information
     */
    public function editAction()
    {
        
        try {
            $this->getResponse()->setRedirect($this->_config->getExpressCheckoutEditUrl($this->_initGuid()));
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/review');
        }
    }

    /**
     * Update shipping method (combined action for ajax and regular request)
     */
    public function saveShippingMethodAction()
    {
        
        try {
            $isAjax = $this->getRequest()->getParam('isAjax');
            $this->_initCheckout();
            $this->_checkout->updateShippingMethod($this->getRequest()->getParam('shipping_method'));
            if ($isAjax) {
                $this->loadLayout('centralprocessing_express_review_details');
                $this->getResponse()->setBody($this->getLayout()->getBlock('root')
                    ->setQuote($this->_getQuote())
                    ->toHtml());
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to update shipping method.'));
            Mage::logException($e);
        }
        if ($isAjax) {
            $this->getResponse()->setBody('<script type="text/javascript">window.location.href = '
                . Mage::getUrl('*/*/review') . ';</script>');
        } else {
            $this->_redirect('*/*/review');
        }
    }

    /**
     * Update Order (combined action for ajax and regular request)
     */
    public function updateShippingMethodsAction()
    {
        
        try {
            $this->_initCheckout();
            $this->_checkout->prepareOrderReview($this->_initGuid());
            $this->loadLayout('centralprocessing_express_review');

            $this->getResponse()->setBody($this->getLayout()->getBlock('express.review.shipping.method')
                ->setQuote($this->_getQuote())
                ->toHtml());
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to update Order data.'));
            Mage::logException($e);
        }
        $this->getResponse()->setBody('<script type="text/javascript">window.location.href = '
            . Mage::getUrl('*/*/review') . ';</script>');
    }

    /**
     * Update Order (combined action for ajax and regular request)
     */
    public function updateOrderAction()
    {
        
        try {
            $isAjax = $this->getRequest()->getParam('isAjax');
            $this->_initCheckout();
            $this->_checkout->updateOrder($this->getRequest()->getParams());
            if ($isAjax) {
                $this->loadLayout('centralprocessing_express_review_details');
                $this->getResponse()->setBody($this->getLayout()->getBlock('root')
                    ->setQuote($this->_getQuote())
                    ->toHtml());
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to update Order data.'));
            Mage::logException($e);
        }
        if ($isAjax) {
            $this->getResponse()->setBody('<script type="text/javascript">window.location.href = '
                . Mage::getUrl('*/*/review') . ';</script>');
        } else {
            $this->_redirect('*/*/review');
        }
    }

    /**
     * Submit the order
     */
    public function placeOrderAction()
    {
        
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if (array_diff($requiredAgreements, $postedAgreements)) {
                    Mage::throwException(Mage::helper('centralprocessing')->__('Please agree to all the terms and conditions before placing the order.'));
                }
            }

            $this->_initCheckout();
            $this->_checkout->place($this->_initGuid());

            // prepare session to success or cancellation page
            $session = $this->_getCheckoutSession();
            $session->clearHelperData();

            // "last successful quote"
            $quoteId = $this->_getQuote()->getId();
            $session->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->_checkout->getOrder();
            if ($order) {
                $session->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
                // as well a billing agreement can be created
                $agreement = $this->_checkout->getBillingAgreement();
                if ($agreement) {
                    $session->setLastBillingAgreementId($agreement->getId());
                }
            }

            // recurring profiles may be created along with the order or without it
            $profiles = $this->_checkout->getRecurringPaymentProfiles();
            if ($profiles) {
                $ids = array();
                foreach($profiles as $profile) {
                    $ids[] = $profile->getId();
                }
                $session->setLastRecurringProfileIds($ids);
            }

            // redirect if Centralprocessing specified some URL (for example, to Giropay bank)
            $url = $this->_checkout->getRedirectUrl();
            if ($url) {
                $this->getResponse()->setRedirect($url);
                return;
            }
            $this->_initGuid(false); // no need in guid anymore
            $this->_redirect('checkout/onepage/success');
            return;
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to place the order.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*/review');
    }

    /**
     * Instantiate quote and checkout
     * @throws Mage_Core_Exception
     */
    private function _initCheckout()
    {
        
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            Mage::throwException(Mage::helper('centralprocessing')->__('Unable to initialize Express Checkout.'));
        }
        $this->_checkout = Mage::getSingleton($this->_checkoutType, array(
            'config' => $this->_config,
            'quote'  => $quote,
        ));
    }

    /**
     * Search for proper checkout guid in request or session or (un)set specified one
     * Combined getter/setter
     *
     * @param string $setGuid
     * @return Wsu_Centralprocessing_ExpressController|string
     */
    protected function _initGuid($setGuid = null)
    {
        if (null !== $setGuid) {
            if (false === $setGuid) {
                // security measure for avoid unsetting guid twice
                if (!$this->_getSession()->getExpressCheckoutGuid()) {
                    Mage::throwException($this->__('Central Processing Express Checkout Guid does not exist.'));
                }
                $this->_getSession()->unsExpressCheckoutGuid();
            } else {
                $this->_getSession()->setExpressCheckoutGuid($setGuid);
            }
            return $this;
        }
        if ($setGuid = $this->getRequest()->getParam('GUID')) {
            if ($setGuid !== $this->_getSession()->getExpressCheckoutGuid()) {
                Mage::throwException($this->__('Wrong Central Processing Express Checkout Guid specified.'));
            }
        } else {
            $setGuid = $this->_getSession()->getExpressCheckoutGuid();
        }
        return $setGuid;
    }

    /**
     * Centralprocessing session instance getter
     *
     * @return Wsu_Centralprocessing_Model_Session
     */
    private function _getSession()
    {
        
        return Mage::getSingleton('centralprocessing/session');
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    private function _getCheckoutSession()
    {
        
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sale_Model_Quote
     */
    private function _getQuote()
    {
        
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Redirect to login page
     *
     */
    public function redirectLogin()
    {
        
        $this->setFlag('', 'no-dispatch', true);
        $this->getResponse()->setRedirect(
            Mage::helper('core/url')->addRequestParam(
                Mage::helper('customer')->getLoginUrl(),
                array('context' => 'checkout')
            )
        );
    }
	
	
	
}
