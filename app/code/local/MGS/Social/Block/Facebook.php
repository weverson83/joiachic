<?php

/* * ****************************************************
 * Package   : Social
 * Author    : HIEPNH
 * Copyright : (c) 2014
 * ***************************************************** */
?>
<?php

class MGS_Social_Block_Facebook extends Mage_Core_Block_Template {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mgs/social/facebook.phtml');
    }

}
