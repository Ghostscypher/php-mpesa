<?php

namespace Hackdelta\Mpesa\Main;

use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Extras\MpesaConstants;

/**
 * Contains methods that are used to store environment
 * configurations
 */
class MpesaConfig 
{
    /**
     * Stores the config values
     */
    protected array $config = [
        // API Credentials
        'consumer_key' => '',
        'consumer_secret' => '',

        // Environment
        'environment' => 'sandbox',

        // User credential
        'initiator_name' => '',
        'initiator_password' => '',
        'passkey' => '',

        // Short code
        'short_code' => '',
        'business_short_code' => '',

        // Identifier type of the shortcode
        'identifier_type' => '',

        // URLS
        // Webhook URLS
        'confirmation_url' => '',
        'validation_url' => '',

        // STK callback URL
        'stk_callback_url' => '',

        // Query results URL
        'queue_timeout_url' => '',
        'result_url' => '',

    ];

    /**
     * Will hold auth instance
     */
    protected ?MpesaAuth $auth = null;

    /**
     * Will hold the security credential
     */
    protected string $security_credential = ''; 

    /**
     * Indicates thats one or more of the security credential
     * change, i.e. the consumer key, the consumer password
     */
    protected bool $has_api_credentials_changed = false;

    /**
     * Indicates that one or more of the user credential changed
     */
    protected bool $has_user_credentials_changed = false;

    /**
     * Constructor
     * 
     * Note that the constructors try to find the key set in your parameter
     * if the key is not found then the config key value pair is set
     * 
     * @throws MpesaInternalException when it is initialized with wrong parameters
     * 
     * @link https://www.amitmerchant.com/multiple-constructors-php/
     */
    public function __construct() 
    {
        $numberOfArguments = func_num_args();

        if ($numberOfArguments === 0) {
            return;
        }

        if ($numberOfArguments > 1) {
            throw new MpesaInternalException(
                "No constructor found with that number of arguments"
            );
        }

        $argument = func_get_args()[0];
        $argument_type = gettype($argument);

        if(! in_array($argument_type, ['array', 'string']) ){
            throw new MpesaInternalException(
                "No constructor accepting the argument type supplied found"
            );
        }

        switch ($argument_type) {
            case 'string':
                $this->__constructString($argument);
                break;
            
            case 'array':
                $this->__constructArray($argument);
                break;

            default:
                break;
        }

    }

    /**
     * Initialize with an array of configuration
     * 
     * @param array $config Key, value pair containing configurations
     * 
     * @throws MpesaInternalException when the config value passed is not a string
     */
    public function __constructArray(array $config): self
    {
        foreach ($this->config as $key => $value) {

            if( isset($config[$key]) ) { 
                
                if(gettype($config[$key]) !== 'string'){
                    throw new MpesaInternalException(
                        "Value of {$key} - '${value}' in your config must be a string"
                    );
                }

                $this->config[$key] = trim($config[$key]);
            }

        }
        
        return $this;
    }

    /**
     * Initialize with configuration as JSON string
     * 
     * @param string $config JSON string containing config
     * 
     * @throws MpesaInternalException when key value pairs are not both strings
     */
    public function __constructString(string $config): self
    {
        // Parse the JSON file
        $parsed_config = json_decode($config, true);

        return $this->__constructArray($parsed_config);
    }

    /**
     * Sets the environment
     * 
     * @param bool $is_sandbox - Set to true if environment is sandbox, by default environment is sandbox
     */
    public function setEnvironment(bool $is_sandbox): self 
    {
        $this->config['environment'] = $is_sandbox ? 'sandbox' : 'production';

        return $this;
    }

    /**
     * Get the environment as string i.e. 'production', 'sandbox'
     * 
     * @return string The environment
     */
    public function getEnvironment(): string 
    {
        return $this->config['environment'];
    }

