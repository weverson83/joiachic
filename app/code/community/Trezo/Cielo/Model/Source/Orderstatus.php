<?php

class Trezo_Cielo_Model_Source_Orderstatus
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'authorized', 'label' => Mage::helper('adminhtml')->__('Authorized')),
            array('value' => 'processing', 'label' => Mage::helper('adminhtml')->__('Processing')),
        );
    }
}