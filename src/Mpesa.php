<?php

namespace Hackdelta\Mpesa;

use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Extras\Validatable;
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
    protected MpesaConfig $config;

    /**
     * Constructor
     * @param array|MpesaConfig $config The config to pass to this class instance
     */
    public function __construct($config)
    {
        $this->setConfig($config);

        if (self::$http_client === null) {
            self::$http_client = new MpesaHttp($this->config);
        }
    }

    /**
     * Change the config of this instance
     * @param array|MpesaConfig $config - The config to pass to this class instance
     *
     * @return self
     */
    public function setConfig($config): self
    {
        if (is_array($config)) {
            $config = new MpesaConfig($config);
        }

        if (! $config instanceof MpesaConfig) {
            throw new MpesaInternalException("Config must be either array or an instance of MpesaConfig class");
        }

        $this->config = $config;

        return $this;
    }

    /**
     * @return MpesaConfig the config class for this instance
     */
    public function getConfig(): MpesaConfig
    {
        return $this->config;
    }

    /**
     * Account balance query - Check for mpesa balance
     * @param string $remarks Optional remarks sent with the request
     */
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

    /**
     * Check the status of a transaction using either transaction ID or conversation ID
     *
     * Transaction status, check the transaction status of an mpesa code
     * @param string $transaction_id This is the mpesa code
     * @param string $remarks comments sent along with the transaction
     * @param string $occasion additional data sent with the transaction
     * @param string $original_conversation_id You can use this instead of transaction id
     */
    public function checkTransactionStatus(
        string $transaction_id,
        string $remarks = 'remarks',
        string $occasion = ' ',
        string $original_conversation_id = ''
    ): MpesaResponse {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['transaction_status']
        );

        // Validate that data is correct
        $this->validateString('initiator_name', $this->config->getInitiator());
        $this->validateString('security_credential', $this->config->getSecurityCredential());
        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('queue_timeout_url', $this->config->getQueueTimeoutURL());
        $this->validateString('result_url', $this->config->getResultURL());

        $this->validateString('remarks', $remarks);

        if (empty($original_conversation_id) && empty($original_conversation_id)) {
            $this->validateString('transaction_id', $transaction_id);
        } elseif (empty($transaction_id) && ! empty($original_conversation_id)) {
            $this->validateString('original_conversation_id', $transaction_id);
        }

        $data = [
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
        ];

        if (! empty($original_conversation_id)) {
            $data += ['OriginalConversationID' => $original_conversation_id];
        }

        $response = self::$http_client->request(
            'POST',
            $url,
            $data,
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

    /**
     * Initiate reversal request
     *
     * @param string $transaction_id The Mpesa code.
     * @param int $amount The amount that is being reversed
     * @param string $remarks optional remarks sent with this request
     * @param string $occasion optional value sent with this request
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

    /**
     * Send money from business to business
     *
     * @deprecated This API is no longer supported, kept here for completeness of the library
     *
     * @param int $amount The amount to send
     * @param string $to The shortcode to send the money to
     * @param string $command_id command ID for this request check daraja documentation for supported commands
     * @param string $receiver_identifier_type type of the reciever
     * @param string $account_reference optional value for account number
     * @param string $remarks optional remarks sent with the request
     */
    public function sendB2B(
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
            'POST',
            $url,
            $temp,
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

    /**
     * Send money from B2C shortcode to client
     *
     * @param int $amount The amount to send
     * @param string $to The shortcode to send the money to
     * @param string $command_id command ID for this request check daraja documentation for supported commands
     * @param string $remarks optional remarks sent with the request
     * @param string $occasion optional value sent with the request
     */
    public function sendB2C(
        int $amount,
        string $to,
        string $command_id,
        string $remarks = 'remarks',
        string $occasion = ''
    ): MpesaResponse {
        $url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['payment_request_b2c']
        );

        // Validate that data is correct
        $this->validateString('initiator_name', $this->config->getInitiator());
        $this->validateString('security_credential', $this->config->getSecurityCredential());

        // $this->validateArray( 'command_id', $command_id, [
        //     MpesaConstants::MPESA_COMMAND_ID_BUSINESS_PAYMENT,
        //     MpesaConstants::MPESA_COMMAND_ID_SALARY_PAYMENT,
        //     MpesaConstants::MPESA_COMMAND_ID_PROMOTION_PAYMENT,
        // ]);

        $this->validateInt('amount', $amount, 1);
        $this->validateString('short_code', $this->config->getShortCode());
        $this->validateString('to', $to);
        $this->validateString('queue_timeout_url', $this->config->getQueueTimeoutURL());
        $this->validateString('result_url', $this->config->getResultURL());

        $this->validateString('remarks', $remarks);

        $temp = [
            'InitiatorName'      => $this->config->getInitiator(),
            'SecurityCredential' => $this->config->getSecurityCredential(),
            'CommandID'          => $command_id,
            'PartyA'             => $this->config->getShortCode(),
            'PartyB'             => $to,
            'Amount'             => $amount,
            'Remarks'            => $remarks,
            'QueueTimeOutURL'    => $this->config->getQueueTimeoutURL(),
            'ResultURL'          => $this->config->getResultURL(),
            'Occasion'           => $occasion,
        ];

        $response = self::$http_client->request(
            'POST',
            $url,
            $temp,
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

    /**
     * Register C2B validation and confirmation callbacks
     */
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
            'POST',
            $url,
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

    /**
     * Simulate a transaction on sandbox environment
     *
     * @param string $MSISDN phone number
     * @param int $amount amount to simulate
     * @param string $account_reference the account number for this simulated transaction
     */
    public function simulate(string $MSISDN, int $amount, $account_reference = ''): MpesaResponse
    {
        if ($this->config->isProductionEnvironment()) {
            throw new MpesaInternalException('This can only work in sandbox');
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
            'POST',
            $url,
            $temp,
            [
                'Authorization' => sprintf('Bearer %s', $this->config->getAuth()->getToken()),
            ]
        );

        return $response;
    }

    /**
     * Alias to initiateSTKPush
     *
     * @param string $to phone number to send STK request to
     * @param int $amount amount to request
     * @param string $account_reference account number
     * @param string $description description to accompany the request
     * @param string $timestamp custom timestam in the format 'yyyymmddhhiiss' e.g.20220110232005
     *
     * @link STKPushQuery
     */
    public function STKPush(
        string $to,
        int $amount,
        string $account_reference = '',
        string $description = 'Description',
        string $timestamp = ''
    ): MpesaResponse {
        return $this->initiateSTKPush($to, $amount, $account_reference, $description, $timestamp);
    }
    /**
     * Send STK push query
     *
     * @param string $to phone number to send STK request to
     * @param int $amount amount to request
     * @param string $account_reference account number
     * @param string $description description to accompany the request
     * @param string $timestamp custom timestam in the format 'yyyymmddhhiiss' e.g. 20220110232005
     */
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
            'POST',
            $url,
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

    /**
     * Check on the status of an STK push query
     *
     * @param string $checkout_request_id The checkout request id received after initiating an STK push query
     * @param string $timestamp Timestamp associated with the transaction in the format 'yyyymmddhhiiss'
     */
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
            'POST',
            $url,
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
