<?php

namespace Hackdelta\Mpesa\Main;

use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Extras\Validatable;

/**
 * This class will contain methods for generating authentication tokens, and managing the
 * whole token lifecycle.
 */
class MpesaAuth
{
    use Validatable;

    protected MpesaConfig $config;
    protected int $expires_at         = 0;
    protected string $token           = '';
    protected bool $has_token_changed = false;

    protected static ?MpesaHttp $http_client = null;

    public function __construct(MpesaConfig $config)
    {
        $this->setConfig($config);

        if (self::$http_client === null) {
            self::$http_client = new MpesaHttp($this->config);
        }
    }

    public function setAuthToken(string $token, int $expires_at_timestamp = 0): self
    {
        $this->validateString('token', $token);

        $this->token      = $token;
        $this->expires_at = $expires_at_timestamp === 0 ? time() + 3600 : $expires_at_timestamp;

        return $this;
    }

    public function setConfig(MpesaConfig $config): self
    {
        $this->validateString('consumer_key', $config->getConsumerKey());
        $this->validateString('consumer_secret', $config->getConsumerSecret());

        $this->config = $config;

        return $this;
    }

    public function getConfig(): MpesaConfig
    {
        return $this->config;
    }

    protected function refreshToken(): void
    {
        $auth_url = sprintf(
            '%s%s',
            $this->config->getBaseURL(),
            MpesaConstants::MPESA_URIS['generate_token']
        );

        if (self::$http_client === null) {
            self::$http_client = new MpesaHttp($this->config);
        }

        $response = self::$http_client->request(
            'GET',
            $auth_url,
            [],
            [
                'Authorization' => sprintf(
                    'Basic %s',
                    $this->config->getCredentials()
                ),
            ]
        );

        // Mark token as has changed
        if ($this->expires_at !== 0) {
            $this->has_token_changed = true;
        }

        // Decode the JSON data returned
        $json_data = json_decode($response->getJSONString(), true);

        // Set the tokens
        $this->token      = $json_data['access_token'];
        $this->expires_at = time() + (int) $json_data['expires_in'];
    }

    public function getAuthToken(bool $force = false): self
    {
        if (! $force && ! $this->hasExpired()) {
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
        // Will refresh token if it has expired
        $this->getAuthToken();

        return $this->expires_at;
    }

    public function getToken(): string
    {
        // Will refresh token if it has expired
        $this->getAuthToken();

        return $this->token;
    }

    public function hasTokenChanged(): bool
    {
        return $this->has_token_changed;
    }
}
