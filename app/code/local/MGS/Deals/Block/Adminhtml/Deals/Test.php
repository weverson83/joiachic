<?php

class MGS_Deals_Block_Adminhtml_Deals_Test extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_selectedProducts = array();

    /**
     * Block construction, prepare grid params
     *
     * @param array $arguments Object data
     */
    public function __construct($arguments=array())
    {
        parent::__construct($arguments);
        $this->setDefaultSort('name');
        $this->setUseAjax(true);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element Form Element
     * @return Varien_Data_Form_Element_Abstract
     */
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqId = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('*/catalog_product_widget/chooser', array(
            'uniq_id' => $uniqId,
            'use_massaction' => false,
        ));

        $chooser = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')
            ->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId);

        if ($element->getValue()) {
            $value = explode('/', $element->getValue());
            $productId = false;
            if (isset($value[0]) && isset($value[1]) && $value[0] == 'product') {
                $productId = $value[1];
            }
            $categoryId = isset($value[2]) ? $value[2] : false;
            $label = '';
            if ($categoryId) {
                $label = Mage::getResourceSingleton('catalog/category')
                    ->getAttributeRawValue($categoryId, 'name', Mage::app()->getStore()) . '/';
            }
            if ($productId) {
                $label .= Mage::getResourceSingleton('catalog/product')
                    ->getAttributeRawValue($productId, 'name', Mage::app()->getStore());
            }
            $chooser->setLabel($label);
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Checkbox Check JS Callback
     *
     * @return string
     */
    public function getCheckboxCheckCallback()
    {
        if ($this->getUseMassaction()) {
            return "function (grid, element) {
                $(grid.containerId).fire('product:changed', {element: element});
            }";
        }
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        if (!$this->getUseMassaction()) {
            $chooserJsObject = $this->getId();
            return '
                function (grid, event) {
                    var trElement = Event.findElement(event, "tr");
                    var productId = trElement.down("td").innerHTML;
                    var productName = trElement.down("td").next().next().innerHTML;
                    var optionLabel = productName;
                    var optionValue = "product/" + productId.replace(/^\s+|\s+$/g,"");
                    if (grid.categoryId) {
                        optionValue += "/" + grid.categoryId;
                    }
                    if (grid.categoryName) {
                        optionLabel = grid.categoryName + " / " + optionLabel;
                    }
                    '.$chooserJsObject.'.setElementValue(optionValue);
                    '.$chooserJsObject.'.setElementLabel(optionLabel);
                    '.$chooserJsObject.'.close();
                }
            ';
        }
    }

    /**
     * Category Tree node onClick listener js function
     *
     * @return string
     */
    public function getCategoryClickListenerJs()
    {
        $js = '
            function (node, e) {
                {jsObject}.addVarToUrl("category_id", node.attributes.id);
                {jsObject}.reload({jsObject}.url);
                {jsObject}.categoryId = node.attributes.id != "none" ? node.attributes.id : false;
                {jsObject}.categoryName = node.attributes.id != "none" ? node.text : false;
            }
        ';
        $js = str_replace('{jsObject}', $this->getJsObjectName(), $js);
        return $js;
    }

    /**
     * Filter checked/unchecked rows in grid
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Mage_Adminhtml_Block_Catalog_Product_Widget_Chooser
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $selected = $this->getSelectedProducts();
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$selected));
            } else {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$selected));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare products collection, defined collection filters (category, product type)
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('deals/deals')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
    }

    /**
     * Prepare columns for products grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _getStore()
	{
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}
    /**
     * Prepare columns for products grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {

            $this->addColumn('in_products', array(
                'header_css_class' => 'a-center',
                'type'      => 'checkbox',
                'name'      => 'in_products',
                'inline_css' => 'checkbox entities',
                'field_name' => 'in_products',
                'values'    => $this->getSelectedProducts(),
                'align'     => 'center',
                'index'     => 'entity_id',
                'use_index' => true,
            ));

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));
        $this->addColumn('chooser_sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'name'      => 'chooser_sku',
            'width'     => '80px',
            'index'     => 'sku'
        ));
        $this->addColumn('chooser_name', array(
            'header'    => Mage::helper('catalog')->__('Product Name'),
            'name'      => 'chooser_name',
            'index'     => 'name'
        ));
		$store = $this->_getStore();
		$this->addColumn('price',array(
			'header'=> Mage::helper('catalog')->__('Price'),
			'type'  => 'price',
			'currency_code' => $store->getBaseCurrency()->getCode(),
			'index' => 'price',
		));
		
		$this->addColumn('special_price',array(
			'header'=> Mage::helper('catalog')->__('Deal Price'),
			'type'  => 'price',
			'currency_code' => $store->getBaseCurrency()->getCode(),
			'index' => 'special_price',
		));
		
		$this->addColumn('start_time', array(
            'header'    => Mage::helper('cms')->__('Start Time'),
            'index'     => 'start_time',
            'type'      => 'datetime',
        ));

        $this->addColumn('end_time', array(
            'header'    => Mage::helper('cms')->__('End Time'),
            'index'     => 'end_time',
            'type'      => 'datetime',
        ));
		
		/* $this->addColumn('qty', array(
			'header'    => Mage::helper('deals')->__('Qty'),
			'align'     =>'left',
			'index'     => 'qty',
			'width'		=>'50px',
		)); */
		
		$this->addColumn('deal_qty', array(
			'header'    => Mage::helper('deals')->__('Deal Quantity'),
			'align'     =>'left',
			'width'		=>'50px',
			'filter'    => false,
			'renderer'	=> new MGS_Deals_Block_Adminhtml_Deals_Renderer_Qty
		));
		
		/* $this->addColumn('max_deal_qty', array(
			'header'    => Mage::helper('deals')->__('Deal Quantity'),
			'align'     =>'left',
			'index'     => 'max_deal_qty',
			'width'		=>'50px',
		)); */
		
		$this->addColumn('sold', array(
			'header'    => Mage::helper('deals')->__('Sold'),
			'align'     =>'left',
			'index'     => 'sold',
			'width'		=>'50px',
		));


		$this->addColumn('status', array(
			'header'    => Mage::helper('deals')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
				1 => 'Processing',
				2 => 'Running',
				3 => 'Done',
			),
			'renderer'	=> new MGS_Deals_Block_Adminhtml_Deals_Renderer_Status
		));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('deals')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
				'renderer'	=> new MGS_Deals_Block_Adminhtml_Deals_Renderer_Action,
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
        return parent::_prepareColumns();
    }

    /**
     * Adds additional parameter to URL for loading only products grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/catalog_product_widget/chooser', array(
            'products_grid' => true,
            '_current' => true,
            'uniq_id' => $this->getId(),
            'use_massaction' => $this->getUseMassaction(),
            'product_type_id' => $this->getProductTypeId()
        ));
    }

    /**
     * Setter
     *
     * @param array $selectedProducts
     * @return Mage_Adminhtml_Block_Catalog_Product_Widget_Chooser
     */
    public function setSelectedProducts($selectedProducts)
    {
        $this->_selectedProducts = $selectedProducts;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        if ($selectedProducts = $this->getRequest()->getParam('selected_products', null)) {
            $this->setSelectedProducts($selectedProducts);
        }
        return $this->_selectedProducts;
    }
}
