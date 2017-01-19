<?php

class MGS_Mpanel_Block_Layout_Page_Left extends Mage_Core_Block_Template {

    public function getBlocks() {
        $cmsId = Mage::getBlockSingleton('cms/page')->getPage()->getId();
        $collection = Mage::getModel('mpanel/block')
                ->getCollection()
                ->addFieldToFilter('page_type', array('eq' => 'page'))
                ->addFieldToFilter('page_id', array('eq' => $cmsId))
                ->addFieldToFilter('place', array('eq' => 'left'))
                ->setOrder('sort_order', 'ASC');
        if (!count($collection)) {
            $collection = Mage::getModel('mpanel/block')
                    ->getCollection()
                    ->addFieldToFilter('page_type', array('eq' => 'page'))
                    ->addFieldToFilter('page_id', array('eq' => 0))
                    ->addFieldToFilter('place', array('eq' => 'left'))
                    ->setOrder('sort_order', 'ASC');
        }
        return $collection;
    }

}
