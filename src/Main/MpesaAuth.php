<?

namespace HackDelta\Mpesa\Main;

use HackDelta\Mpesa\Exceptions\MpesaInternalException;

/**
 * This class will contain methods for generating authentication tokens, and managing the
 * whole token lifecycle
 */
class MpesaAuth 
{
    protected MpesaConfig $config;
    protected int $expires_at = 0;
    protected string $token = '';

    public function __construct(MpesaConfig $config)
    {
        // Check to see if the consumer key and consumer secret are set
        if( $config->getConsumerKey() === '' || $config->getConsumerKey() === '') {
            throw new MpesaInternalException(
                "Consumer key or consumer secret empty"
            );
        }

        $this->config = $config;
    }

    public function setAuthToken(string $token, int $expires_at_timestamp): self
    {
        $this->token = $token;
        $this->expires_at = $expires_at_timestamp;

        return $this;
    }

    public function getAuthToken(): self
    {
        throw new MpesaInternalException("Method not implemented");
    }

    public function hasExpired(): bool 
    {
        throw new MpesaInternalException("Method not implemented");
    }

    public function getExpiresAtTime(): int 
    {
        return $this->expired_at;
    }

    public function getToken(): string
    {
        return $this->token;
    }

}