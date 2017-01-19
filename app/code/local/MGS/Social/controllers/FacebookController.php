<?php

/* * ****************************************************
 * Package   : Social
 * Author    : HIEPNH
 * Copyright : (c) 2014
 * ***************************************************** */
?>
<?php

require_once(Mage::getBaseDir('lib') . '/mgs/social/facebook/src/facebook.php');

class MGS_Social_FacebookController extends Mage_Core_Controller_Front_Action
{

    public function connectAction()
    {
        try {
            $this->_connectCallback();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        $this->_loginPostRedirect();
    }

    protected function _connectCallback()
    {
        $helper = Mage::helper('social/facebook');
        $storeId = Mage::app()->getStore()->getStoreId();
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $facebook = new Facebook(array(
            'appId' => $helper->getFacebookConfig('client_id', $storeId),
            'secret' => $helper->getFacebookConfig('client_secret', $storeId),
        ));
        $fid = $facebook->getUser();
        $ftoken = $facebook->getAccessToken();
        if ($fid) {
            try {
                $data = $facebook->api('/me?fields=id,first_name,last_name,email,gender,locale,picture');
                $customersByFacebookId = Mage::helper('social/facebook')->getCustomersByFacebookId($fid, $websiteId);
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    if ($customersByFacebookId->getSize()) {
                        Mage::getSingleton('core/session')->addNotice($this->__('Your facebook account is already connected to one of our store accounts.'));
                        return $this;
                    }
                    $customer = Mage::getSingleton('customer/session')->getCustomer();
                    Mage::helper('social/facebook')->connectByFacebookId($customer, $fid, $ftoken);
                    Mage::getSingleton('core/session')->addSuccess($this->__('Your facebook account is now connected to your store account. You can now login using our facebook login button or using store account credentials you will receive to your email address.'));
                    return $this;
                }
                if ($customersByFacebookId->getSize()) {
                    $customer = $customersByFacebookId->getFirstItem();
                    Mage::helper('social/facebook')->loginByCustomer($customer);
                    Mage::getSingleton('core/session')->addSuccess($this->__('You have successfully logged in using your facebook account.'));
                    return $this;
                }
                $customersByEmail = Mage::helper('social/facebook')->getCustomersByEmail($data['email'], $websiteId);
                if ($customersByEmail->getSize()) {
                    $customer = $customersByEmail->getFirstItem();
                    Mage::helper('social/facebook')->connectByFacebookId($customer, $fid, $ftoken);
                    Mage::getSingleton('core/session')->addSuccess($this->__('We have discovered you already have an account at our store. Your facebook account is now connected to your store account.'));
                    return $this;
                }
                $firstName = $data['first_name'];
                if (empty($firstName)) {
                    throw new Exception($this->__('Sorry, could not retrieve your facebook first name. Please try again.'));
                }
                $lastName = $data['last_name'];
                if (empty($lastName)) {
                    throw new Exception($this->__('Sorry, could not retrieve your facebook last name. Please try again.'));
                }
                Mage::helper('social/facebook')->connectByCreatingAccount($data['email'], $data['first_name'], $data['last_name'], $fid, $ftoken);
                Mage::getSingleton('core/session')->addSuccess($this->__('Your facebook account is now connected to your new user account at our store. Now you can login using our facebook login button or using store account credentials you will receive to your email address.'));
                return $this;
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError($this->__($e->getMessage()));
                return $this;
            }
        } else {
            Mage::getSingleton('core/session')->addError($this->__('Sorry, could not login. Please try again.'));
            return $this;
        }
    }

    protected function _loginPostRedirect()
    {
        $session = Mage::getSingleton('customer/session');
        $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }

}
