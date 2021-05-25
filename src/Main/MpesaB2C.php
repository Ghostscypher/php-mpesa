<?php

namespace Hackdelta\Mpesa\Main;

use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Extras\Validatable;

/**
 * Contains tasks that can be done for a B2C transaction 
 */
class MpesaB2C 
{

    use Validatable;

    protected MpesaConfig $config;

    protected static ?MpesaHttp $http_client = null;

    public function __construct(MpesaConfig $config)
    {
        $this->config = $config;

        if( self::$http_client === null ) { self::$http_client = new MpesaHttp($this->config); }

    }

    public function setConfig(MpesaConfig $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig(): MpesaConfig
    {
        return $this->config;
    }

    public function send(
        int $amount, 
        string $to,
        string $command_id,
        string $remarks = 'remarks',
        string $occasion = ''
    ): MpesaResponse 
    {
        $url = sprintf(
            "%s%s", 
            $this->config->getBaseURL(), 
            MpesaConstants::MPESA_URIS['payment_request_b2c']
        );

        // Validate that data is correct
        $this->validateString( 'initiator_name', $this->config->getInitiator() );
        $this->validateString( 'security_credential', $this->config->getSecurityCredential() );
        
        // $this->validateArray( 'command_id', $command_id, [
        //     MpesaConstants::MPESA_COMMAND_ID_BUSINESS_PAYMENT,
        //     MpesaConstants::MPESA_COMMAND_ID_SALARY_PAYMENT,
        //     MpesaConstants::MPESA_COMMAND_ID_PROMOTION_PAYMENT,
        // ]);

        $this->validateInt( 'amount', $amount, 1 );
        $this->validateString('short_code', $this->config->getShortCode() );
        $this->validateString('to', $to);
        $this->validateString( 'queue_timeout_url', $this->config->getQueueTimeoutURL() );
        $this->validateString( 'result_url', $this->config->getResultURL() );

        $this->validateString( 'remarks', $remarks );

        $temp = [
            'InitiatorName' => $this->config->getInitiator(),
            'SecurityCredential' => $this->config->getSecurityCredential(),
            'CommandID' => $command_id,
            'PartyA' => $this->config->getShortCode(),
            'PartyB' => $to,
            'Amount' => $amount,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $this->config->getQueueTimeoutURL(),
            'ResultURL' => $this->config->getResultURL(),
            'Occasion' => $occasion,
        ];

        $response = self::$http_client->request(
            $url,
            'POST',
            $temp,
            [
                'Authorization' => sprintf("Bearer %s", $this->config->getAuth()->getToken() )
            ]
        );

        return $response;
        
    }

}