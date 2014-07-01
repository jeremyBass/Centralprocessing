<?php
/**
 * @category   Cybersource
 * @package    Wsu_CentralProcessing
 */
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('centralprocessing_api_debug')};
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
$installer->run("
ALTER TABLE `{$quote}` ADD `response_return_code` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$quote}` ADD `response_guid` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$quote}` ADD `approval_code` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$quote}` ADD `card_type` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$quote}` ADD `masked_cc_number` VARCHAR( 255 ) NOT NULL ;

ALTER TABLE `{$order}` ADD `response_return_code` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$order}` ADD `response_guid` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$order}` ADD `approval_code` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$order}` ADD `card_type` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$order}` ADD `masked_cc_number` VARCHAR( 255 ) NOT NULL ;
");



$installer->endSetup();