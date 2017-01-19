<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to suporte.developer@buscape-inc.com so we can send you a copy immediately.
 *
 * @category   Buscape
 * @package    Navegg_Analytics
 * @copyright  Copyright (c) 2015 Navegg (http://www.navegg.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Navegg_Analytics_Model_Config extends Varien_Object
{
    const XML_PATH          = 'navegg_analytics/navegg/';
    
    const XML_PATH_ACTIVE   = 'navegg_analytics/navegg/active';
    
    const XML_PATH_ACCOUNT  = 'navegg_analytics/navegg/advertising';
    
    protected $_config = array();

    
    public function getConfigData($key, $storeId = null)
    {
        if (!isset($this->_config[$key][$storeId])) {
            $value = Mage::getStoreConfig(self::XML_PATH . $key, $storeId);
            $this->_config[$key][$storeId] = $value;
        }
        return $this->_config[$key][$storeId];
    }
    
    public function getAccount($store = null)
    {
        if (!$this->hasData('navegg_account')) {
            $this->setData('navegg_account', $this->getConfigData('advertising', $store));
        }
        
        return $this->getData('navegg_account');
    }
    
    public function getEvent($store = null)
    {
        die(var_dump($this->_config));
        if (!$this->hasData('navegg_event')) {
            $this->setData('navegg_event', $this->getConfigData('event', $store));
        }
        
        return $this->getData('navegg_event');
    }
}
