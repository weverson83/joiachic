<?php

class Navegg_Analytics_IndexController extends Mage_Core_Controller_Front_Action
{

	private static $nvgApiUrl = 'http://cluster.navegg.com/ws/';
	private static $nvgApiKey = 'cfcd208495d565ef66e7dff9f98764da';

	protected function _getHelper()
    {
        return Mage::helper('navegg');
    }

	public function getidAction(){
		$params = $this->getRequest()->getParams();
		if (empty($params['email'])) {
            print '{"error":"true"}';
			// Mage::throwException('Error: No parameters specified');
		}
        $url = Navegg_Analytics_IndexController::$nvgApiUrl;
        $url .= '?action=partneruseremail';
        $url .= '&part_key='.Navegg_Analytics_IndexController::$nvgApiKey;
        $url .= '&email='.urlencode($params['email']);
        if (!$data = file_get_contents($url)) {
            print '{"error":"true"}';
        } else {
            print $data;
        }
    }

    public function newaccAction(){
    	$params = $this->getRequest()->getParams();
		if (count($params)<4) {
            print '{"error":"true","message":"parameters"}';
			Mage::throwException('Error: No parameters specified');
		}
        // $postdata = http_build_query(
        //     array(
        //         'action' => 'partneraccount',
        //         'usr_name' => $name,
        //         'usr_email' => $email,
        //         'usr_site_name' => $siteName,
        //         'usr_domain' => $siteUrl,
        //         'part_key' => Navegg_Analytics_IndexController::$nvgApiKey
        //     )
        // );

        // $opts = array('http' =>array('method'  => 'POST','content' => $postdata));
        // $context = stream_context_create($opts);
        // $content = file_get_contents(Navegg_Analytics_IndexController::$nvgApiUrl, 0, $context);
        $url = Navegg_Analytics_IndexController::$nvgApiUrl;
        $url .= '?action=partneraccount';
        $url .= '&part_key='.Navegg_Analytics_IndexController::$nvgApiKey;
        $url .= '&usr_name='.urlencode($params['usr_name']);
        $url .= '&usr_email='.urlencode($params['usr_email']);
        $url .= '&usr_site_name='.urlencode($params['usr_site_name']);
        $url .= '&usr_domain='.urlencode($params['usr_domain']);
        if (!$data = file_get_contents($url)) {
            print '{"error":"true"}';
        } else {
            print $data;
        }
    }

	public function checkemailAction()
	{
		try {
			$params = $this->getRequest()->getParams();
			if (empty($params['email'])) {
				Mage::throwException('Error: No parameters specified');
			}
			$response =  $this->_getHelper()->checkEmailRequest($params);
			if (empty($response)) {
				Mage::throwException('Error: Connection to moneybookers.com failed');
			}
			$this->getResponse()->setBody($response);
			return;
		} catch (Mage_Core_Exception $e) {
			$response = $e->getMessage();
		} catch (Exception $e) {
			$response = 'Error: System error during request';
		}
		$this->getResponse()->setBody($response);
	}

}
