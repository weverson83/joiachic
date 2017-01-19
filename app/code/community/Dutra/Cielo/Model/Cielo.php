<?php

class Dutra_Cielo_Model_Cielo extends Mage_Payment_Model_Method_Cc
{

    protected $_code = 'dutra_cielo';
    protected $_formBlockType = 'dutra_cielo/form';
    protected $_infoBlockType = 'dutra_cielo/info';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canSaveCc = false;
    protected $_canFetchTransactionInfo = true;

    public function validate()
    {
        $ccType = '';
        $errorMsg = false;
        $info = $this->getInfoInstance();
        $availableTypes = explode(',',$this->getConfigData('cctypes'));

        $ccNumber = preg_replace('/[\-\s]+/', '', $info->getCcNumber());
        $info->setCcNumber($ccNumber);

        if (in_array($info->getCcType(), $availableTypes)){
            if ($this->validateCcNum($ccNumber) || ($this->OtherCcType($info->getCcType()) && $this->validateCcNumOther($ccNumber))) {
                $ccType = 'OT';
                $ccTypeRegExpList = array(
                    // Visa
                    'VI'  => '/^4/', //'/^4[0-9]{12}([0-9]{3})?$/',
                    // Master Card
                    'MC'  => '/^5[1-5]/', //'/^5[1-5][0-9]{14}$/',
                    // American Express
                    'AE'  => '/^3[47]/', //'/^3[47][0-9]{13}$/',
                    // Diners
                    'DN'  => '/^3(?:0[0-5]|[68])/', //'/^30[0-5][0-9]{11}|(2014|2149)[0-9]{11}|36[0-9]{12}|(54|55)[0-9]{12}$/',
                    // Discovery
                    'DI'  => '/^6(?:011|5)/', //'/^6011[0-9]{12}$/',
                    // JCB
                    'JCB' => '/^(?:2131|1800|35\d{3})/', //'/^(3[0-9]{15}|(2131|1800)[0-9]{11})$/',
                    // Elo
                    'EL'  => '/^[0-9]{16}$/', //6362970000457013
                );

                foreach ($ccTypeRegExpList as $ccTypeMatch=>$ccTypeRegExp) {
                    if (preg_match($ccTypeRegExp, $ccNumber)) {
                        $ccType = $ccTypeMatch;
                        break;
                    }
                }

                if (!$this->OtherCcType($info->getCcType()) && $ccType!=$info->getCcType()) $errorMsg = Mage::helper('payment')->__('Credit card number mismatch with credit card type.');
            } else $errorMsg = Mage::helper('payment')->__('Invalid Credit Card Number');
        } else $errorMsg = Mage::helper('payment')->__('Credit card type is not allowed for this payment method.');

        if ($errorMsg === false && $this->hasVerification()) {
            $verifcationRegEx = $this->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
            if (!$info->getCcCid() || !$regExp || !preg_match($regExp ,$info->getCcCid())){
                $errorMsg = Mage::helper('payment')->__('Please enter a valid credit card verification number.');
            }
        }

        if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) $errorMsg = Mage::helper('payment')->__('Incorrect credit card expiration date.');
        if ($errorMsg) Mage::throwException($errorMsg);
        if ($this->getIsCentinelValidationEnabled()) $this->getCentinelValidator()->validate($this->getCentinelValidationData());

