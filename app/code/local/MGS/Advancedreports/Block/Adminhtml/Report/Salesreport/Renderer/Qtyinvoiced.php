<?php

/* * ****************************************************
 * Package   : Advancedreports
 * Author    : http://www.arrowhitech.com
 * Copyright : (c) 2013 - ArrowHiTech.Com
 * ***************************************************** */
?>
<?php

class MGS_Advancedreports_Block_Adminhtml_Report_Salesreport_Renderer_Qtyinvoiced extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $id = $row->getData('entity_id');
        $model = Mage::getModel('sales/order_item')->getCollection()->addFieldToFilter('order_id', array('eq' => $id))->getFirstItem();
        if ((int) $model->getQtyInvoiced() == 0) {
            $html = '0';
        } else {
            $html = (int) $model->getQtyInvoiced();
        }
        return $html;
    }

}