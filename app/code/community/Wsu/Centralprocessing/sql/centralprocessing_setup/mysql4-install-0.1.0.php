<?php
/**
 * @category   Cybersource
 * @package    Wsu_Centralprocessing
 */

$helper		= Mage::helper('centralprocessing');

$installer = $this;

$installer->startSetup();

$quote=$installer->getTable('sales/quote_payment');
$order=$installer->getTable('sales/order_payment');
// adding column to track the auuth type used for that order
$helper->checkForColumn($installer,$quote,'auth_type','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$order,'auth_type','VARCHAR( 255 ) NOT NULL');

$installer->endSetup();
