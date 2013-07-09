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
 * Renderer for Centralprocessing banner in System Configuration
 *
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Centralprocessing_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'centralprocessing/system/config/fieldset/hint.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $elementOriginalData = $element->getOriginalData();
        if (isset($elementOriginalData['help_link'])) {
            $this->setHelpLink($elementOriginalData['help_link']);
        }
        $js = '
            centralprocessingToggleSolution = function(id, url) {
                var doScroll = false;
                Fieldset.toggleCollapse(id, url);
                if ($(this).hasClassName("open")) {
                    $$(".with-button button.button").each(function(anotherButton) {
                        if (anotherButton != this && $(anotherButton).hasClassName("open")) {
                            $(anotherButton).click();
                            doScroll = true;
                        }
                    }.bind(this));
                }
                if (doScroll) {
                    var pos = Element.cumulativeOffset($(this));
                    window.scrollTo(pos[0], pos[1] - 45);
                }
            }

            toggleCentralprocessingSolutionConfigureButton = function(button, enable) {
                var $button = $(button);
                $button.disabled = !enable;
                if ($button.hasClassName("disabled") && enable) {
                    $button.removeClassName("disabled");
                } else if (!$button.hasClassName("disabled") && !enable) {
                    $button.addClassName("disabled");
                }
            }

            // check store-view disabling Express Checkout
            document.observe("dom:loaded", function() {
                var ecButton = $$(".pp-method-express button.button")[0];
                var ecEnabler = $$(".centralprocessing-ec-enabler")[0];
                if (typeof ecButton == "undefined" || typeof ecEnabler != "undefined") {
                    return;
                }
                var $ecButton = $(ecButton);
                $$(".with-button button.button").each(function(configureButton) {
                    if (configureButton != ecButton && !configureButton.disabled
                        && !$(configureButton).hasClassName("centralprocessing-ec-separate")
                    ) {
                        toggleCentralprocessingSolutionConfigureButton(ecButton, false);
                    }
                });
            });
        ';
        return $this->toHtml() . $this->helper('adminhtml/js')->getScript($js);
    }
}
