<?php

class Trezo_Cielo_Block_Info extends Mage_Payment_Block_Info_Cc
{

    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);

        if ($this->getInfo()->getCcInstallments()) {
            $transport[$this->__('Parcelas')] = $this->getInfo()->getCcInstallments();
        }

        if ($this->getInfo()->getCcOwner()) {
            $transport[$this->__('Nome no portador')] = $this->getInfo()->getCcOwner();
        }

        $transaction = $this->getInfo()->getAuthorizationTransaction();

        if (!empty($transaction)) {
            $info = $transaction->getAdditionalInformation();

            if (!empty($info['response'])) {
                $info = json_decode($info['response']);

                if (!empty($info->tid)) {
                    $transport[$this->__('Código da transação')] = $info->tid;
                }

                if (!empty($info->autorizacao->mensagem)) {
                    $transport[$this->__('Mensagem de autorização')] = $info->autorizacao->mensagem;
                }

                if (!empty($info->autorizacao->arp)) {
                    $transport[$this->__('ARP')] = $info->autorizacao->arp;
                }
            }
        }

        return $transport;
    }

}