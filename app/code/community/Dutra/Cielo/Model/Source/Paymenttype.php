<?php

class Dutra_Cielo_Model_Source_Paymenttype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'VI', 'label'=>Mage::helper('adminhtml')->__('Visa')),
            array('value' => 'MC', 'label'=>Mage::helper('adminhtml')->__('MasterCard')),
            array('value' => 'AE', 'label'=>Mage::helper('adminhtml')->__('American Express')),
            array('value' => 'DN', 'label'=>Mage::helper('adminhtml')->__('Diners Club')),
            array('value' => 'EL', 'label'=>Mage::helper('adminhtml')->__('Elo')),
        );
    }
}