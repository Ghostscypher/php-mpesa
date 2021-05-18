<?php

namespace Hackdelta\Mpesa\Main;

use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Extras\Validatable;

/**
 * Contains tasks that can be done for a C2B transaction 
 */
class MpesaC2B 
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

    public function registerURL(): MpesaResponse
    {
        $url = sprintf(
            "%s%s", 
            $this->config->getBaseURL(), 
            MpesaConstants::MPESA_URIS['register_c2b']
        );

        // Validate that data is correct
        $this->validateString( 'short_code', $this->config->getShortCode() );
        $this->validateString( 'confirmation_url', $this->config->getConfirmationURL() );
        $this->validateString( 'validation_url', $this->config->getValidationURL() );

        $response = self::$http_client->request(
            $url,
            'POST',
            [
                'ShortCode' => $this->config->getShortCode(),
                'ResponseType' => ' ',
                'ConfirmationURL' => $this->config->getConfirmationURL(),
                'ValidationURL' => $this->config->getValidationURL(),
            ],
            [
                'Authorization' => sprintf("Bearer %s", $this->config->getAuth()->getToken() )
            ]
        );

        return $response;
    }

    public function simulate(string $MSISDN, int $amount, $account_reference = ''): MpesaResponse
    {
        $url = sprintf(
            "%s%s", 
            $this->config->getBaseURL(), 
            MpesaConstants::MPESA_URIS['simulate_c2b']
        );

        // Validate that data is correct
        $this->validateString( 'short_code', $this->config->getShortCode() );
        $this->validateString( 'MSISDN', $MSISDN );
        $this->validateInt('amount', $amount, 1);

        $temp = [
            'ShortCode' => $this->config->getShortCode(),
            'CommandID' => $this->config->getIdentifierType() === MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL ? 
                            MpesaConstants::MPESA_COMMAND_ID_CUSTOMER_BUY_GOODS_ONLINE : MpesaConstants::MPESA_COMMAND_ID_CUSTOMER_PAYBILL_ONLINE,
            'Amount' => "{$amount}",
            'Msisdn' => $MSISDN,
            'BillRefNumber' => '',
        ];

        // Append account number if we are using paybill
        if($this->config->getIdentifierType() !== MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL) {
            $temp['BillRefNumber'] = $account_reference;
        }

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

    public function initiateSTKPush(
        string $to,
        int $amount, 
        string $account_reference = '',
        string $description = 'Description',
        string $timestamp = ''
    ): MpesaResponse 
    {
        $url = sprintf(
            "%s%s", 
            $this->config->getBaseURL(), 
            MpesaConstants::MPESA_URIS['stk_push']
        );
        
        $my_timestamp = trim($timestamp);

        if($my_timestamp === ''){
            $my_timestamp = date("Ymdhis", time() );
        }

        // Validate that data is correct
        $this->validateString( 'business_short_code', $this->config->getBusinessShortCode() );
        $this->validateString('timestamp', $my_timestamp);
        $this->validateString( 'passkey', $this->config->getPasskey() );
        $this->validateInt( 'amount', $amount, 1 );
        $this->validateString( 'to', $to );
        $this->validateString( 'short_code', $this->config->getShortCode() );
        $this->validateString( 'stk_callback_url', $this->config->getSTKCallbackURL() );

        $response = self::$http_client->request(
            $url,
            'POST',
            [
                'BusinessShortCode' => $this->config->getBusinessShortCode(),
                'Password' => $this->config->getPassword($my_timestamp),
                'Timestamp' => $my_timestamp,
                'TransactionType' => MpesaConstants::MPESA_COMMAND_ID_CUSTOMER_PAYBILL_ONLINE,
                'Amount' => $amount,
                'PartyA' => $to,
                'PartyB' => $this->config->getShortCode(),
                'PhoneNumber' => $to,
                'CallBackURL' => $this->config->getSTKCallbackURL(),
                'AccountReference' => $account_reference,
                'TransactionDesc' => $description,
            ],
            [
                'Authorization' => sprintf("Bearer %s", $this->config->getAuth()->getToken() )
            ]
        );

        return $response;
    
    }

    public function STKPushQuery(string $checkout_request_id, string $timestamp = ''): MpesaResponse
    {
        $url = sprintf(
            "%s%s", 
            $this->config->getBaseURL(), 
            MpesaConstants::MPESA_URIS['stk_push_query']
        );

        // Validate that data is correct
        $this->validateString( 'business_short_code', $this->config->getBusinessShortCode() );
        $this->validateString( 'passkey', $this->config->getPasskey() );
        $this->validateString( 'checkout_request_id', $checkout_request_id );

        $my_timestamp = trim($timestamp);

        if($my_timestamp === ''){
            $my_timestamp = date("Ymdhis", time() );
        }

        $response = self::$http_client->request(
            $url,
            'POST',
            [
                'BusinessShortCode' => $this->config->getBusinessShortCode(),
                'Password' => $this->config->getPassword($my_timestamp),
                'Timestamp' => $my_timestamp,
                'CheckoutRequestID' => $checkout_request_id,
            ],
            [
                'Authorization' => sprintf("Bearer %s", $this->config->getAuth()->getToken() )
            ]
        );

        return $response;
    }

    /** 
     * Register pull request endpoint
     */
    public function pullRequestRegisterURL(): MpesaResponse
    {
        $url = sprintf(
            "%s%s", 
            $this->config->getBaseURL(), 
            MpesaConstants::MPESA_URIS['pull_transaction_register']
        );

        // Validate that data is correct
        $this->validateString( 'short_code', $this->config->getShortCode() );
        $this->validateString( 'nominated_number', $this->config->getOrganizationMSISDN() );
        $this->validateString( 'passkey', $this->config->getPasskey() );
        $this->validateString( 'pull_callback_url', $this->config->getPullCallbackURL() );

        $response = self::$http_client->request(
            'POST',
            $url,
            [
                'ShortCode' => $this->config->getShortCode(),
                'RequestType' => MpesaConstants::MPESA_REQUEST_TYPE_PULL,
                'NominatedNumber' => $this->config->getOrganizationMSISDN(),
                'CallBackURL' => $this->config->getPullCallbackURL(),
            ],
            [
                'Authorization' => sprintf("Bearer %s", $this->config->getAuth()->getToken() )
            ]
        );

        return $response;
    } 

    /**
     * Perform a pull request query
     * @param string $start_date: The start period of the missing transactions in the 
     *      format of 2019-07-31 20:35:21 or 2019-07-31 19:00
     * @param string $end_date: The end of the period for the missing transactions in the 
     *          format of 2019-07-31 20:35:21 or 2019-07-31 22:35
     * @param string $offset: Starts from 0. The service uses offset as opposed to page numbers. 
     *      The OFF SET value allows you to specify which row to start from retrieving 
     *      data. Suppose you wanted to show results 101-200. With the 
     *      OFFSET keyword you type the (page number/index/offset value) 100.
    */
    public function pullRequestQuery(string $start_date, string $end_date, int $offset = 0): MpesaResponse
    {
        $url = sprintf(
            "%s%s", 
            $this->config->getBaseURL(), 
            MpesaConstants::MPESA_URIS['pull_transaction_query']
        );

        // Validate that data is correct
        $this->validateString( 'short_code', $this->config->getShortCode() );
        $this->validateString( 'start_date', $start_date );
        $this->validateString( 'end_date', $end_date );
        $this->validateInt( 'offset', $offset, 0 );

        $response = self::$http_client->request(
            $url,
            'POST',
            [
                'ShortCode' => $this->config->getShortCode(),
                'StartDate' => $start_date,
                'EndDate' => $end_date,
                'OffSetValue' => $offset,
            ],
            [
                'Authorization' => sprintf("Bearer %s", $this->config->getAuth()->getToken() )
            ]
        );

        return $response;
    }

}
