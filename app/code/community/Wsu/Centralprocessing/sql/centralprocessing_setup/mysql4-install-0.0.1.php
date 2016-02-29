<?php
/**
 * @category   Cybersource
 * @package    Wsu_Centralprocessing
 */

$helper		= Mage::helper('centralprocessing');

$installer = $this;




$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('centralprocessing_api_debug')};
CREATE TABLE {$this->getTable('centralprocessing_api_debug')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `created_time` datetime NULL,
  `request_body` text,
  `response_body` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$quote=$installer->getTable('sales/quote_payment');
$order=$installer->getTable('sales/order_payment');

$helper->checkForColumn($installer,$quote,'response_return_code','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$quote,'response_guid','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$quote,'approval_code','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$quote,'card_type','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$quote,'masked_cc_number','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$quote,'cc_mode','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$quote,'other_multishipping_orders','VARCHAR( 255 ) NOT NULL');

$helper->checkForColumn($installer,$order,'response_return_code','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$order,'response_guid','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$order,'approval_code','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$order,'card_type','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$order,'masked_cc_number','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$order,'cc_mode','VARCHAR( 255 ) NOT NULL');
$helper->checkForColumn($installer,$order,'other_multishipping_orders','VARCHAR( 255 ) NOT NULL');


$installer->endSetup();