        return $this;
    }

    public function getVerificationRegEx()
    {
        $verificationExpList = array(
            'VI'  => '/^[0-9]{3}$/',   // Visa
            'MC'  => '/^[0-9]{3}$/',   // Master Card
            'AE'  => '/^[0-9]{4}$/',   // American Express
            'DI'  => '/^[0-9]{3}$/',   // Discovery
            'JCB' => '/^[0-9]{3,4}$/', // JCB
            'DN'  => '/^[0-9]+$/',     // Diners 
            'EL'  => '/^[0-9]+$/',     // Elo
        );
        return $verificationExpList;
    }

    public function assignData($data)
    {
        $this->getInfoInstance()->setCcInstallments($data['cc_installments']);
        return parent::assignData($data);
    }

    private function getCcTypeCielo($ccType)
    {
        switch ($ccType) {
            case 'VI':
                return 'visa';
                break;            
            case 'MC':
                return 'mastercard';
                break;
            case 'AE':
                return 'amex';
                break;
            case 'DN':
                return 'diners';
                break;
            case 'EL':
                return 'elo';
                break;
        }
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        $enabledLog = Mage::getStoreConfig('payment/dutra_cielo/active_log');
        //Pega os dados do formulário de pagamento
        $method = $payment->getMethod();
        $ccType = $payment->getCcType();       
        $ccTypeCielo = $this->getCcTypeCielo($ccType);
        $ccOwner = $payment->getCcOwner();
        $ccNumber = $payment->getCcNumber();
        $ccExpMonth = $payment->getCcExpMonth();
        $ccExpYear = $payment->getCcExpYear();
        $ccExp = $ccExpYear .''. $ccExpMonth;
        $ccCid = $payment->getCcCid();
        $ccInstallments = $payment->getCcInstallments();

        //Cria o objeto do webservice
        $webservice = Mage::getModel('dutra_cielo/webservice_cielo', array('numero' => Mage::getStoreConfig('payment/dutra_cielo/affiliationnumber'), 'chave' => Mage::getStoreConfig('payment/dutra_cielo/affiliationkey')));

        //Define os dados do webservice
        $webservice->setAmbiente(Mage::getStoreConfig('payment/dutra_cielo/environment'));
        $webservice->setProduto((($ccInstallments == 1) ? 1 : Mage::getStoreConfig('payment/dutra_cielo/installmenttype')));
        $webservice->setBandeira($ccTypeCielo);
        $webservice->setParcelas($ccInstallments);

        //Gera o TID
        $tid = simplexml_load_string($webservice->tid());
        $tid = (string) $tid->tid;

        //Cria uma nova transacao
        $transacao = Mage::getModel('dutra_cielo/webservice_transacao');
        $transacao->setTid($tid);
        $transacao->setAutorizar(3);
        $transacao->setCapturar('false');
        $transacao->setDataHora(date('Y-m-d\Th:i:s'));
        $transacao->setDescricao($payment->getOrder()->getIncrementId());
        $transacao->setMoeda(986);
        $transacao->setNumero($payment->getOrder()->getIncrementId());
        $transacao->setParcelas($ccInstallments);
        $transacao->setProduto((($ccInstallments == 1) ? 1 : Mage::getStoreConfig('payment/dutra_cielo/installmenttype')));
        $transacao->setValor($payment->getOrder()->getGrandTotal() * 100);

        //Cria um novo cartão
        $cartao = Mage::getModel('dutra_cielo/webservice_cartao');
        $cartao->setBandeira($ccTypeCielo);
        $cartao->setCartao($ccNumber);
        $cartao->setCodigoSeguranca($ccCid);
        $cartao->setIndicador((!$ccCid) ? 9 : 1);
        $cartao->setNomePortador($ccOwner);
        $cartao->setValidade($ccExp);

        //Cria um novo cliente
        $cliente = Mage::getModel('dutra_cielo/webservice_cliente', array('numero' => Mage::getStoreConfig('payment/dutra_cielo/affiliationnumber'), 'chave' => Mage::getStoreConfig('payment/dutra_cielo/affiliationkey')));
        $cliente->setAmbiente(Mage::getStoreConfig('payment/dutra_cielo/environment'));
        $cliente->autorizacaoPortador($transacao, $cartao);

        //Carrega a resposta do webservice
        $request = simplexml_load_string($cliente->getXml()->asXML());
        $response = simplexml_load_string($cliente->enviaChamada()->asXML());
        if ($enabledLog) {
            Mage::log($request, null, 'dutra-cielo-request.log', true);
            Mage::log($response, null, 'dutra-cielo-response.log', true);
        }

        //Se a transação não foi autorizada
        if ($response->autorizacao->codigo != 4)
        {
            Mage::throwException('Transação não autorizada. Verifique seus dados e tente novamente. (Cód '. $response->autorizacao->codigo .')');
        }

        //Se a transação foi autorizada        
        $transaction = Mage::getModel('sales/order_payment_transaction');
        $transaction->setOrderPaymentObject($payment);
        $transaction->setTxnId($tid);
        $transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
        $transaction->setAdditionalInformation('request', json_encode($request));
        $transaction->setAdditionalInformation('response', json_encode($response));
        $transaction->setIsClosed(false);
        $transaction->save();

        return $this;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        //Pega os dados de autorização
        $authorizationTransaction = $payment->getAuthorizationTransaction();

        //Se a transação ainda não foi autorizada
        if (!$authorizationTransaction) 
        {
            //Autoriza a transação
            $authorization = $this->authorize($payment, $amount);
            //Pega os dados de autorização
            $authorizationTransaction = $payment->getAuthorizationTransaction();           
        }

        //Cria uma nova transacao
        $transacao = Mage::getModel('dutra_cielo/webservice_transacao');
        $transacao->setTid($authorizationTransaction->getTxnId()); 

        //Cria um novo cliente
        $cliente = Mage::getModel('dutra_cielo/webservice_cliente', array('numero' => Mage::getStoreConfig('payment/dutra_cielo/affiliationnumber'), 'chave' => Mage::getStoreConfig('payment/dutra_cielo/affiliationkey')));
        $cliente->setAmbiente(Mage::getStoreConfig('payment/dutra_cielo/environment'));
        $cliente->captura($transacao);

        //Carrega a resposta do webservice
        $request = simplexml_load_string($cliente->getXml()->asXML());
        $response = simplexml_load_string($cliente->enviaChamada()->asXML());

        if ($enabledLog) {
            Mage::log($request, null, 'dutra-cielo-request.log', true);
            Mage::log($response, null, 'dutra-cielo-response.log', true);
        }

        //Se a transação não foi capturada
        if ($response->captura->codigo != 6)
        {
            Mage::throwException('Transação não capturada. Verifique os dados e tente novamente. (Cód '. $response->captura->codigo .')');
        }

        //Salva a quantia capturada
        $payment->setCapturedAmount($amount);

        //Registra a transação
        $transaction = Mage::getModel('sales/order_payment_transaction');
        $transaction->setOrderPaymentObject($payment);
        $transaction->setTxnId($authorizationTransaction->getTxnId().'-capture');
        $transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
        $transaction->setAdditionalInformation('request', json_encode($request));
        $transaction->setAdditionalInformation('response', json_encode($response));
        $transaction->setIsClosed(true);
        $transaction->setParentId($authorizationTransaction->getId());
        $transaction->setParentTxnId($authorizationTransaction->getTxnId());
        $transaction->save();

        return $this;
    }

    public function void(Varien_Object $payment)
    {
        //Pega os dados de autorização
        $amount = (float) $payment->getAmountAuthorized();
        $authorizationTransaction = $payment->getAuthorizationTransaction();

        //Cria uma nova transacao
        $transacao = Mage::getModel('dutra_cielo/webservice_transacao');
        $transacao->setTid($authorizationTransaction->getTxnId()); 

        //Cria um novo cliente
        $cliente = Mage::getModel('dutra_cielo/webservice_cliente', array('numero' => Mage::getStoreConfig('payment/dutra_cielo/affiliationnumber'), 'chave' => Mage::getStoreConfig('payment/dutra_cielo/affiliationkey')));
        $cliente->setAmbiente(Mage::getStoreConfig('payment/dutra_cielo/environment'));
        $cliente->cancelamento($transacao);

        //Carrega a resposta do webservice
        $request = simplexml_load_string($cliente->getXml()->asXML());
        $response = simplexml_load_string($cliente->enviaChamada()->asXML());
        
        if ($enabledLog) {
            Mage::log($request, null, 'dutra-cielo-request.log', true);
            Mage::log($response, null, 'dutra-cielo-response.log', true);
        }

        //Se a transação não foi cancelada
        if ((strpos($response->mensagem,'já está cancelada') == false) && $response->cancelamento->codigo != 9)
        {
            Mage::throwException('Transação não cancelada. Verifique os dados e tente novamente. (Cód '. $response->cancelamento->codigo .')');
        }

        //Salva a quantia cancelada
        $payment->setAmountCanceled($amount);

        //Registra a transação
        $transaction = Mage::getModel('sales/order_payment_transaction');
        $transaction->setOrderPaymentObject($payment);
        $transaction->setTxnId($payment->getTransactionId());
        $transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID);
        $transaction->setAdditionalInformation('request', json_encode($request));
        $transaction->setAdditionalInformation('response', json_encode($response));
        $transaction->setIsClosed(true);
        $transaction->setParentId($authorizationTransaction->getId());
        $transaction->setParentTxnId($authorizationTransaction->getTxnId());
        $transaction->save();

        return $this;        
    }

    public function cancel(Varien_Object $payment)
    {
        $this->void($payment);
        return $this;
    }

    public function getInstallments()
    {
        $total = $this->getInfoInstance()->getQuote()->getGrandTotal();
        $portions = array(1 => Mage::helper('payment')->__('À vista - ' . Mage::helper('core')->currency($total, true, false)));
        $maxPortions = (int) Mage::getStoreConfig('payment/dutra_cielo/maxinstallments');
        $minimumPortionAmount = (float) Mage::getStoreConfig('payment/dutra_cielo/mininstallmentsvalue');
        
        for ($i = 2; $i <= $maxPortions; $i++)
        {
            $portionAmount = $total / $i;
            
            if ($portionAmount < $minimumPortionAmount)
            {
                break;
            }

            $portions[$i] = Mage::helper('payment')->__('%dx de %s', $i, Mage::helper('core')->currency($portionAmount, true, false));
        }
        
        return $portions;
    }

}