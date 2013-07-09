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
 * Backend model for saving certificate file in case of using certificate based authentication
 */
class Wsu_Centralprocessing_Model_System_Config_Backend_Cert extends Mage_Core_Model_Config_Data
{
    /**
     * Process additional data before save config
     *
     * @return Wsu_Centralprocessing_Model_System_Config_Backend_Cert
     */
    protected function _beforeSave()
    {
        
        $value = $this->getValue();
        if (is_array($value) && !empty($value['delete'])) {
            $this->setValue('');
            Mage::getModel('centralprocessing/cert')->loadByWebsite($this->getScopeId())->delete();
        }

        if (!isset($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'])) {
            return $this;
        }
        $tmpPath = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
        if ($tmpPath && file_exists($tmpPath)) {
            if (!filesize($tmpPath)) {
                Mage::throwException(Mage::helper('centralprocessing')->__('Centralprocessing certificate file is empty.'));
            }
            $this->setValue($_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value']);
            $content = Mage::helper('core')->encrypt(file_get_contents($tmpPath));
            Mage::getModel('centralprocessing/cert')->loadByWebsite($this->getScopeId())
                ->setContent($content)
                ->save();
        }
        return $this;
    }

    /**
     * Process object after delete data
     *
     * @return Wsu_Centralprocessing_Model_System_Config_Backend_Cert
     */
    protected function _afterDelete()
    {
        
        Mage::getModel('centralprocessing/cert')->loadByWebsite($this->getScopeId())->delete();
        return $this;
    }
}
