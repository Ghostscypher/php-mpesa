<?php

namespace HackDelta\Mpesa\Main;

/**
 * Contains tasks that can be done for a C2B transaction 
 */
class MpesaC2B 
{

    protected MpesaConfig $config;

    public function __construct(MpesaConfig $config)
    {
        $this->config = $config;
    }

    public function setConfig(MpesaConfig $config): self
    {

    }

    public function getConfig(): MpesaConfig
    {
        return $this->config;
    }

    public function register(): MpesaResponse
    {

    }

    public function simulate(string $MSISDN, int $amount): MpesaResponse
    {

    }

    public function initiateSTKPush(
        int $amount, 
        string $to, 
        string $account_reference = '', 
        string $timestamp = '', 
        string $description = ''
    ): MpesaResponse 
    {

    }

    public function STKPushQuery(string $checkout_request_id, string $timestamp = ''): MpesaResponse
    {

    }

}
