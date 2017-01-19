<?php

/* * ****************************************************
 * Package   : Advancedreports
 * Author    : http://www.arrowhitech.com
 * Copyright : (c) 2013 - ArrowHiTech.Com
 * ***************************************************** */
?>
<?php

class MGS_Advancedreports_Block_Adminhtml_Report_Salesbydayofweek_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract {

    protected $_columnGroupBy = 'day';

    public function __construct() {
        parent::__construct();
        $this->setCountTotals(true);
    }

    public function getResourceCollectionName() {
        return 'advancedreports/report_salesbydayofweek_collection';
    }

    protected function _prepareColumns() {
        $this->addColumn('day', array(
            'header' => Mage::helper('advancedreports')->__('Day of Week'),
            'index' => 'day',
            'type' => 'text',
            'sortable' => false,
            'totals_label' => Mage::helper('advancedreports')->__('Total'),
            'html_decorators' => array('nobr')
        ));

        $this->addColumn('percent', array(
            'header' => Mage::helper('advancedreports')->__('Percent'),
            'align' => 'right',
            'index' => 'percent',
            'type' => 'text',
            'renderer' => 'MGS_Advancedreports_Block_Adminhtml_Report_Salesbydayofweek_Renderer_Percent',
            'sortable' => false,
            'totals_label' => Mage::helper('advancedreports')->__(null),
        ));

        $this->addColumn('total_item_count', array(
            'header' => Mage::helper('advancedreports')->__('Number of Orders'),
            'align' => 'right',
            'index' => 'total_item_count',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false
        ));

        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('advancedreports')->__('Total'),
            'align' => 'right',
            'index' => 'grand_total',
            'currency_code' => $currencyCode,
            'width' => '100px',
            'type' => 'currency',
            'total' => 'sum',
            'sortable' => false
        ));

        $this->addExportType('*/*/exportSalesByDayOfWeekCsv', Mage::helper('advancedreports')->__('CSV'));
        $this->addExportType('*/*/exportSalesByDayOfWeekExcel', Mage::helper('advancedreports')->__('Excel XML'));

        return parent::_prepareColumns();
    }

}