    /**
     * @return bool true if environment is sandbox
     */
    public function isSandboxEnvironment(): bool 
    {
        return !$this->isProductionEnvironment();   
    }

    /**
     * @return bool true if environment is production
     */
    public function isProductionEnvironment(): bool 
    {
        return strtolower($this->config['environment']) === 'production';
    }

    /**
     * Set the consumer key
     * 
     * @param string $value The consumer key
     */
    public function setConsumerKey(string $value): self 
    {
        $this->config['consumer_key'] = trim($value);

        $this->has_api_credentials_changed = true;

        return $this;
    }

    /**
     * Get the consumer key
     * 
     * @return string The consumer key
     */
    public function getConsumerKey(): string 
    {
        return $this->config['consumer_key'];
    }

    /**
     * Set the consumer secret
     * 
     * @param string $value The consumer secret
     */
    public function setConsumerSecret(string $value): self
    {
        $this->config['consumer_secret'] = trim($value);

        $this->has_api_credentials_changed = true;

        return $this;
    } 

    /**
     * Get the consumer secret
     * 
     * @return string The consumer secret
     */
    public function getConsumerSecret(): string
    {
        return $this->config['consumer_secret'];
    }

    /**
     * Gets the base64 encoded credential generated from consumer key and secret
     * 
     * @return string base64 encoded credentials
     */
    public function getCredentials(): string
    {
        return base64_encode(
            sprintf("%s:%s", $this->getConsumerKey(), $this->getConsumerSecret())
        );
    }

    /**
     * Set the auth class, this is useful when one wishes to override, the default token
     * Useful when we get our token from somewhere else
     * 
     * @param MpesaAuth $auth The auth class object
     */
    public function setAuth(MpesaAuth $auth): self
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * Gets the auth class which has the auth token information
     * 
     * @return MpesaAuth The auth class
     */
    public function getAuth(): MpesaAuth
    {
        if (! $this->has_api_credentials_changed 
            && $this->auth !== null 
            && ! $this->auth->hasExpired()
        ) {
            return $this->auth;
        }

        $this->has_api_credentials_changed = false;

        $this->auth = new MpesaAuth($this);

        return $this->auth;
    }

    /**
     * Set the short code, this is the till or your paybill
     * 
     * @param string $value The shortcode
     * @param string $identifier_type Indicates the kind of short code the above is
     *      Accepted values are:
     *         Hackdelta\Mpesa\Extras\MpesaConstants\MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL,
     *         Hackdelta\Mpesa\Extras\MpesaConstants\MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL
     * 
     * @var Hackdelta\Mpesa\Extras\MpesaConstants\MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL
     * @var Hackdelta\Mpesa\Extras\MpesaConstants\MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL
     * 
     * @throws MpesaInternalException when an invalid identifier type is set
     */
    public function setShortCode(string $value, string $identifier_type): self
    {
        $accepted_identifiers = [
            MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, 
            MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL
        ];

        // Identifier type
        $identifier_type = trim($identifier_type);

        if(! in_array($identifier_type, $accepted_identifiers) ) {
            throw new MpesaInternalException(
                sprintf("'%s' Invalid identifier type, accepted identifiers are: %s", 
                    $identifier_type, implode(', ', $accepted_identifiers))
            );
        }

        $this->config['identifier_type'] = $identifier_type;
        $this->config['short_code'] = trim($value);

        return $this;
    }

    /**
     * Get the shortcode
     * 
     * @return string The shortcode
     */
    public function getShortCode(): string
    {
        return $this->config['short_code'];
    }

    /**
     * Gets the identifier type
     * 
     * @return string The identifier type
     */
    public function getIdentifierType(): string
    {
        return $this->config['identifier_type'];
    }

    /**
     * Sets the business shortcode, this is assumed to be the same the
     * shortcode if not set.
     * This configuration is useful when performing STK push as in the case of till
     * numbers which have a different shortcode, and business short code.
     * The business shortcode might be the head office number
     * 
     * @param string $value The business shortcode
     */
    public function setBusinessShortCode(string $value): self
    {
        $this->config['business_short_code'] = trim($value);

        return $this;
    }

