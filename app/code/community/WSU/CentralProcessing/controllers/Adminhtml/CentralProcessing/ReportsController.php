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
 * Centralprocessing Settlement Reports Controller
 *
 * @category    Wsu
 * @package     Wsu_Centralprocessing
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Centralprocessing_Adminhtml_Centralprocessing_ReportsController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Grid action
     */
    public function indexAction()
    {
        
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('centralprocessing/adminhtml_settlement_report'))
            ->renderLayout();
    }

    /**
     * Ajax callback for grid actions
     */
    public function gridAction()
    {
        
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('centralprocessing/adminhtml_settlement_report_grid')->toHtml()
        );
    }

    /**
     * View transaction details action
     */
    public function detailsAction()
    {
        
        $rowId = $this->getRequest()->getParam('id');
        $row = Mage::getModel('centralprocessing/report_settlement_row')->load($rowId);
        if (!$row->getId()) {
            $this->_redirect('*/*/');
            return;
        }
        Mage::register('current_transaction', $row);
        $this->_initAction()
            ->_title($this->__('View Transaction'))
            ->_addContent($this->getLayout()->createBlock('centralprocessing/adminhtml_settlement_details', 'settlementDetails'))
            ->renderLayout();
    }

    /**
     * Forced fetch reports action
     */
    public function fetchAction()
    {
        
        try {
            $reports = Mage::getModel('centralprocessing/report_settlement');
            /* @var $reports Wsu_Centralprocessing_Model_Report_Settlement */
            $credentials = $reports->getSftpCredentials();
            if (empty($credentials)) {
                Mage::throwException(Mage::helper('centralprocessing')->__('Nothing to fetch because of an empty configuration.'));
            }
            foreach ($credentials as $config) {
                try {
                    $fetched = $reports->fetchAndSave($config);
                    $this->_getSession()->addSuccess(
                        Mage::helper('centralprocessing')->__("Fetched %s report rows from '%s@%s'.", $fetched, $config['username'], $config['hostname'])
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError(
                        Mage::helper('centralprocessing')->__("Failed to fetch reports from '%s@%s'.", $config['username'], $config['hostname'])
                    );
                    Mage::logException($e);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Initialize titles, navigation
     * @return Wsu_Centralprocessing_Adminhtml_Centralprocessing_ReportsController
     */
    protected function _initAction()
    {
        
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('centralprocessing Settlement Reports'));
        $this->loadLayout()
            ->_setActiveMenu('report/sales')
            ->_addBreadcrumb(Mage::helper('centralprocessing')->__('Reports'), Mage::helper('centralprocessing')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('centralprocessing')->__('Sales'), Mage::helper('centralprocessing')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('centralprocessing')->__('centralprocessing Settlement Reports'), Mage::helper('centralprocessing')->__('centralprocessing Settlement Reports'));
        return $this;
    }

    /**
     * ACL check
     * @return bool
     */
    protected function _isAllowed()
    {
        
        switch ($this->getRequest()->getActionName()) {
            case 'index':
            case 'details':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/centralprocessing_settlement_reports/view');
                break;
            case 'fetch':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/centralprocessing_settlement_reports/fetch');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/centralprocessing_settlement_reports');
                break;
        }
    }
}
