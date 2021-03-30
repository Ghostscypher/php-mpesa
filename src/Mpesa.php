<?php

namespace HackDelta\Mpesa;

use HackDelta\Mpesa\Extras\MpesaConstants;

class Mpesa
{
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
        $this->base_uri = $is_development ? MpesaConstants::MPESA_URIS['sandbox_base_uri'] : MpesaConstants::MPESA_URIS['base_uri'];

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