    /**
     * Return the business shortcode, Sets the business shortcode, this is 
     * assumed to be the same the shortcode
     * 
     * @return string The business shortcode
     */
    public function getBusinessShortCode(): string
    {
        if($this->config['business_short_code'] === '') {
            return $this->config['short_code'];
        }

        return $this->config['business_short_code'];
    }

    /**
     * Sets the confirmation URL, this is the callback/webhook URL
     * that receives successful transactions.
     * 
     * @param string $value The confirmation URL
     */
    public function setConfirmationURL(string $value): self
    {
        $this->config['confirmation_url'] = trim($value);

        return $this;
    }

    /**
     * Gets the confirmation URL, this is the callback/webhook URL
     * that receives successful transactions.
     * 
     * @return string The confirmation URL
     */
    public function getConfirmationURL(): string
    {
        return $this->config['confirmation_url'];
    }

    /**
     * Sets the validation URL, this is the callback/webhook URL
     * that receives validation request, validation requests are only called
     * if the user has enabled validation in their Mpesa web portal
     * 
     * @param string $value The validation URL
     */
    public function setValidationURL(string $value): self
    {
        $this->config['validation_url'] = trim($value);

        return $this;
    }

    /**
     * Sets the validation URL, this is the callback/webhook URL
     * that receives validation request, validation requests are only called
     * if the user has enabled validation in their Mpesa web portal
     * 
     * @return string The validation URL
     */
    public function getValidationURL(): string
    {
        return $this->config['validation_url'];
    }

    /**
     * Sets the queue timeout URL, this is the callback/webhook URL
     * that receives timed out requests to the mpesa gateway.
     * 
     * @param string The queue timeout URL
     */
    public function setQueueTimeoutURL(string $value): self
    {
        $this->config['queue_timeout_url'] = trim($value);

        return $this;
    }

    /**
     * Gets the queue timeout URL, this is the callback/webhook URL
     * that receives timed out requests to the MPesa gateway.
     * 
     * @return string The queue timeout URL
     */
    public function getQueueTimeoutURL(): string
    {
        return $this->config['queue_timeout_url'];
    }

    /**
     * Sets the result URL, this is the callback/webhook URL
     * that receives successful requests to the MPesa gateway.
     * 
     * @param string $value The result URL
     */
    public function setResultURL(string $value): self
    {
        $this->config['result_url'] = trim($value);

        return $this;
    }

    /**
     * Gets the result URL, this is the callback/webhook URL
     * that receives successful requests to the MPesa gateway.
     * 
     * @return string The result URL
     */
    public function getResultURL(): string
    {
        return $this->config['result_url'];
    }

    /**
     * Sets the STK (Sim toolkit) callback URL, this is the callback/webhook URL
     * that receives a callback whenever an stk request is initialized.
     * 
     * @param string $value The STK callback URL
     */
    public function setSTKCallbackURL(string $value): self
    {
        $this->config['stk_callback_url'] = trim($value);

        return $this;
    }

    /**
     * Gets the STK (Sim toolkit) callback URL, this is the callback/webhook URL
     * that receives a callback whenever an stk request is initialized.
     * 
     * @param string $value - The STK callback URL
     */
    public function getSTKCallbackURL(): string
    {
        return $this->config['stk_callback_url'];
    }

