<?php

namespace HackDelta\Mpesa;

class Mpesa
{
    /**
     * Defines the mpesa endpoints
     */
    public const MPESA_URIS = [
        // Base uris
        'base_uri' => 'https://api.safaricom.co.ke/',
        'sandbox_base_uri' => 'https://sandbox.safaricom.co.ke/',

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
     * This is the base uris
     */
    private string $base_uri;

    /**
     * Stores the credentials, from the consumer key, and secret
     */
    private string $credentials;

    /**
     * Consumer key
    */
    private string $consumer_key;

    /**
     * Consumer secred
     */
    private string $consumer_secret;

    /**
     * Constructor
     */
    public function __construct(string $consumer_key, string $consumer_secret, $is_development = true)
    {
        // Set consumer key and secret
        $this->setConsumerKey($consumer_key)
            ->setConsumerSecret($consumer_secret);

        // Set the base URI
        $this->base_uri = $is_development ? Mpesa::MPESA_URIS['sandbox_base_uri'] : Mpesa::MPESA_URIS['base_uri'];

    }

    /**
     * Set the consumer secret
     * 
     * @param $value - The consumer key
     */
    public function setConsumerKey(string $value) :self 
    {
        $this->consumer_key = $value;
        $this->regenerateCredentials();

        return $this;
    }

    /**
     * Set the consumer secret
     * 
     * @param $value - The consumer secret
     */
    public function setConsumerSecret(string $value) :self 
    {
        $this->consumer_secret = $value;
        $this->regenerateCredentials();

        return $this;
    }

    /**
     * Utility function to regenerate credentials
     */
    private function regenerateCredentials() :void
    {
        $this->credentials = base64_encode("{$this->consumer_key}:{$this->consumer_secret}");
    }

}