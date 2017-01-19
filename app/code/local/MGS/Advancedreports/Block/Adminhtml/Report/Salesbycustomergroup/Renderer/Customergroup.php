<?php

/* * ****************************************************
 * Package   : Advancedreports
 * Author    : http://www.arrowhitech.com
 * Copyright : (c) 2013 - ArrowHiTech.Com
 * ***************************************************** */
?>
<?php

class MGS_Advancedreports_Block_Adminhtml_Report_Salesbycustomergroup_Renderer_Customergroup extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $groupId = $row->getData('customer_group');
        $customerGroup = Mage::getModel('customer/group')->load($groupId);
        $html = $customerGroup->getCustomerGroupCode();
        return $html;
    }

}