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
 * Renderer for service JavaScript code that disables corresponding centralprocessing methods on page load
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Centralprocessing_Block_Adminhtml_System_Config_Fieldset_Store
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'centralprocessing/system/config/fieldset/store.phtml';

    /**
     * Render service JavaScript code
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    /**
     * Returns list of disabled (in the Default or the Website Scope) centralprocessing methods
     *
     * @return array
     */
    protected function getCentralprocessingDisabledMethods()
    {
        
        // Assoc array that contains info about centralprocessing methods (their IDs and corresponding Config Paths)
        $methods = array(
            'express'   => 'payment/centralprocessing_express/active',
            'wps'       => 'payment/centralprocessing_standard/active',
            'wpp'       => 'payment/centralprocessing_direct/active',
            'wpppe'     => 'payment/centralprocessinguk_direct/active',
            'verisign'  => 'payment/verisign/active',
            'expresspe' => 'payment/centralprocessinguk_express/active'
        );
        // Retrieve a code of the current website
        $website = $this->getRequest()->getParam('website');

        $configRoot = Mage::getConfig()->getNode(null, 'website', $website);

        $disabledMethods = array();
        foreach ($methods as $methodId => $methodPath) {
            $isEnabled = (int) $configRoot->descend($methodPath);
            if ($isEnabled === 0) {
                $disabledMethods[$methodId] = $isEnabled;
            }
        }

        return $disabledMethods;
    }
}
