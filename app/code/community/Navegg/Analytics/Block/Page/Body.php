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

class Navegg_Analytics_Block_Page_Body extends Mage_Core_Block_Text
{
    /**
     * Render tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('navegg')->isAvailable()) {
            return '';
        }
        
        $html = '<!-- Navegg __CID -->';
        
        $html .= '<input type="hidden" name="__cid" value="'.$this->getProductName().'" />';
        
        $html .= '<!-- Navegg __CID -->';
        
        return $html;
    }
}