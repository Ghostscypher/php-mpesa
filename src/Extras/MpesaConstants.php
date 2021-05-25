<?php

namespace Hackdelta\Mpesa\Extras;

/**
 * This class stores constants that will be used throughout the mpesa app.
 */
class MpesaConstants
{
    /**
     * Defines the mpesa endpoints.
     */
    public const MPESA_URIS = [
        // Base uris
        'base_uri'         => 'https://api.safaricom.co.ke',
        'sandbox_base_uri' => 'https://sandbox.safaricom.co.ke',

        // Auth endpoint
        'generate_token' => '/oauth/v1/generate?grant_type=client_credentials',

        // Payment requests
        'payment_request_b2c' => '/mpesa/b2c/v1/paymentrequest',
        'payment_request_b2b' => '/mpesa/b2b/v1/paymentrequest',

        // Registering callback/webhook uris
        'register_c2b' => '/mpesa/c2b/v1/registerurl',
        'simulate_c2b' => '/mpesa/c2b/v1/simulate',

        // Transaction queries
        'account_balance'    => '/mpesa/accountbalance/v1/query',
        'transaction_status' => '/mpesa/transactionstatus/v1/query',

        // Reversal
        'reversal' => '/mpesa/reversal/v1/request',

        // Stk related
        'stk_push'       => '/mpesa/stkpush/v1/processrequest',
        'stk_push_query' => '/mpesa/stkpushquery/v1/query',

        // Pull transactions API
        'pull_transaction_register' => '/pulltransactions/v1/register',
        'pull_transaction_query'    => '/pulltransactions/v1/query',

    ];

    // Identifier types
    public const MPESA_IDENTIFIER_TYPE_MSISDN = '1';
    public const MPESA_IDENTIFIER_TYPE_TILL = '2';
    public const MPESA_IDENTIFIER_TYPE_PAYBILL = '4';
    public const MPESA_IDENTIFIER_TYPE_SHORTCODE = '4';

    // Command ids
    public const MPESA_COMMAND_ID_TRANSACTION_REVERSAL = 'TransactionReversal';
    public const MPESA_COMMAND_ID_SALARY_PAYMENT = 'SalaryPayment';
    public const MPESA_COMMAND_ID_BUSINESS_PAYMENT = 'BusinessPayment';
    public const MPESA_COMMAND_ID_PROMOTION_PAYMENT = 'PromotionPayment';
    public const MPESA_COMMAND_ID_ACCOUNT_BALANCE = 'AccountBalance';
    public const MPESA_COMMAND_ID_CUSTOMER_PAYBILL_ONLINE = 'CustomerPayBillOnline';
    public const MPESA_COMMAND_ID_CUSTOMER_BUY_GOODS_ONLINE = 'CustomerBuyGoodsOnline';
    public const MPESA_COMMAND_ID_TRANSACTION_STATUS_QUERY = 'TransactionStatusQuery';
    public const MPESA_COMMAND_ID_CHECK_IDENTITY = 'CheckIdentity';
    public const MPESA_COMMAND_ID_BUSINESS_PAY_BILL = 'BusinessPayBill';
    public const MPESA_COMMAND_ID_BUSINESS_PAY_BUY_GOODS = 'BusinessBuyGoods';
    public const MPESA_COMMAND_ID_DISBURSE_FUNDS_TO_BUSINESS = 'DisburseFundsToBusiness';
    public const MPESA_COMMAND_ID_BUSINESS_TO_BUSINESS_TRANSFER = 'BusinessToBusinessTransfer';
    public const MPESA_COMMAND_ID_TRANSFER_FROM_MMF_TO_UTILITY = 'BusinessTransferFromMMFToUtility';
    public const MPESA_COMMAND_ID_MERCHANT_TO_MERCHANT_TRANSFER = 'MerchantToMerchantTransfer';
    public const MPESA_COMMAND_ID_MERCHANT_FROM_MERCHANT_TO_WORKING = 'MerchantTransferFromMerchantToWorking';
    public const MPESA_COMMAND_ID_MERCHANT_TO_MMF = 'MerchantServicesMMFAccountTransfer';
    public const MPESA_COMMAND_ID_AGENCY_FLOAT_ADVANCE = 'AgencyFloatAdvance';

    // Http codes
    // 2xx series
    public const MPESA_HTTP_OK = 200;

    // 4xx series
    public const MPESA_HTTP_BAD_REQUEST = 400;
    public const MPESA_HTTP_UNAUTHORIZED = 401;
    public const MPESA_HTTP_FORBIDDEN = 403;
    public const MPESA_HTTP_NOT_FOUND = 404;
    public const MPESA_HTTP_METHOD_NOT_ALLOWED = 405;
    public const MPESA_HTTP_NOT_ACCEPTABLE = 406;
    public const MPESA_HTTP_TOO_MANY_REQUESTS = 429;

    // 5xx series
    public const MPESA_HTTP_INTERNAL_SERVER_ERROR = 500;
    public const MPESA_HTTP_SERVICE_UNAVAILABLE = 503;

    // Gateway to client status code
    public const MPESA_GATEWAY_TO_CLIENT_SUCCESS = 0;

    // Fund related
    public const MPESA_GATEWAY_TO_CLIENT_INSUFFICIENT_FUNDS = 1;
    public const MPESA_GATEWAY_TO_CLIENT_LESS_THAN_MAX_TRANSACTION_VALUE = 2;
    public const MPESA_GATEWAY_TO_CLIENT_MORE_THAN_MAX_TRANSACTION_VALUE = 3;
    public const MPESA_GATEWAY_TO_CLIENT_WOULD_EXCEED_DAILY_TRANSFER_LIMIT = 4;
    public const MPESA_GATEWAY_TO_CLIENT_WOULD_EXCEED_MINIMUM_BALANCE = 5;

    // User input related
    public const MPESA_GATEWAY_TO_CLIENT_UNRESOLVED_PRIMARY_PARTY = 6;
    public const MPESA_GATEWAY_TO_CLIENT_UNRESOLVED_RECEIVER_PARTY = 7;

    // Fund
    public const MPESA_GATEWAY_TO_CLIENT_WOULD_EXCEED_MAXIMUM_BALANCE = 8;

    // User input
    public const MPESA_GATEWAY_TO_CLIENT_INVALID_DEBIT_ACCOUNT = 11;
    public const MPESA_GATEWAY_TO_CLIENT_INVALID_CREDIT_ACCOUNT = 12;

    // User input
    public const MPESA_GATEWAY_TO_CLIENT_UNRESOLVED_DEBIT_ACCOUNT = 13;
    public const MPESA_GATEWAY_TO_CLIENT_UNRESOLVED_CREDIT_ACCOUNT = 14;

    // Server
    public const MPESA_GATEWAY_TO_CLIENT_DUPLICATE_DETECTED = 15;
    public const MPESA_GATEWAY_TO_CLIENT_INTERNAL_FAILURE = 17;

    // User input
    public const MPESA_GATEWAY_TO_CLIENT_UNRESOLVED_INITIATOR = 20;

    // Server
    public const MPESA_GATEWAY_TO_CLIENT_TRAFFIC_BLOCKING_CONDITION_IN_PLACE = 26;

    // Client to gateway status code
    public const MPESA_CLIENT_TO_GATEWAY_SUCCESS_C2B = '0';
    public const MPESA_CLIENT_TO_GATEWAY_SUCCESS_OTHERS = '00000000';
    public const MPESA_CLIENT_TO_GATEWAY_REJECT = '1';

    // MPesa request PULL request
    public const MPESA_REQUEST_TYPE_PULL = 'Pull';
}