    /**
     * Helper function to quickly get a URL
     * 
     * @param string $name The name of the url, supported values are
     *  '           stk_callback_url', 'result_url', 'queue_timeout_url', 
     *              'validation_url', 'confirmation_url'
     * 
     * @throws MpesaInternalException Whenever a wrong URL name is supplied
     */
    public function getUrl(string $name): string
    {
        $url_name = strtolower( trim($name) );

        $available_urls = [
            'stk_callback_url',
            
            'result_url',
            'queue_timeout_url',

            'validation_url',
            'confirmation_url',
        ];

        if( !in_array($url_name, $available_urls) ) {
            throw new MpesaInternalException(
                sprintf("'%s' not found in the configuration, available options are: '%s'",
                    $name, implode(', ', $available_urls)
                )
            );
        }

        return $this->config[$url_name];
    }

    /**
     * Sets the initiator name
     * 
     * @param string $value - Initiator name
     */
    public function setInitiatorName(string $value): self
    {
        $this->config['initiator_name'] = trim($value);

        return $this;
    }

    /**
     * Gets the initiator name
     * 
     * @return string The initiator name
     */
    public function getInitiatorName(): string
    {
        return $this->config['initiator_name'];
    }

    /**
     * Gets the initiator name, this is an alias of getInitiatorName()
     * 
     * @return string The initiator name
     */
    public function getInitiator(): string
    {
        return $this->getInitiatorName();
    }

    /**
     * Sets the mpesa passkey, this is given together with the API credentials
     * 
     * @param string $value - The Mpesa passkey
     */
    public function setPassKey(string $value): self
    {
        $this->config['passkey'] = trim($value);

        return $this;
    }

    /**
     * Gets the Mpesa passkey.
     * 
     * @return string The mpesa passkey
     */
    public function getPasskey(): string
    {
        return $this->config['passkey'];
    }

    /**
     * Gets the Mpesa password.
     * 
     * The password for encrypting the request. This is generated by 
     * base64 encoding BusinessShortcode, Passkey and Timestamp.
     * 
     * @return string The mpesa password
     */
    public function getPassword(string $timestamp): string
    {
        return base64_encode(sprintf(
                "%s%s%s",
                $this->getBusinessShortCode(),
                $this->config['passkey'],
                $timestamp
            )
        );
    }

    /**
     * Sets the initiator password
     */
    public function setInitiatorPassword(string $initiator_password): self
    {
        $this->config['initiator_password'] = $initiator_password;
        
        $this->has_user_credentials_changed = true;

        return $this;
    }

    /**
     * @return the initiator password
     */
    public function getInitiatorPassword(): string
    {
        return $this->config['initiator_password'];
    }

    /**
    * Allows one to override the default security credential,
    * useful in situation where this credential is stored in database
    */
    public function setSecurityCredential(string $value): self
    {
        $this->security_credential = $value;
        $this->has_user_credentials_changed = false;

        return $this;
    }

