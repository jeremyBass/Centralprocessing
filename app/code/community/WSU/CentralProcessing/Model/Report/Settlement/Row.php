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

/*
 * Model for report rows
 */
/**
 * Enter description here ...
 *
 * @method Wsu_Centralprocessing_Model_Resource_Report_Settlement_Row _getResource()
 * @method Wsu_Centralprocessing_Model_Resource_Report_Settlement_Row getResource()
 * @method int getReportId()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setReportId(int $value)
 * @method string getTransactionId()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setTransactionId(string $value)
 * @method string getInvoiceId()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setInvoiceId(string $value)
 * @method string getCentralprocessingReferenceId()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setCentralprocessingReferenceId(string $value)
 * @method string getCentralprocessingReferenceIdType()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setCentralprocessingReferenceIdType(string $value)
 * @method string getTransactionEventCode()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setTransactionEventCode(string $value)
 * @method string getTransactionInitiationDate()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setTransactionInitiationDate(string $value)
 * @method string getTransactionCompletionDate()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setTransactionCompletionDate(string $value)
 * @method string getTransactionDebitOrCredit()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setTransactionDebitOrCredit(string $value)
 * @method float getGrossTransactionAmount()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setGrossTransactionAmount(float $value)
 * @method string getGrossTransactionCurrency()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setGrossTransactionCurrency(string $value)
 * @method string getFeeDebitOrCredit()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setFeeDebitOrCredit(string $value)
 * @method float getFeeAmount()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setFeeAmount(float $value)
 * @method string getFeeCurrency()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setFeeCurrency(string $value)
 * @method string getCustomField()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setCustomField(string $value)
 * @method string getConsumerId()
 * @method Wsu_Centralprocessing_Model_Report_Settlement_Row setConsumerId(string $value)
 *
 * @category    Wsu
 * @package     Wsu_Centralprocessing
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Centralprocessing_Model_Report_Settlement_Row extends Mage_Core_Model_Abstract
{
    /**
     * Assoc array event code => label
     *
     * @var array
     */
    protected static $_eventList = array();

    /**
     * Casted amount keys registry
     *
     * @var array
     */
    protected $_castedAmounts = array();

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        
        $this->_init('centralprocessing/report_settlement_row');
    }

    /**
     * Return description of Reference ID Type
     * If no code specified, return full list of codes with their description
     *
     * @param string code
     * @return string|array
     */
    public function getReferenceType($code = null)
    {
        $types = array(
            'TXN' => Mage::helper('centralprocessing')->__('Transaction ID'),
            'ODR' => Mage::helper('centralprocessing')->__('Order ID'),
            'SUB' => Mage::helper('centralprocessing')->__('Subscription ID'),
            'PAP' => Mage::helper('centralprocessing')->__('Preapproved Payment ID')
        );
        if($code === null) {
            asort($types);
            return $types;
        }
        if (isset($types[$code])) {
            return $types[$code];
        }
        return $code;
    }

    /**
     * Get native description for transaction code
     *
     * @param string code
     * @return string
     */
    public function getTransactionEvent($code)
    {
        $this->_generateEventLabels();
        if (isset(self::$_eventList[$code])) {
            return self::$_eventList[$code];
        }
        return $code;
    }

    /**
     * Get full list of codes with their description
     *
     * @return &array
     */
    public function &getTransactionEvents()
    {
        
        $this->_generateEventLabels();
        return self::$_eventList;
    }

    /**
     * Return description of "Debit or Credit" value
     * If no code specified, return full list of codes with their description
     *
     * @param string code
     * @return string|array
     */
    public function getDebitCreditText($code = null)
    {
        $options = array(
            'CR' => Mage::helper('centralprocessing')->__('Credit'),
            'DR' => Mage::helper('centralprocessing')->__('Debit'),
        );
        if($code === null) {
            return $options;
        }
        if (isset($options[$code])) {
            return $options[$code];
        }
        return $code;
    }

    /**
     * Invoke casting some amounts
     *
     * @param mixed $key
     * @param mixed $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        $this->_castAmount('fee_amount', 'fee_debit_or_credit');
        $this->_castAmount('gross_transaction_amount', 'transaction_debit_or_credit');
        return parent::getData($key, $index);
    }

    /**
     * Cast amounts of the specified keys
     *
     * Centralprocessing settlement reports contain amounts in cents, hence the values need to be divided by 100
     * Also if the "credit" value is detected, it will be casted to negative amount
     *
     * @param string $key
     * @param string $creditKey
     */
    public function _castAmount($key, $creditKey)
    {
        if (isset($this->_castedAmounts[$key]) || !isset($this->_data[$key]) || !isset($this->_data[$creditKey])) {
            return;
        }
        if (empty($this->_data[$key])) {
            return;
        }
        $amount = $this->_data[$key] / 100;
        if ('CR' === $this->_data[$creditKey]) {
            $amount = -1 * $amount;
        }
        $this->_data[$key] = $amount;
        $this->_castedAmounts[$key] = true;
    }

    /**
     * Fill/translate and sort all event codes/labels
     */
    protected function _generateEventLabels()
    {
        
        if (!self::$_eventList) {
            self::$_eventList = array(
            'T0000' => Mage::helper('centralprocessing')->__('General: received payment of a type not belonging to the other T00xx categories'),
            'T0001' => Mage::helper('centralprocessing')->__('Mass Pay Payment'),
            'T0002' => Mage::helper('centralprocessing')->__('Subscription Payment, either payment sent or payment received'),
            'T0003' => Mage::helper('centralprocessing')->__('Preapproved Payment (BillUser API), either sent or received'),
            'T0004' => Mage::helper('centralprocessing')->__('eBay Auction Payment'),
            'T0005' => Mage::helper('centralprocessing')->__('Direct Payment API'),
            'T0006' => Mage::helper('centralprocessing')->__('Express Checkout APIs'),
            'T0007' => Mage::helper('centralprocessing')->__('Website Payments Standard Payment'),
            'T0008' => Mage::helper('centralprocessing')->__('Postage Payment to either USPS or UPS'),
            'T0009' => Mage::helper('centralprocessing')->__('Gift Certificate Payment: purchase of Gift Certificate'),
            'T0010' => Mage::helper('centralprocessing')->__('Auction Payment other than through eBay'),
            'T0011' => Mage::helper('centralprocessing')->__('Mobile Payment (made via a mobile phone)'),
            'T0012' => Mage::helper('centralprocessing')->__('Virtual Terminal Payment'),
            'T0100' => Mage::helper('centralprocessing')->__('General: non-payment fee of a type not belonging to the other T01xx categories'),
            'T0101' => Mage::helper('centralprocessing')->__('Fee: Web Site Payments Pro Account Monthly'),
            'T0102' => Mage::helper('centralprocessing')->__('Fee: Foreign ACH Withdrawal'),
            'T0103' => Mage::helper('centralprocessing')->__('Fee: WorldLink Check Withdrawal'),
            'T0104' => Mage::helper('centralprocessing')->__('Fee: Mass Pay Request'),
            'T0200' => Mage::helper('centralprocessing')->__('General Currency Conversion'),
            'T0201' => Mage::helper('centralprocessing')->__('User-initiated Currency Conversion'),
            'T0202' => Mage::helper('centralprocessing')->__('Currency Conversion required to cover negative balance'),
            'T0300' => Mage::helper('centralprocessing')->__('General Funding of Centralprocessing Account '),
            'T0301' => Mage::helper('centralprocessing')->__('Centralprocessing Balance Manager function of Centralprocessing account'),
            'T0302' => Mage::helper('centralprocessing')->__('ACH Funding for Funds Recovery from Account Balance'),
            'T0303' => Mage::helper('centralprocessing')->__('EFT Funding (German banking)'),
            'T0400' => Mage::helper('centralprocessing')->__('General Withdrawal from Centralprocessing Account'),
            'T0401' => Mage::helper('centralprocessing')->__('AutoSweep'),
            'T0500' => Mage::helper('centralprocessing')->__('General: Use of Centralprocessing account for purchasing as well as receiving payments'),
            'T0501' => Mage::helper('centralprocessing')->__('Virtual Centralprocessing Debit Card Transaction'),
            'T0502' => Mage::helper('centralprocessing')->__('Centralprocessing Debit Card Withdrawal from ATM'),
            'T0503' => Mage::helper('centralprocessing')->__('Hidden Virtual Centralprocessing Debit Card Transaction'),
            'T0504' => Mage::helper('centralprocessing')->__('Centralprocessing Debit Card Cash Advance'),
            'T0600' => Mage::helper('centralprocessing')->__('General: Withdrawal from Centralprocessing Account'),
            'T0700' => Mage::helper('centralprocessing')->__('General (Purchase with a credit card)'),
            'T0701' => Mage::helper('centralprocessing')->__('Negative Balance'),
            'T0800' => Mage::helper('centralprocessing')->__('General: bonus of a type not belonging to the other T08xx categories'),
            'T0801' => Mage::helper('centralprocessing')->__('Debit Card Cash Back'),
            'T0802' => Mage::helper('centralprocessing')->__('Merchant Referral Bonus'),
            'T0803' => Mage::helper('centralprocessing')->__('Balance Manager Account Bonus'),
            'T0804' => Mage::helper('centralprocessing')->__('Centralprocessing Buyer Warranty Bonus'),
            'T0805' => Mage::helper('centralprocessing')->__('Centralprocessing Protection Bonus'),
            'T0806' => Mage::helper('centralprocessing')->__('Bonus for first ACH Use'),
            'T0900' => Mage::helper('centralprocessing')->__('General Redemption'),
            'T0901' => Mage::helper('centralprocessing')->__('Gift Certificate Redemption'),
            'T0902' => Mage::helper('centralprocessing')->__('Points Incentive Redemption'),
            'T0903' => Mage::helper('centralprocessing')->__('Coupon Redemption'),
            'T0904' => Mage::helper('centralprocessing')->__('Reward Voucher Redemption'),
            'T1000' => Mage::helper('centralprocessing')->__('General. Product no longer supported'),
            'T1100' => Mage::helper('centralprocessing')->__('General: reversal of a type not belonging to the other T11xx categories'),
            'T1101' => Mage::helper('centralprocessing')->__('ACH Withdrawal'),
            'T1102' => Mage::helper('centralprocessing')->__('Debit Card Transaction'),
            'T1103' => Mage::helper('centralprocessing')->__('Reversal of Points Usage'),
            'T1104' => Mage::helper('centralprocessing')->__('ACH Deposit (Reversal)'),
            'T1105' => Mage::helper('centralprocessing')->__('Reversal of General Account Hold'),
            'T1106' => Mage::helper('centralprocessing')->__('Account-to-Account Payment, initiated by Centralprocessing'),
            'T1107' => Mage::helper('centralprocessing')->__('Payment Refund initiated by merchant'),
            'T1108' => Mage::helper('centralprocessing')->__('Fee Reversal'),
            'T1110' => Mage::helper('centralprocessing')->__('Hold for Dispute Investigation'),
            'T1111' => Mage::helper('centralprocessing')->__('Reversal of hold for Dispute Investigation'),
            'T1200' => Mage::helper('centralprocessing')->__('General: adjustment of a type not belonging to the other T12xx categories'),
            'T1201' => Mage::helper('centralprocessing')->__('Chargeback'),
            'T1202' => Mage::helper('centralprocessing')->__('Reversal'),
            'T1203' => Mage::helper('centralprocessing')->__('Charge-off'),
            'T1204' => Mage::helper('centralprocessing')->__('Incentive'),
            'T1205' => Mage::helper('centralprocessing')->__('Reimbursement of Chargeback'),
            'T1300' => Mage::helper('centralprocessing')->__('General (Authorization)'),
            'T1301' => Mage::helper('centralprocessing')->__('Reauthorization'),
            'T1302' => Mage::helper('centralprocessing')->__('Void'),
            'T1400' => Mage::helper('centralprocessing')->__('General (Dividend)'),
            'T1500' => Mage::helper('centralprocessing')->__('General: temporary hold of a type not belonging to the other T15xx categories'),
            'T1501' => Mage::helper('centralprocessing')->__('Open Authorization'),
            'T1502' => Mage::helper('centralprocessing')->__('ACH Deposit (Hold for Dispute or Other Investigation)'),
            'T1503' => Mage::helper('centralprocessing')->__('Available Balance'),
            'T1600' => Mage::helper('centralprocessing')->__('Funding'),
            'T1700' => Mage::helper('centralprocessing')->__('General: Withdrawal to Non-Bank Entity'),
            'T1701' => Mage::helper('centralprocessing')->__('WorldLink Withdrawal'),
            'T1800' => Mage::helper('centralprocessing')->__('Buyer Credit Payment'),
            'T1900' => Mage::helper('centralprocessing')->__('General Adjustment without businessrelated event'),
            'T2000' => Mage::helper('centralprocessing')->__('General (Funds Transfer from Centralprocessing Account to Another)'),
            'T2001' => Mage::helper('centralprocessing')->__('Settlement Consolidation'),
            'T9900' => Mage::helper('centralprocessing')->__('General: event not yet categorized'),
            );
            asort(self::$_eventList);
        }
    }
}
