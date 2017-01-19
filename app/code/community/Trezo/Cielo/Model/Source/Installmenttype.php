<?php

class Trezo_Cielo_Model_Source_Installmenttype
{
    public function toOptionArray()
    {
        return array(
            array('value' => '2', 'label' => Mage::helper('adminhtml')->__('Loja')),
            array('value' => '3', 'label' => Mage::helper('adminhtml')->__('Administradora')),
        );
    }
}