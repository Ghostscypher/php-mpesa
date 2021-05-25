<?php

namespace Hackdelta\Mpesa\Main;

use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Extras\Validatable;

/**
 * Contains actions that can be done for a B2B transaction.
 */
class MpesaB2B
{
    use Validatable;

    protected MpesaConfig $config;

    protected static ?MpesaHttp $http_client = null;

    public function __construct(MpesaConfig $config)
    {
        $this->config = $config;

        if (self::$http_client === null) {
            self::$http_client = new MpesaHttp($this->config);
        }
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
        string $receiver_identifier_type,
        string $account_reference = '',
        string $remarks = 'remarks'
    ): MpesaResponse {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['payment_request_b2b']
        );

        // Validate that data is correct
        $this->validateString('initiator_name', $this->config->getInitiator());
        $this->validateString('security_credential', $this->config->getSecurityCredential());

        // $this->validateArray('command_id', $command_id, [
        //     MpesaConstants::MPESA_COMMAND_ID_BUSINESS_PAY_BILL,
        //     MpesaConstants::MPESA_COMMAND_ID_MERCHANT_TO_MERCHANT_TRANSFER,
        //     MpesaConstants::MPESA_COMMAND_ID_MERCHANT_FROM_MERCHANT_TO_WORKING,
        //     MpesaConstants::MPESA_COMMAND_ID_MERCHANT_TO_MMF,
        //     MpesaConstants::MPESA_COMMAND_ID_AGENCY_FLOAT_ADVANCE,
        // ]);

        $this->validateArray('reciever_identifier_type', $receiver_identifier_type, [
            MpesaConstants::MPESA_IDENTIFIER_TYPE_MSISDN,
            MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL,
            MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL,
            MpesaConstants::MPESA_IDENTIFIER_TYPE_SHORTCODE,
        ]);

        $this->validateInt('amount', $amount, 1);
        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('to', $to);
        $this->validateString('queue_timeout_url', $this->config->getQueueTimeoutURL());
        $this->validateString('result_url', $this->config->getResultURL());

        $this->validateString('remarks', $remarks);

        $temp = [
            'Initiator'              => $this->config->getInitiator(),
            'SecurityCredential'     => $this->config->getSecurityCredential(),
            'CommandID'              => $command_id,
            'SenderIdentifierType'   => $this->config->getIdentifierType(),
            'RecieverIdentifierType' => $receiver_identifier_type,
            'Amount'                 => $amount,
            'PartyA'                 => $this->config->getShortCode(),
            'PartyB'                 => $to,
            'Remarks'                => $remarks,
            'QueueTimeOutURL'        => $this->config->getQueueTimeoutURL(),
            'ResultURL'              => $this->config->getResultURL(),
        ];

        if ($command_id === MpesaConstants::MPESA_COMMAND_ID_BUSINESS_PAY_BILL) {
            $this->validateString('account_reference', $account_reference);

            $temp['AccountReference'] = $account_reference;
        }

        $response = self::$http_client->request(
            $url,
            'POST',
            $temp,
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }
}
