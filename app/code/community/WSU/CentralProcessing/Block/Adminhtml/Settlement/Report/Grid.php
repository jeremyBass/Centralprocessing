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
class Wsu_Centralprocessing_Block_Adminhtml_Settlement_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Retain filter parameters in session
     *
     * @var bool
     */
    protected $_saveParametersInSession = true;

    /**
     * Constructor
     * Set main configuration of grid
     */
    public function __construct()
    {
        
        parent::__construct();
        $this->setId('settlementGrid');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection for grid
     * @return Wsu_Centralprocessing_Block_Adminhtml_Settlement_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('centralprocessing/report_settlement_row_collection');
        $this->setCollection($collection);
		var_dump($collection);
		print(__FILE__);
        if (!$this->getParam($this->getVarNameSort()) && $collection) {
            $collection->setOrder('row_id', 'desc');
        }
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     * @return Wsu_Centralprocessing_Block_Adminhtml_Settlement_Grid
     */
    protected function _prepareColumns()
    {
        
        $settlement = Mage::getSingleton('centralprocessing/report_settlement');
        $this->addColumn('report_date', array(
            'header'    => $settlement->getFieldLabel('report_date'),
            'index'     => 'report_date',
            'type'     => 'date'
        ));
        $this->addColumn('account_id', array(
            'header'    => $settlement->getFieldLabel('account_id'),
            'index'     => 'account_id'
        ));
        $this->addColumn('transaction_id', array(
            'header'    => $settlement->getFieldLabel('transaction_id'),
            'index'     => 'transaction_id'
        ));
        $this->addColumn('invoice_id', array(
            'header'    => $settlement->getFieldLabel('invoice_id'),
            'index'     => 'invoice_id'
        ));
        $this->addColumn('centralprocessing_reference_id', array(
            'header'    => $settlement->getFieldLabel('centralprocessing_reference_id'),
            'index'     => 'centralprocessing_reference_id'
        ));
        $this->addColumn('transaction_event_code', array(
            'header'    => $settlement->getFieldLabel('transaction_event'),
            'index'     => 'transaction_event_code',
            'type'      => 'options',
            'options'   => Mage::getModel('centralprocessing/report_settlement_row')->getTransactionEvents()
        ));
        $this->addColumn('transaction_initiation_date', array(
            'header'    => $settlement->getFieldLabel('transaction_initiation_date'),
            'index'     => 'transaction_initiation_date',
            'type'      => 'datetime'
        ));
        $this->addColumn('transaction_completion_date', array(
            'header'    => $settlement->getFieldLabel('transaction_completion_date'),
            'index'     => 'transaction_completion_date',
            'type'      => 'datetime'
        ));
        $this->addColumn('gross_transaction_amount', array(
            'header'    => $settlement->getFieldLabel('gross_transaction_amount'),
            'index'     => 'gross_transaction_amount',
            'type'      => 'currency',
            'currency'  => 'gross_transaction_currency',
        ));
        $this->addColumn('fee_amount', array(
            'header'    => $settlement->getFieldLabel('fee_amount'),
            'index'     => 'fee_amount',
            'type'      => 'currency',
            'currency'  => 'gross_transaction_currency',
        ));
        return parent::_prepareColumns();
    }

    /**
     * Return grid URL
     * @return string
     */
    public function getGridUrl()
    {
        
         return $this->getUrl('*/*/grid');
    }

    /**
     * Return item view URL
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/details', array('id' => $item->getId()));
    }
}
