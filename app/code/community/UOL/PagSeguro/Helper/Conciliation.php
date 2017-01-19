<?php

/**
************************************************************************
Copyright [2015] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

class UOL_PagSeguro_Helper_Conciliation extends UOL_PagSeguro_Helper_Data
{
    /**
     * @var int
     */
    private $days;
    
    /**
     * @var Array
     */
    private $magentoPaymentList;
    
    /**
     * @var Array
     */
    private $PagSeguroPaymentList;
    
    /**
     * @var Array
     */
    protected $arrayPayments;

    /**
     * Executes the essentials functions for this helper
     * @param $days
     */
    public function initialize($days)
    {
        $this->days = $days;
        $this->getPagSeguroPayments();
        $this->requestTransactionsToConciliation();
    }

    /**
     * Returns payment array
     * @return mixed $this->arrayPayment
     */
    public function getPaymentsArray()
    {
        return $this->arrayPayments;
    }

    /**
     * Build a array with PagSeguroTransaction
     */
    private function requestTransactionsToConciliation()
    {

        foreach ($this->PagSeguroPaymentList->getTransactions() as $payment) {
            $orderId = $this->getReferenceDecryptOrderID($payment->getReference());
            $orderHandler = Mage::getModel('sales/order')->load($orderId);

            if ($this->getStoreReference() == $this->getReferenceDecrypt($payment->getReference())) {
                if (Mage::getStoreConfig('payment/pagseguro/environment')
                    == strtolower(trim($this->getOrderEnvironment($orderId)))) {
                    if (!is_null(Mage::getSingleton('core/session')->getData("store_id"))) {
                        if (Mage::getSingleton('core/session')->getData("store_id") == $orderHandler->getStoreId()) {
                            if ($orderHandler->getStatus()
                                != $this->getPaymentStatusFromKey($payment->getStatus()->getValue())) {
                                $this->arrayPayments[] = $this->build($payment, $orderHandler);
                            }
                        }
                    } elseif ($orderHandler) {
                        if ($orderHandler->getStatus()
                            != $this->getPaymentStatusFromKey($payment->getStatus()->getValue())) {
                            $this->arrayPayments[] = $this->build($payment, $orderHandler);
                        }
                    }
                }
            }
        }
        Mage::getSingleton('core/session')->unsetData('store_id');
    }

    /**
     * @param PagSeguroSummaryItem $PagSeguroSummaryItem
     * @param Mage_Sales_Model_Order $order
     * @return multitype:date string NULL Ambigous <boolean, string, string, multitype:>
     */
    public function build($payment, $order)
    {

        $config = $order->getId() .'/'. $payment->getCode() .'/'
            . $this->getPaymentStatusFromKey($payment->getStatus()->getValue(), true);

        $checkbox  = "<label class='chk_email'>";
        $checkbox .= "<input type='checkbox' name='conciliation_orders[]' class='checkbox' data-config='"
            . $config . "' />";
        $checkbox .= "</label>";

        // Receives the full html link to edit an order
        $editOrder = "<a class='edit' target='_blank' href='" . $this->getEditOrderUrl($order->getEntityId()) . "'>";
        $editOrder .= $this->__('Ver detalhes') . "</a>";

        return array('checkbox' => $checkbox,
            'date' => $this->getOrderMagetoDateConvert($order->getCreatedAt()),
            'id_magento' => "#".$order->getIncrementId(),
            'id_pagseguro' => $payment->getCode(),
            'status_magento' => $this->getPaymentStatusToString($this->getPaymentStatusFromValue($order->getStatus())),
            'status_pagseguro' => $this->getPaymentStatusToString($payment->getStatus()->getValue()),
            'edit' => $editOrder);
    }

    /**
     * Get PagSeguroTransaction from webservice in a date range.
     * @param null $page
     */
    private function getPagSeguroPayments($page = null)
    {
        if (is_null($page)) {
            $page = 1;
        }

        $date = new DateTime ( "now" );
        $date->setTimezone ( new DateTimeZone ( "America/Sao_Paulo" ) );
        $dateInterval = "P" . ( string ) $this->days . "D";
        $date->sub ( new DateInterval ( $dateInterval ) );
        $date->setTime ( 00, 00, 00 );
        $date = $date->format ( "Y-m-d\TH:i:s" );

        try {

            if (is_null($this->PagSeguroPaymentList)) {
                $this->PagSeguroPaymentList = Mage::helper('pagseguro/webservice')->getTransactionsByDate($page, 1000, $date);
            } else {
                $PagSeguroPaymentList = Mage::helper('pagseguro/webservice')->getTransactionsByDate($page, 1000, $date);

                $this->PagSeguroPaymentList->setDate($PagSeguroPaymentList->getDate());
                $this->PagSeguroPaymentList->setCurrentPage($PagSeguroPaymentList->getCurrentPage());
                $this->PagSeguroPaymentList->setTotalPages($PagSeguroPaymentList->getTotalPages());
                $this->PagSeguroPaymentList->setResultsInThisPage(
                    $PagSeguroPaymentList->getResultsInThisPage() + $this->PagSeguroPaymentList->getResultsInThisPage
                );

                $this->PagSeguroPaymentList->setTransactions(
                    array_merge(
                        $this->PagSeguroPaymentList->getTransactions(),
                        $PagSeguroPaymentList->getTransactions()
                    )
                );
            }

            if ($this->PagSeguroPaymentList->getTotalPages() > $page) {
                $this->getPagSeguroPayments(++$page);
            }
        } catch (Exception $pse) {
            throw $pse;     
        }
    }

    /**
     * Get order environment
     * @param int $orderId
     * @return string Order environment
     */
    private function getOrderEnvironment($orderId)
    {
        $reader = Mage::getSingleton("core/resource")->getConnection('core_read');
        $table = Mage::getConfig()->getTablePrefix() . 'pagseguro_orders';

        $query = "SELECT environment FROM ".$table." WHERE order_id = ".$orderId;

        if ($reader->fetchOne($query) == 'Produção')
        {
            return "production";
        } else {
            return $reader->fetchOne($query);
        }
    }
    
    /**
     * Check config for access
     * @return multitype:string boolean
     */
    private function checkAccess()
    {
        $paymentModel = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');

        if (!Mage::getStoreConfig('uol_pagseguro/store/credentials')) {
            return array(
                'message' => "E-mail e/ou token inválido(s) para o ambiente selecionado.",
                'status' => false
            );
        }

        if (!$paymentModel->getConfigData('email')) {
            return array(
                'message' => "Preencha o e-mail do vendedor",
                'status' => false
            );
        }

        if (!$paymentModel->getConfigData('token')) {
            return array(
                'message' => "Preencha o token.",
                'status' => false
            );
        }

        return array(
            'message' => "",
            'status' => true
        );

    }
    
    /**
     * Check for access and set errors if exists.
     */
    public function checkViewAccess()
    {
        $access = $this->checkAccess();
        if (!$access['status']) {
            Mage::getSingleton('core/session')->addError($access['message']);
            Mage::app()->getResponse()->setRedirect(
                Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit/section/payment/')
            );
        }
    }
}
