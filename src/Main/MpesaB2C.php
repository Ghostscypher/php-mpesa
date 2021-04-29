<?php

namespace HackDelta\Mpesa\Main;

/**
 * Contains tasks that can be done for a B2C transaction 
 */
class MpesaB2C 
{
    protected MpesaConfig $config;
    protected string $command_id;

    public function __construct(MpesaConfig $config, string $command_id)
    {
        $this->config = $config;
        $this->command_id = $command_id;
    }

    public function setConfig(MpesaConfig $config): self
    {

    }

    public function getConfig(): MpesaConfig
    {
        return $this->config;
    }

    public function setCommandID(string $command_id): self
    {

    }

    public function getCommandID(): string
    {
        return $this->command_id;
    }

    public function send(
        int $amount, 
        string $to,
        string $receiver_identifier_type,
        string $account_reference = '',
        string $remarks = 'remarks',
        string $occasion = '',
    ): MpesaResponse
    {

    }

}