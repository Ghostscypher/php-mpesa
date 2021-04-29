<?php

namespace HackDelta\Mpesa\Main;

use HackDelta\Mpesa\Exceptions\MpesaInternalException;
use HackDelta\Mpesa\Extras\MpesaConstants;

/**
 * This class will contain methods for generating authentication tokens, and managing the
 * whole token lifecycle
 */
class MpesaAuth 
{
    protected MpesaConfig $config;
    protected int $expires_at = 0;
    protected string $token = '';
    protected bool $has_token_changed = false;
    protected static ?MpesaHttp $http_client = null;

    public function __construct(MpesaConfig $config)
    {
        // Check to see if the consumer key and consumer secret are set
        if( $config->getConsumerKey() === '' || $config->getConsumerKey() === '' ) {
            throw new MpesaInternalException(
                "Consumer key or consumer secret empty"
            );
        }

        if( self::$http_client === null ) { self::$http_client = new MpesaAuth($this->config); }

        $this->config = $config;
    }

    public function setAuthToken(string $token, int $expires_at_timestamp): self
    {
        $this->token = $token;
        $this->expires_at = $expires_at_timestamp;

        return $this;
    }

    private function refreshToken(): void 
    {
        $auth_url = sprintf(
            "%s/%s", 
            $this->config->getBaseURL(), 
            MpesaConstants::MPESA_URIS['generate_token']
        );

        $response = self::$http_client->request(
            method: 'GET',
            uri: $auth_url,
            headers: [
                'Authorization' => sprintf(
                    'Basic %s', 
                    $this->config->getCredentials() 
                ),
            ]
        );

        
        // Mark token as has changed
        if($this->expires_at !== 0) { $this->has_token_changed = true; }

        // Decode the JSON data returned
        $json_data = json_decode( $response->getJSONString() );

        // Set the tokens
        $this->token - $json_data['token'];
        $this->expires_at = time() + (int)$json_data['expires'];

    }

    public function getAuthToken(bool $force = false): self
    {
        if( !$force && !$this->hasExpired() )
        {
            return $this;
        }

        $this->refreshToken();

        return $this;
    }

    public function hasExpired(): bool 
    {
        return ($this->expires_at - time()) <= 0;
    }

    public function getExpiresAtTime(): int 
    {
        return $this->expired_at;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function hasTokenChanged(): bool
    {
        return $this->has_token_changed;
    }

}