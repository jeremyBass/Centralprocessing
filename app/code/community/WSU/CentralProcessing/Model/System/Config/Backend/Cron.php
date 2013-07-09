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

class Wsu_Centralprocessing_Model_System_Config_Backend_Cron extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH = 'crontab/jobs/centralprocessing_fetch_settlement_reports/schedule/cron_expr';
    const CRON_MODEL_PATH_INTERVAL = 'centralprocessing/fetch_reports/schedule';

    /**
     * Cron settings after save
     * @return void
     */
    protected function _afterSave()
    {
        
        $cronExprString = '';
        $time = explode(',', Mage::getModel('core/config_data')->load('centralprocessing/fetch_reports/time', 'path')->getValue());
        if (Mage::getModel('core/config_data')->load('centralprocessing/fetch_reports/active', 'path')->getValue()) {
            $interval = Mage::getModel('core/config_data')->load(self::CRON_MODEL_PATH_INTERVAL, 'path')->getValue();
            $cronExprString = "{$time[1]} {$time[0]} */{$interval} * *";
        }

        Mage::getModel('core/config_data')
            ->load(self::CRON_STRING_PATH, 'path')
            ->setValue($cronExprString)
            ->setPath(self::CRON_STRING_PATH)
            ->save();

        return parent::_afterSave();
    }
}
