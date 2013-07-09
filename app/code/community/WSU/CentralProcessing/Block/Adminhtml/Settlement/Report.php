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
 * Adminhtml centralprocessing settlement reports grid block
 *
 * @category    Wsu
 * @package     Wsu_Centralprocessing
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Centralprocessing_Block_Adminhtml_Settlement_Report extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Prepare grid container, add additional buttons
     */
    public function __construct()
    {
        
        $this->_blockGroup = 'centralprocessing';
        $this->_controller = 'adminhtml_settlement_report';
        $this->_headerText = Mage::helper('centralprocessing')->__('Centralprocessing Settlement Reports');
        parent::__construct();
        $this->_removeButton('add');
        $message = Mage::helper('centralprocessing')->__('Connecting to Centralprocessing SFTP server to fetch new reports. Are you sure you want to proceed?');
        $this->_addButton('fetch', array(
            'label'   => Mage::helper('centralprocessing')->__('Fetch Updates'),
            'onclick' => "confirmSetLocation('{$message}', '{$this->getUrl('*/*/fetch')}')",
            'class'   => 'task'
        ));
    }
}
