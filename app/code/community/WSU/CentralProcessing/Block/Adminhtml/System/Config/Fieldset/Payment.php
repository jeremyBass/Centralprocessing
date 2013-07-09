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
 * Fieldset renderer for Centralprocessing solution
 *
 * @category    Wsu
 * @package     Wsu_Centralprocessing
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Centralprocessing_Block_Adminhtml_System_Config_Fieldset_Payment
        extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Add custom css class
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getFrontendClass($element)
    {
        return parent::_getFrontendClass($element) . ' with-button '
            . ($this->_isPaymentEnabled($element) ? ' enabled' : '');
    }

    /**
     * Check whether current payment method is enabled
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return bool
     */
    protected function _isPaymentEnabled($element)
    {
        $groupConfig = $this->getGroup($element)->asArray();
        $activityPath = isset($groupConfig['activity_path']) ? $groupConfig['activity_path'] : '';

        if (empty($activityPath)) {
            return false;
        }

        $isPaymentEnabled = (string)Mage::getSingleton('adminhtml/config_data')->getConfigDataValue($activityPath);

        return (bool)$isPaymentEnabled;
    }

    /**
     * Return header title part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $html = '<div class="config-heading" ><div class="heading"><strong>' . $element->getLegend();

        $groupConfig = $this->getGroup($element)->asArray();
        if (!empty($groupConfig['learn_more_link'])) {
            $html .= '<a class="link-more" href="' . $groupConfig['learn_more_link'] . '" target="_blank">'
                . $this->__('Learn More') . '</a>';
        }
        if (!empty($groupConfig['demo_link'])) {
            $html .= '<a class="link-demo" href="' . $groupConfig['demo_link'] . '" target="_blank">'
                . $this->__('View Demo') . '</a>';
        }
        $html .= '</strong>';

        if ($element->getComment()) {
            $html .= '<span class="heading-intro">' . $element->getComment() . '</span>';
        }
        $html .= '</div>';

        $html .= '<div class="button-container"><button type="button"'
            . ($this->_isPaymentEnabled($element) ? '' : ' disabled="disabled"') . ' class="button'
            . (empty($groupConfig['centralprocessing_ec_separate']) ? '' : ' centralprocessing-ec-separate')
            . ($this->_isPaymentEnabled($element) ? '' : ' disabled') . '" id="' . $element->getHtmlId()
            . '-head" onclick="centralprocessingToggleSolution.call(this, \'' . $element->getHtmlId() . '\', \''
            . $this->getUrl('*/*/state') . '\'); return false;"><span class="state-closed">'
            . $this->__('Configure') . '</span><span class="state-opened">'
            . $this->__('Close') . '</span></button></div></div>';

        return $html;
    }

    /**
     * Return header comment part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }

    /**
     * Get collapsed state on-load
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return bool
     */
    protected function _getCollapseState($element)
    {
        return false;
    }
}
