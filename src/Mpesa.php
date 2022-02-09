<?php

namespace Hackdelta\Mpesa;

use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Extras\Validatable;
use Hackdelta\Mpesa\Main\MpesaB2B;
use Hackdelta\Mpesa\Main\MpesaB2C;
use Hackdelta\Mpesa\Main\MpesaC2B;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Main\MpesaHttp;
use Hackdelta\Mpesa\Main\MpesaResponse;

/**
 * The main class that will be instantiated.
 */
class Mpesa
{
    use Validatable;

    protected static ?MpesaHttp $http_client = null;

    protected static ?MpesaB2C $B2C = null;
    protected static ?MpesaC2B $C2B = null;
    protected static ?MpesaB2B $B2B = null;

    protected MpesaConfig $config;

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

        // Change the other classes config to
        $this->B2B()->setConfig($config);
        $this->B2C()->setConfig($config);
        $this->C2B()->setConfig($config);

        return $this;
    }

    public function getConfig(): MpesaConfig
    {
        return $this->config;
    }

    public function C2B(): MpesaC2B
    {
        return self::$C2B === null ?
            self::$C2B = new MpesaC2B($this->config) :
            self::$C2B;
    }

    /**
     * @deprecated - This set of API is no longer supported
     */
    public function B2B(): MpesaB2B
    {
        return self::$B2B === null ?
            self::$B2B = new MpesaB2B($this->config) :
            self::$B2B;
    }

    public function B2C(): MpesaB2C
    {
        return self::$B2C === null ?
            self::$B2C = new MpesaB2C($this->config) :
            self::$B2C;
    }

    // Account balance query
    // Optional remarks sent with the request
    public function checkBalance(string $remarks = 'remarks'): MpesaResponse
    {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['account_balance']
        );

        // Validate that data is correct
        $this->validateString('initiator_name', $this->config->getInitiator());
        $this->validateString('security_credential', $this->config->getSecurityCredential());
        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('queue_timeout_url', $this->config->getQueueTimeoutURL());
        $this->validateString('result_url', $this->config->getResultURL());

        $this->validateString('remarks', $remarks);

        $response = self::$http_client->request(
            'POST',
            $url,
            [
                'Initiator'          => $this->config->getInitiator(),
                'SecurityCredential' => $this->config->getSecurityCredential(),
                'CommandID'          => MpesaConstants::MPESA_COMMAND_ID_ACCOUNT_BALANCE,
                'PartyA'             => $this->config->getShortCode(),
                'IdentifierType'     => $this->config->getIdentifierType(),
                'Remarks'            => $remarks,
                'QueueTimeOutURL'    => $this->config->getQueueTimeoutURL(),
                'ResultURL'          => $this->config->getResultURL(),
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

    // Transaction status, check the transaction status of an mpesa code
    // transaction_id: This is the mpesa code
    // Remarks: comments sent along with the transaction
    // Occasion: additional data sent with the transaction
    public function checkTransactionStatus(
        string $transaction_id,
        string $remarks = 'remarks',
        string $occasion = ' '
    ): MpesaResponse {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['transaction_status']
        );

        // Validate that data is correct
        $this->validateString('initiator_name', $this->config->getInitiator());
        $this->validateString('security_credential', $this->config->getSecurityCredential());
        $this->validateString('transaction_id', $transaction_id);
        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('queue_timeout_url', $this->config->getQueueTimeoutURL());
        $this->validateString('result_url', $this->config->getResultURL());

        $this->validateString('remarks', $remarks);

        $response = self::$http_client->request(
            'POST',
            $url,
            [
                'Initiator'          => $this->config->getInitiator(),
                'SecurityCredential' => $this->config->getSecurityCredential(),
                'CommandID'          => MpesaConstants::MPESA_COMMAND_ID_TRANSACTION_STATUS_QUERY,
                'TransactionID'      => $transaction_id,
                'PartyA'             => $this->config->getShortCode(),
                'IdentifierType'     => $this->config->getIdentifierType(),
                'Remarks'            => $remarks,
                'QueueTimeOutURL'    => $this->config->getQueueTimeoutURL(),
                'ResultURL'          => $this->config->getResultURL(),
                'Occasion'           => $occasion,
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

    /**
     * Initiate reversal request
     * transaction_id: The Mpesa code.
     * amount: The amount that is being reversed
    */
    public function reverseTransaction(
        string $transaction_id,
        int $amount,
        string $remarks = 'remarks',
        string $occasion = ''
    ): MpesaResponse {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['reversal']
        );

        // Validate that data is correct
        $this->validateString('initiator_name', $this->config->getInitiator());
        $this->validateString('security_credential', $this->config->getSecurityCredential());
        $this->validateString('transaction_id', $transaction_id);
        $this->validateInt('amount', $amount, 1);

        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('queue_timeout_url', $this->config->getQueueTimeoutURL());
        $this->validateString('result_url', $this->config->getResultURL());

        $this->validateString('remarks', $remarks);

        $response = self::$http_client->request(
            'POST',
            $url,
            [
                'Initiator'              => $this->config->getInitiator(),
                'SecurityCredential'     => $this->config->getSecurityCredential(),
                'CommandID'              => MpesaConstants::MPESA_COMMAND_ID_TRANSACTION_REVERSAL,
                'TransactionID'          => $transaction_id,
                'Amount'                 => $amount,
                'ReceiverParty'          => $this->config->getShortCode(),
                'RecieverIdentifierType' => MpesaConstants::MPESA_IDENTIFIER_TYPE_REVERSAL,
                'Remarks'                => $remarks,
                'QueueTimeOutURL'        => $this->config->getQueueTimeoutURL(),
                'ResultURL'              => $this->config->getResultURL(),
                'Occasion'               => $occasion,
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

    /**
     * Register pull request endpoint.
     */
    public function pullRequestRegisterURL(): MpesaResponse
    {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['pull_transaction_register']
        );

        // Validate that data is correct
        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('nominated_number', $this->config->getOrganizationMSISDN());
        $this->validateString('pull_callback_url', $this->config->getPullCallbackURL());

        $response = self::$http_client->request(
            'POST',
            $url,
            [
                'ShortCode'       => $this->config->getShortCode(),
                'RequestType'     => MpesaConstants::MPESA_REQUEST_TYPE_PULL,
                'NominatedNumber' => $this->config->getOrganizationMSISDN(),
                'CallBackURL'     => $this->config->getPullCallbackURL(),
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

    /**
     * Perform a pull request query.
     *
     * @param string $start_date: The start period of the missing transactions in the
     *                            format of 2019-07-31 20:35:21 or 2019-07-31 19:00
     * @param string $end_date:   The end of the period for the missing transactions in the
     *                            format of 2019-07-31 20:35:21 or 2019-07-31 22:35
     * @param string $offset:     Starts from 0. The service uses offset as opposed to page numbers.
     *                            The OFF SET value allows you to specify which row to start from retrieving
     *                            data. Suppose you wanted to show results 101-200. With the
     *                            OFFSET keyword you type the (page number/index/offset value) 100.
     */
    public function pullRequestQuery(string $start_date, string $end_date, int $offset = 0): MpesaResponse
    {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['pull_transaction_query']
        );

        // Validate that data is correct
        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('start_date', $start_date);
        $this->validateString('end_date', $end_date);
        $this->validateInt('offset', $offset, 0);

        $response = self::$http_client->request(
            'POST',
            $url,
            [
                'ShortCode'   => $this->config->getShortCode(),
                'StartDate'   => $start_date,
                'EndDate'     => $end_date,
                'OffSetValue' => $offset,
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }
}
