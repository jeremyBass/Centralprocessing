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
 * Centralprocessing online logo with additional options
 */
class Wsu_Centralprocessing_Block_Logo extends Mage_Core_Block_Template
{
    /**
     * Return URL for Centralprocessing Landing page
     *
     * @return string
     */
    public function getAboutCentralprocessingPageUrl()
    {
        
        return $this->_getConfig()->getPaymentMarkWhatIsCentralprocessingUrl(Mage::app()->getLocale());
    }

    /**
     * Getter for centralprocessing config
     *
     * @return Wsu_Centralprocessing_Model_Config
     */
    protected function _getConfig()
    {
        
        return Mage::getSingleton('centralprocessing/config');
    }

    /**
     * Disable block output if logo turned off
     *
     * @return string
     */
    protected function _toHtml()
    {
        
        $type = $this->getLogoType(); // assigned in layout etc.
        $logoUrl = $this->_getConfig()->getAdditionalOptionsLogoUrl(Mage::app()->getLocale()->getLocaleCode(), $type);
        if (!$logoUrl) {
            return '';
        }
        $this->setLogoImageUrl($logoUrl);
        return parent::_toHtml();
    }
}
