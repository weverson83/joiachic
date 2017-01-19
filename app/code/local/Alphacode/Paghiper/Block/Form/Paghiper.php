<?php
// app/code/local/Envato/Custompaymentmethod/Block/Form/Custompaymentmethod.php
class Alphacode_Paghiper_Block_Form_Paghiper extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('paghiper/Form/paghiper.phtml');
  }
}