<?php

namespace OCSoftwarePL\SmsApiBundle\Sms;

use OCSoftwarePL\SmsApiBundle\Sms\DTO\Sms;
use SMSApi\Api\Response\MessageResponse;
use SMSApi\Api\SmsFactory;
use SMSApi\Client;
use SMSApi\Proxy\Http\Native;

class SmsApiPlSender
{
    private $config = [];
    private $senderName;
    private $api = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->senderName = $config['default_sender_name'];
    }

    protected function getApi()
    {
        if (null === $this->api) {
            $proxy = null;

            if (false !== $this->config['proxy']) {
                $proxy = new Native($this->config['proxy']);
            }

            $this->api = new SmsFactory($proxy);

            $client = new Client($this->config['login']);
            $client->setPasswordRaw($this->config['password']);

            $this->api->setClient($client);
        }

        return $this->api;
    }

    private function getSendAction(Sms $sms)
    {
        $action = $this->getApi()->actionSend();
        $action->setText($sms->msg);
        $action->setTo($sms->phone);
        $action->setSender($sms->sender ?: $this->senderName);

        return $action;
    }

    /**
     * @param Sms $sms
     * @return MessageResponse[]
     * @throws \Exception
     */
    public function sendSms(Sms $sms)
    {
        try {
            $action = $this->getSendAction($sms);
            $result = $action->execute();
            return $result->getList();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Sms $sms
     * @return MessageResponse[]
     * @throws \Exception
     */
    public function sendFastSms(Sms $sms)
    {
        try {
            $action = $this->getSendAction($sms);
            $action->setFast(true);

            $result = $action->execute();
            return $result->getList();

        } catch (\Exception $e) {
            throw $e;
        }
    }

}