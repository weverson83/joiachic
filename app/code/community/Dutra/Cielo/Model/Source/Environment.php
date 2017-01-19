<?php

class Dutra_Cielo_Model_Source_Environment
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'teste', 'label' => Mage::helper('adminhtml')->__('Testing')),
            array('value' => 'producao', 'label' => Mage::helper('adminhtml')->__('Production')),
        );
    }
}