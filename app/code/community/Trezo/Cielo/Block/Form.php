<?php

class Trezo_Cielo_Block_Form extends Mage_Payment_Block_Form_Cc
{

    protected function _construct()
    {
        $this->setTemplate('trezo/cielo/form.phtml');
    }

    public function getInstallments() 
    {
        return $this->getMethod()->getInstallments();
    }

    public function getCcMonths()
	{
    	$months = array();
		
		for($i = 1; $i <= 12; $i++)
		{
			$label = ($i < 10) ? ("0" . $i) : $i;
			
			$months[] = array("num" => $i, "label" => $this->htmlEscape($label));
		}
		
		return $months;
	}
}