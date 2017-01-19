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

use UOL_PagSeguro_Helper_Data as HelperData;

class UOL_PagSeguro_Helper_Canceled extends HelperData
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
        $this->getMagentoPayments();
        $this->requestTransactionsToCancel();
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
     * Get magento orders in a date range.
     */
    protected function getMagentoPayments()
    {
        $date = new DateTime ( "now" );
        $date->setTimezone ( new DateTimeZone ( "America/Sao_Paulo" ) );
        $dateInterval = "P" . ( string ) $this->days . "D";
        $date->sub ( new DateInterval ( $dateInterval ) );
        $date->setTime ( 00, 00, 00 );
        $date = $date->format ( "Y-m-d\TH:i:s" );

        $collection = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('created_at', array('from'=>$date->date, 'to'=>date('Y-m-d H:i:s')));

        foreach ($collection as $order) {
            $this->magentoPaymentList[] = $order->getId();
        }
    }
    
    /**
     * Build a array with PagSeguroTransaction where status is cancelable
     */
    private function requestTransactionsToCancel()
    {
        foreach ($this->magentoPaymentList as $orderId) {
            $orderHandler = Mage::getModel('sales/order')->load($orderId);
                
            if (Mage::getStoreConfig('payment/pagseguro/environment')
                    == strtolower(trim($this->getOrderEnvironment($orderId)))) {
                
                if (!is_null(Mage::getSingleton('core/session')->getData("store_id"))) {
                    if (Mage::getSingleton('core/session')->getData("store_id") == $orderHandler->getStoreId()) {
                        if ($orderHandler->getStatus() == "em_analise_ps"
                            or $orderHandler->getStatus() == 'aguardando_pagamento_ps'
                        ) {
                            $PagSeguroSummaryItem = $this->findPagSeguroTransactionByReference(
                                $orderHandler->getEntityId()
                            );

                            if (!is_null($PagSeguroSummaryItem)) {
                                $this->arrayPayments[] = $this->build($PagSeguroSummaryItem, $orderHandler);
                            }
                        }
                    }
                } elseif ($orderHandler) {
                    if ($orderHandler->getStatus() == "em_analise_ps"
                        or $orderHandler->getStatus() == 'aguardando_pagamento_ps'
                    ) {
                        $PagSeguroSummaryItem = $this->findPagSeguroTransactionByReference(
                            $orderHandler->getEntityId()
                        );

                        if (!is_null($PagSeguroSummaryItem)) {
                            $this->arrayPayments[] = $this->build($PagSeguroSummaryItem, $orderHandler);
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
    public function build($PagSeguroSummaryItem, $order)
    {

        $PagSeguroStatusValue = $this->getPaymentStatusFromKey($PagSeguroSummaryItem->getStatus()->getValue());

        if ($order->getStatus() == $PagSeguroStatusValue) {
            $config = "class='action' data-config='" . $order->getId() .'/'. $PagSeguroSummaryItem->getCode() .'/'.
                $this->getPaymentStatusFromKey($PagSeguroSummaryItem->getStatus()) . "'";
        } else {
            $config = " onclick='Modal.alertConciliation(&#34;"
                . $this->alertConciliation($this->__('estornar')) . "&#34;)'";
        }

        $actionOrder = "<a class='edit' target='_blank' href='" . $this->getEditOrderUrl($order->getId()) . "'>";
        $actionOrder .= $this->__('Ver detalhes') . "</a>";
        $actionOrder .= "<a " . $config . " href='javascript:void(0)'>";
        $actionOrder .= $this->__('Cancelar') . "</a>";

        return array( 'date' => $this->getOrderMagetoDateConvert($order->getCreatedAt()),
            'id_magento' => $order->getIncrementId(),
            'id_pagseguro' => $PagSeguroSummaryItem->getCode(),
            'status_magento' => $this->getPaymentStatusToString($this->getPaymentStatusFromValue($order->getStatus())),
            'action' => $actionOrder);

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
     * Find a PagSeguroTransaction by referece
     * @param $orderId
     * @return mixed
     */
    private function findPagSeguroTransactionByReference($orderId)
    {
        foreach ($this->PagSeguroPaymentList->getTransactions() as $list) {
            if ($this->getReferenceDecrypt($list->getReference()) == $this->getStoreReference()) {
                if ($this->getReferenceDecryptOrderID($list->getReference()) == $orderId) {
                    return $list;
                }
            }
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
}