    /**
     * The encrypted security
     * 
     * Thanks to https://github.com/peternjeru/mpesa-encryption-encoding-php/blob/master/src/InitiatorPasswordEncryption.php
     */
    public function getSecurityCredential(): string
    {
        if( !$this->has_user_credentials_changed && $this->security_credential !== '' ) {
            return $this->security_credential;
        }

        // Get the path to correct cert
        $cert_path =  realpath(
            sprintf(
                "%s/../../certificates/%s",
                __DIR__,
                $this->isSandboxEnvironment() ? 'sandbox.cer' : 'production.cer'
            )
        );

        $encrypted = '';
        
        // Get the contents of the 
        $cert_content = file_get_contents($cert_path);

        $cert_content = "-----BEGIN CERTIFICATE-----\n".
            "MIIGgDCCBWigAwIBAgIKMvrulAAAAARG5DANBgkqhkiG9w0BAQsFADBbMRMwEQYK".
            "CZImiZPyLGQBGRYDbmV0MRkwFwYKCZImiZPyLGQBGRYJc2FmYXJpY29tMSkwJwYD".
            "VQQDEyBTYWZhcmljb20gSW50ZXJuYWwgSXNzdWluZyBDQSAwMjAeFw0xNDExMTIw".
            "NzEyNDVaFw0xNjExMTEwNzEyNDVaMHsxCzAJBgNVBAYTAktFMRAwDgYDVQQIEwdO".
            "YWlyb2JpMRAwDgYDVQQHEwdOYWlyb2JpMRAwDgYDVQQKEwdOYWlyb2JpMRMwEQYD".
            "VQQLEwpUZWNobm9sb2d5MSEwHwYDVQQDExhhcGljcnlwdC5zYWZhcmljb20uY28u".
            "a2UwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCotwV1VxXsd0Q6i2w0".
            "ugw+EPvgJfV6PNyB826Ik3L2lPJLFuzNEEJbGaiTdSe6Xitf/PJUP/q8Nv2dupHL".
            "BkiBHjpQ6f61He8Zdc9fqKDGBLoNhNpBXxbznzI4Yu6hjBGLnF5Al9zMAxTij6wL".
            "GUFswKpizifNbzV+LyIXY4RR2t8lxtqaFKeSx2B8P+eiZbL0wRIDPVC5+s4GdpFf".
            "Y3QIqyLxI2bOyCGl8/XlUuIhVXxhc8Uq132xjfsWljbw4oaMobnB2KN79vMUvyoR".
            "w8OGpga5VoaSFfVuQjSIf5RwW1hitm/8XJvmNEdeY0uKriYwbR8wfwQ3E0AIW1Fl".
            "MMghAgMBAAGjggMkMIIDIDAdBgNVHQ4EFgQUwUfE+NgGndWDN3DyVp+CAiF1Zkgw".
            "HwYDVR0jBBgwFoAU6zLUT35gmjqYIGO6DV6+6HlO1SQwggE7BgNVHR8EggEyMIIB".
            "LjCCASqgggEmoIIBIoaB1mxkYXA6Ly8vQ049U2FmYXJpY29tJTIwSW50ZXJuYWwl".
            "MjBJc3N1aW5nJTIwQ0ElMjAwMixDTj1TVkRUM0lTU0NBMDEsQ049Q0RQLENOPVB1".
            "YmxpYyUyMEtleSUyMFNlcnZpY2VzLENOPVNlcnZpY2VzLENOPUNvbmZpZ3VyYXRp".
            "b24sREM9c2FmYXJpY29tLERDPW5ldD9jZXJ0aWZpY2F0ZVJldm9jYXRpb25MaXN0".
            "P2Jhc2U/b2JqZWN0Q2xhc3M9Y1JMRGlzdHJpYnV0aW9uUG9pbnSGR2h0dHA6Ly9j".
            "cmwuc2FmYXJpY29tLmNvLmtlL1NhZmFyaWNvbSUyMEludGVybmFsJTIwSXNzdWlu".
            "ZyUyMENBJTIwMDIuY3JsMIIBCQYIKwYBBQUHAQEEgfwwgfkwgckGCCsGAQUFBzAC".
            "hoG8bGRhcDovLy9DTj1TYWZhcmljb20lMjBJbnRlcm5hbCUyMElzc3VpbmclMjBD".
            "QSUyMDAyLENOPUFJQSxDTj1QdWJsaWMlMjBLZXklMjBTZXJ2aWNlcyxDTj1TZXJ2".
            "aWNlcyxDTj1Db25maWd1cmF0aW9uLERDPXNhZmFyaWNvbSxEQz1uZXQ/Y0FDZXJ0".
            "aWZpY2F0ZT9iYXNlP29iamVjdENsYXNzPWNlcnRpZmljYXRpb25BdXRob3JpdHkw".
            "KwYIKwYBBQUHMAGGH2h0dHA6Ly9jcmwuc2FmYXJpY29tLmNvLmtlL29jc3AwCwYD".
            "VR0PBAQDAgWgMD0GCSsGAQQBgjcVBwQwMC4GJisGAQQBgjcVCIfPjFaEwsQDhemF".
            "NoTe0Q2GoIgIZ4bBx2yDublrAgFkAgEMMB0GA1UdJQQWMBQGCCsGAQUFBwMCBggr".
            "BgEFBQcDATAnBgkrBgEEAYI3FQoEGjAYMAoGCCsGAQUFBwMCMAoGCCsGAQUFBwMB".
            "MA0GCSqGSIb3DQEBCwUAA4IBAQBMFKlncYDI06ziR0Z0/reptIJRCMo+rqo/cUuP".
            "KMmJCY3sXxFHs5ilNXo8YavgRLpxJxdZMkiUIVuVaBanXkz9/nMriiJJwwcMPjUV".
            "9nQqwNUEqrSx29L1ARFdUy7LhN4NV7mEMde3MQybCQgBjjOPcVSVZXnaZIggDYIU".
            "w4THLy9rDmUIasC8GDdRcVM8xDOVQD/Pt5qlx/LSbTNe2fekhTLFIGYXJVz2rcsj".
            "k1BfG7P3pXnsPAzu199UZnqhEF+y/0/nNpf3ftHZjfX6Ws+dQuLoDN6pIl8qmok9".
            "9E/EAgL1zOIzFvCRYlnjKdnsuqL1sIYFBlv3oxo6W1O+X9IZ\n".
            "-----END CERTIFICATE-----";

        // Create the public key
        $public_key = openssl_pkey_get_public($cert_content);

        if (! openssl_public_encrypt(
            $this->getInitiatorPassword(), 
            $encrypted, 
            $public_key, 
            OPENSSL_PKCS1_PADDING)
        ) {
            var_dump($encrypted);
            throw new MpesaInternalException(
                "Unable to generate security credential. Perhaps it is bigger than the key size?"
            );
        }

        $this->has_user_credentials_changed = false;
        $this->security_credential = base64_encode($encrypted);

        return $this->security_credential;
    }

