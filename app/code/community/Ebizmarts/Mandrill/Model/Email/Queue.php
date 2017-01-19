<?php
/**
 *
 * @category Ebizmarts
 * @package magemonkey1922
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 2/16/16 12:46 PM
 * @file: Queue.php
 */
class Ebizmarts_Mandrill_Model_Email_Queue extends Mage_Core_Model_Email_Queue
{
    /**
     * Send all messages in a queue via manrill
     *
     * @return Mage_Core_Model_Email_Queue
     */
    public function send()
    {
        /** @var $collection Mage_Core_Model_Resource_Email_Queue_Collection */
        $collection = Mage::getModel('core/email_queue')->getCollection()
            ->addOnlyForSendingFilter()
            ->setPageSize(self::MESSAGES_LIMIT_PER_CRON_RUN)
            ->setCurPage(1)
            ->load();
        /** @var $message Mage_Core_Model_Email_Queue */
        foreach ($collection as $message) {
            if ($message->getId()) {
                if ($message->getEntityType() == 'order') {
                    $order = Mage::getModel('sales/order')->load($message->getEntityId());
                    $storeId = $order->getStoreId();
                    if (Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE, $storeId)) {
                        $parameters = new Varien_Object($message->getMessageParameters());
                        $mailer = $this->getMail($storeId);
                        $mandrill = array(
                            'subject' => $parameters->getSubject(),
                            'to' => array(),
                            'from_email' => $parameters->getFromEmail(),
                            'from_name' => $parameters->getFromName(),
                            'headers' => $mailer->getHeaders(),
                            'html' => ($parameters->getIsPlain() ? "" : $message->getMessageBody()),
                            'text' => ($parameters->getIsPlain() ? $message->getMessageBody() : ""),
                        );
                        foreach ($message->getRecipients() as $recipient) {
                            list($email, $name, $type) = $recipient;
                            $mandrill['to'][] = array(
                                'type' => ($type == self::EMAIL_TYPE_BCC ? "bcc" : "to"),
                                'email' => $email,
                                'name' => $name
                            );
                        }
                        if ($parameters->getReplyTo() !== null) {
                            $mandrill['headers'] = array_merge($mandrill['headers'], array('Reply-To' => $parameters->getReplyTo()));
                        }
                        if ($parameters->getReturnTo() !== null) {
                            $mailer->setReturnPath($parameters->getReturnTo());
                        }
                        try {
                            $mailer->messages->send($mandrill);
                        } catch (Exception $e) {
                            Mage::logException($e);
                        }
                        unset($mailer);
                        unset($mandrill);
                        $message->setProcessedAt(Varien_Date::formatDate(true));
                        $message->save();
                    }else{
                        $parameters = new Varien_Object($message->getMessageParameters());
                        if ($parameters->getReturnPathEmail() !== null) {
                            $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $parameters->getReturnPathEmail());
                            Zend_Mail::setDefaultTransport($mailTransport);
                        }

                        $mailer = new Zend_Mail('utf-8');
                        foreach ($message->getRecipients() as $recipient) {
                            list($email, $name, $type) = $recipient;
                            switch ($type) {
                                case self::EMAIL_TYPE_BCC:
                                    $mailer->addBcc($email, '=?utf-8?B?' . base64_encode($name) . '?=');
                                    break;
                                case self::EMAIL_TYPE_TO:
                                case self::EMAIL_TYPE_CC:
                                default:
                                    $mailer->addTo($email, '=?utf-8?B?' . base64_encode($name) . '?=');
                                    break;
                            }
                        }

                        if ($parameters->getIsPlain()) {
                            $mailer->setBodyText($message->getMessageBody());
                        } else {
                            $mailer->setBodyHTML($message->getMessageBody());
                        }

                        $mailer->setSubject('=?utf-8?B?' . base64_encode($parameters->getSubject()) . '?=');
                        $mailer->setFrom($parameters->getFromEmail(), $parameters->getFromName());
                        if ($parameters->getReplyTo() !== null) {
                            $mailer->setReplyTo($parameters->getReplyTo());
                        }
                        if ($parameters->getReturnTo() !== null) {
                            $mailer->setReturnPath($parameters->getReturnTo());
                        }

                        try {
                            $mailer->send();
                        } catch (Exception $e) {
                            Mage::logException($e);
                        }

                        unset($mailer);
                        $message->setProcessedAt(Varien_Date::formatDate(true));
                        $message->save();
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param $storeId
     * @return Mandrill_Message|Zend_Mail
     */
    public function getMail($storeId)
    {
        if (!Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE, $storeId)) {
            return parent::getMail();
        }
        if ($this->_mail) {
            return $this->_mail;
        } else {
            Mage::helper('ebizmarts_mandrill')->log("store: $storeId API: " . Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::APIKEY, $storeId));
            $this->_mail = new Mandrill_Message(Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::APIKEY, $storeId));
            return $this->_mail;
        }
    }
}
