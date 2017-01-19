<?php

class Promocode_Model_Observer
{

	public function customerSaveAfter(Varien_Event_Observer $o)
	{
	        
		$customerData = $o->getCustomer()->getData();		
	          
        $modelcollection = Mage::getModel('salesrule/rule')
        						->getCollection()
        						->addFieldToFilter('is_active', 1)
        						->addFieldToFilter('name', array('like' => '%cupomprimeiroregistro%'));
        						
        $newCollection = array();
        $newCollection = $modelcollection->getData();

        $ruleName = $newCollection[0]['name'];        
        $promocode = $newCollection[0]['code'];	              

        if($ruleName=="cupomprimeiroregistro") {             

            try {                                
                $subject = 'Cupom de desconto';
                $emailTemplate  = Mage::getModel('core/email_template')->loadDefault('notifynewcustomer');
                $emailTemplateVariables = array();
                $emailTemplateVariables['username'] = $customerData['firstname'].' '.$customerData['lastname'];
                $emailTemplateVariables['customer_email'] = $customerData['email'];
                $emailTemplateVariables['promo_code'] = $promocode;
                $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
            
                $mail = Mage::getModel('core/email')
                        ->setToEmail($customerData['email'])
                        ->setBody(utf8_decode($processedTemplate))
                        ->setSubject($subject)
                        ->setFromName(utf8_decode(Mage::app()->getStore()->getStoreName()))
                        ->setType('html');

                $mail->setFromName(Mage::getStoreConfig('trans_email/ident_support/name'));        
                $mail->setFromEmail('sac@joiachic.com.br');
                $mail->send();
            } catch (Exception $e) {            	
                Mage::logException($e);
            } 
        }
	    	    
	}
}