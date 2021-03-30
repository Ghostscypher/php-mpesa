<?php

namespace HackDelta\Mpesa\Extras;

/**
 * This class stores constants that will be used throughout the mpesa app
 */
class MpesaConstants
{
    /**
     * Defines the mpesa endpoints
    */
    public const MPESA_URIS = [
        // Base uris
        'base_uri' => 'https://api.safaricom.co.ke',
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
        'account_balance' => '/mpesa/accountbalance/v1/query',
        'transaction_status' => '/mpesa/transactionstatus/v1/query',

        // Reversal
        'reversal' => '/mpesa/reversal/v1/request',

        // Stk related
        'stk_push' => '/mpesa/stkpush/v1/processrequest',
        'stk_push_query' => '/mpesa/stkpushquery/v1/query',

    ];

    /**
     * 
    */

}