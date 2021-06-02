<?php

namespace Hackdelta\Mpesa\Main;

use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Extras\Validatable;

/**
 * Contains tasks that can be done for a C2B transaction.
 */
class MpesaC2B
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

    public function registerURL(): MpesaResponse
    {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['register_c2b']
        );

        // Validate that data is correct
        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('confirmation_url', $this->config->getConfirmationURL());
        $this->validateString('validation_url', $this->config->getValidationURL());

        $response = self::$http_client->request(
            $url,
            'POST',
            [
                'ShortCode'       => $this->config->getShortCode(),
                'ResponseType'    => ' ',
                'ConfirmationURL' => $this->config->getConfirmationURL(),
                'ValidationURL'   => $this->config->getValidationURL(),
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

    public function simulate(string $MSISDN, int $amount, $account_reference = ''): MpesaResponse
    {
        if ($this->config->isProductionEnvironment()) {
            throw new MpesaInternalException("This can only work in sandbox");
        }

        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['simulate_c2b']
        );

        // Validate that data is correct
        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('MSISDN', $MSISDN);
        $this->validateInt('amount', $amount, 1);

        $temp = [
            'ShortCode' => $this->config->getShortCode(),
            'CommandID' => $this->config->getIdentifierType() === MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL ?
                            MpesaConstants::MPESA_COMMAND_ID_CUSTOMER_BUY_GOODS_ONLINE : MpesaConstants::MPESA_COMMAND_ID_CUSTOMER_PAYBILL_ONLINE,
            'Amount'        => "{$amount}",
            'Msisdn'        => $MSISDN,
            'BillRefNumber' => '',
        ];

        // Append account number if we are using paybill
        if ($this->config->getIdentifierType() !== MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL) {
            $temp['BillRefNumber'] = $account_reference;
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

    public function initiateSTKPush(
        string $to,
        int $amount,
        string $account_reference = '',
        string $description = 'Description',
        string $timestamp = ''
    ): MpesaResponse {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['stk_push']
        );

        $my_timestamp = trim($timestamp);

        if ($my_timestamp === '') {
            $my_timestamp = date('Ymdhis', time());
        }

        // Validate that data is correct
        $this->validateString('business_short_code', $this->config->getBusinessShortCode());
        $this->validateString('timestamp', $my_timestamp);
        $this->validateString('passkey', $this->config->getPasskey());
        $this->validateInt('amount', $amount, 1);
        $this->validateString('to', $to);
        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('stk_callback_url', $this->config->getSTKCallbackURL());

        $response = self::$http_client->request(
            $url,
            'POST',
            [
                'BusinessShortCode' => $this->config->getBusinessShortCode(),
                'Password'          => $this->config->getPassword($my_timestamp),
                'Timestamp'         => $my_timestamp,
                'TransactionType'   => MpesaConstants::MPESA_COMMAND_ID_CUSTOMER_PAYBILL_ONLINE,
                'Amount'            => $amount,
                'PartyA'            => $to,
                'PartyB'            => $this->config->getShortCode(),
                'PhoneNumber'       => $to,
                'CallBackURL'       => $this->config->getSTKCallbackURL(),
                'AccountReference'  => $account_reference,
                'TransactionDesc'   => $description,
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

    public function STKPushQuery(string $checkout_request_id, string $timestamp = ''): MpesaResponse
    {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['stk_push_query']
        );

        // Validate that data is correct
        $this->validateString('business_short_code', $this->config->getBusinessShortCode());
        $this->validateString('passkey', $this->config->getPasskey());
        $this->validateString('checkout_request_id', $checkout_request_id);

        $my_timestamp = trim($timestamp);

        if ($my_timestamp === '') {
            $my_timestamp = date('Ymdhis', time());
        }

        $response = self::$http_client->request(
            $url,
            'POST',
            [
                'BusinessShortCode' => $this->config->getBusinessShortCode(),
                'Password'          => $this->config->getPassword($my_timestamp),
                'Timestamp'         => $my_timestamp,
                'CheckoutRequestID' => $checkout_request_id,
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

}
