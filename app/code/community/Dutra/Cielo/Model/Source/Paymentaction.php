<?php

class Dutra_Cielo_Model_Source_Paymentaction
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'authorize', 'label' => Mage::helper('adminhtml')->__('Authorize')),
            array('value' => 'authorize_capture', 'label' => Mage::helper('adminhtml')->__('Capture')),
        );
    }
}