    /**
     * Checks to see if a certain config key is set
     * 
     * @param bool $key The config key
     * 
     * @return bool True if that configuration exists
     */
    public function isConfigSet(string $key): bool
    {
        return array_key_exists( $key, $this->config );
    }

     /**
     * Checks to see if a certain config key is set
     * Alias ro the isConfigSet() function
     * 
     * @param bool $key The config key
     * 
     * @return bool True if that configuration exists
     */
    public function exists(string $key): bool
    {
        return $this->isConfigSet($key);
    }

    /**
     * Sets the confirmation URL, this is the callback/webhook URL
     * that receives successful transactions.
     * 
     * @param string $key - The config key
     * @param string $value - The value to set
     * 
     * @throws MpesaInternalException When we try to set a config that doesn't exist
     */
    public function setConfig(string $key, string $value): self
    {
        if( !$this->exists($key) ){
            throw new MpesaInternalException(
                "{$key} not found in configuration"
            );
        }

        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Gets the configuration by name
     * 
     * @param string $key - The config key
     * 
     * @return string The configuration, null if the key is not set
     */
    public function getConfig(string $key): ?string
    {
        if( !$this->exists($key) ) {
            return null;
        }

        return $this->config[$key];
    }

    /**
     * Returns the baseURI/baseURL based on whether the library is in production mode
     *  or not
     * 
     * @return string The base URI/URL
     */ 
    public function getBaseURL(): string
    {
        return $this->isSandboxEnvironment() ? 
            MpesaConstants::MPESA_URIS['sandbox_base_uri'] :
            MpesaConstants::MPESA_URIS['base_uri'];
    }

    /**
     * Gets a clean URL with stripped '/' on the end
     * 
     * @param string $base_url The base URI/ base URL
     * @param string $path The path
     * 
     * @return string The cleaned URL
     */
    public static function makeURL(string $base_url, string $path)
    {
        return sprintf(
            "%s/%s", 
            rtrim( trim($base_url), '/'), 
            rtrim( trim($path), '/') 
        );
    }

}
