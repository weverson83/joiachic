<?php
/**
 * Enable Disable Model
 * 
 * @category    MGS
 * @package     MGS_Storelocator
 * @author      MGS Magento Team
 * 
 */

/**
 * Used in displaying options  Enable|Disable
 */
class MGS_Storelocator_Model_Enabledisable extends Mage_Adminhtml_Model_System_Config_Source_Enabledisable
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            1 => Mage::helper('adminhtml')->__('Enable'),
            0 => Mage::helper('adminhtml')->__('Disable'),
        );
    }
}
?>
