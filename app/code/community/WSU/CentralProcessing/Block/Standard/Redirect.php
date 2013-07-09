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
class Wsu_Centralprocessing_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        
        $standard = Mage::getModel('centralprocessing/standard');

        $form = new Varien_Data_Form();
        $form->setAction($standard->getConfig()->getCentralprocessingUrl())
            ->setId('centralprocessing_standard_checkout')
            ->setName('centralprocessing_standard_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($standard->getStandardCheckoutFormFields() as $field=>$value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }
        $idSuffix = Mage::helper('core')->uniqHash();
        $submitButton = new Varien_Data_Form_Element_Submit(array(
            'value'    => $this->__('Click here if you are not redirected within 10 seconds...'),
        ));
        $id = "submit_to_centralprocessing_button_{$idSuffix}";
        $submitButton->setId($id);
        $form->addElement($submitButton);
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to the Wsu payment website in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("centralprocessing_standard_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}
