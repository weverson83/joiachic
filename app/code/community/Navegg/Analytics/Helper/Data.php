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

class Navegg_Analytics_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    
    public function isAvailable($store = null)
    {
        // you must do the call for getConfig and get the informations of account
        $accountId = Mage::getStoreConfig(Navegg_Analytics_Model_Config::XML_PATH_ACCOUNT, $store);
        return $accountId && Mage::getStoreConfigFlag(Navegg_Analytics_Model_Config::XML_PATH_ACTIVE, $store);
    }

    protected function _getHttpsPage($host, $parameter)
    {
        $client = new Varien_Http_Client();
        $client->setUri($host)
            ->setConfig(array('timeout' => 30))
            ->setHeaders('accept-encoding', '')
            ->setParameterGet($parameter)
            ->setMethod(Zend_Http_Client::GET);
        $request = $client->request();
        // Workaround for pseudo chunked messages which are yet too short, so
        // only an exception is is thrown instead of returning raw body
        if (!preg_match("/^([\da-fA-F]+)[^\r\n]*\r\n/sm", $request->getRawBody(), $m))
            return $request->getRawBody();

        return $request->getBody();
    }
}