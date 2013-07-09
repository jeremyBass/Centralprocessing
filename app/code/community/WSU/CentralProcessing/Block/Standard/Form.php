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
 * Centralprocessing Standard payment "form"
 */
class Wsu_Centralprocessing_Block_Standard_Form extends Mage_Payment_Block_Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = Wsu_Centralprocessing_Model_Config::METHOD_WPS;

    /**
     * Config model instance
     *
     * @var Wsu_Centralprocessing_Model_Config
     */
    protected $_config;

    /**
     * Set template and redirect message
     */
    protected function _construct()
    {
        
        $this->_config = Mage::getModel('centralprocessing/config')->setMethod($this->getMethodCode());
        $locale = Mage::app()->getLocale();
        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setTemplate('centralprocessing/payment/mark.phtml')
            ->setPaymentAcceptanceMarkHref($this->_config->getPaymentMarkWhatIsCentralprocessingUrl($locale))
            ->setPaymentAcceptanceMarkSrc($this->_config->getPaymentMarkImageUrl($locale->getLocaleCode()))
        ; // known issue: code above will render only static mark image
        $this->setTemplate('centralprocessing/payment/redirect.phtml')
            ->setRedirectMessage(
                Mage::helper('centralprocessing')->__('You will be redirected to the central processing website when you place an order.')
            )
            ->setMethodTitle('') // Output Centralprocessing mark, omit title
            ->setMethodLabelAfterHtml($mark->toHtml())
        ;
        return parent::_construct();
    }

    /**
     * Payment method code getter
     * @return string
     */
    public function getMethodCode()
    {
        
        return $this->_methodCode;
    }
}
