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
 * Centralprocessing module observer
 *
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */

class Wsu_Centralprocessing_Model_Observer
{
    /**
     * Goes to reports.centralprocessing.com and fetches Settlement reports.
     * @return Wsu_Centralprocessing_Model_Observer
     */
    public function fetchReports()
    {
        
        try {
            $reports = Mage::getModel('centralprocessing/report_settlement');
            /* @var $reports Wsu_Centralprocessing_Model_Report_Settlement */
            $credentials = $reports->getSftpCredentials(true);
            foreach ($credentials as $config) {
                try {
                    $reports->fetchAndSave($config);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Set the url to be based from it's store not based on the location it's currently showen
     *
     * @return String url
     */
    public function getProductUrl($product, $additional = array()) {

        if ($this->hasProductUrl($product)) {
			$pstore_id = array_shift(array_values($_product->getStoreIds()));
			if(Mage::app()->getStore()->getStoreId() == $pstore_id){
				$purl = $product->getUrlModel()->getUrl($product, $additional);//$this->getProductUrl();
			}else{
				$base = Mage::app()->getStore($pstore_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
				$purl = $base.$product->getUrlPath();
			}
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $purl;
        }
        return '#';
    }


    /**
     * Clean unfinished transaction
     *
     * @deprecated since 1.6.2.0
     * @return Wsu_Centralprocessing_Model_Observer
     */
    public function cleanTransactions()
    {
        
        return $this;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param Varien_Event_Observer $observer
     * @return Wsu_Centralprocessing_Model_Observer
     */
    public function saveOrderAfterSubmit(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getData('order');
        Mage::register('hss_order', $order, true);

        return $this;
    }

    /**
     * Set data for response of frontend saveOrder action
     *
     * @param Varien_Event_Observer $observer
     * @return Wsu_Centralprocessing_Model_Observer
     */
    public function setResponseAfterSaveOrder(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::registry('hss_order');

        if ($order && $order->getId()) {
            $payment = $order->getPayment();
            if ($payment && in_array($payment->getMethod(), Mage::helper('centralprocessing/hss')->getHssMethods())) {
                /* @var $controller Mage_Core_Controller_Varien_Action */
                $controller = $observer->getEvent()->getData('controller_action');
                $result = Mage::helper('core')->jsonDecode(
                    $controller->getResponse()->getBody('default'),
                    Zend_Json::TYPE_ARRAY
                );

                if (empty($result['error'])) {
                    $controller->loadLayout('checkout_onepage_review');
                    $html = $controller->getLayout()->getBlock('centralprocessing.iframe')->toHtml();
                    $result['update_section'] = array(
                        'name' => 'centralprocessingiframe',
                        'html' => $html
                    );
                    $result['redirect'] = false;
                    $result['success'] = false;
                    $controller->getResponse()->clearHeader('Location');
                    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                }
            }
        }

        return $this;
    }
	
	
	
	
	
	
	
public function salesOrderSetGuid($observer)
{
    $orderItem = $observer->getOrder();
    $orderItem->setCustomAttribute($observer->getGuid());
}

    /**
     * Load country dependent Centralprocessing solutions system configuration
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function loadCountryDependentSolutionsConfig(Varien_Event_Observer $observer)
    {
        $requestParam = Wsu_Centralprocessing_Block_Adminhtml_System_Config_Field_Country::REQUEST_PARAM_COUNTRY;
        $countryCode  = Mage::app()->getRequest()->getParam($requestParam);
        if (is_null($countryCode) || preg_match('/^[a-zA-Z]{2}$/', $countryCode) == 0) {
            $countryCode = (string)Mage::getSingleton('adminhtml/config_data')
                ->getConfigDataValue('centralprocessing/general/merchant_country');
        }
        if (empty($countryCode)) {
            $countryCode = Mage::helper('core')->getDefaultCountry();
        }

        $paymentGroups   = $observer->getEvent()->getConfig()->getNode('sections/payment/groups');
        $paymentsConfigs = $paymentGroups->xpath('centralprocessing_payments/*/backend_config/' . $countryCode);
        if ($paymentsConfigs) {
            foreach ($paymentsConfigs as $config) {
                $parent = $config->getParent()->getParent();
                $parent->extend($config, true);
            }
        }

        $payments = $paymentGroups->xpath('centralprocessing_payments/*');
        foreach ($payments as $payment) {
            if ((int)$payment->include) {
                $fields = $paymentGroups->xpath((string)$payment->group . '/fields');
                if (isset($fields[0])) {
                    $fields[0]->appendChild($payment, true);
                }
            }
        }
    }
	

	
	
	
}
