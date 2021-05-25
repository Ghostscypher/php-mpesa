<?php

namespace Hackdelta\Mpesa\Tests\Unit;

use Hackdelta\Mpesa\Exceptions\MpesaClientException;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Utility\DotEnv;
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    protected static string $ngrok_address;

    protected static array $config;

    protected MpesaConfig $mpesa_config;

    protected Mpesa $mpesa;

    public static function setUpBeforeClass(): void
    {
        (new DotEnv(__DIR__.'/../../.env'))->load();

        self::$ngrok_address = getenv('NGROK_ADDRESS');

        self::$config = [
            // API Credentials
            'consumer_key'    => getenv('CONSUMER_KEY'),
            'consumer_secret' => getenv('CONSUMER_SECRET'),

            // Environment
            'environment' => getenv('APP_ENV'),

            // User credential
            'initiator_name'     => getenv('INITIATOR_NAME'),
            'initiator_password' => getenv('INITIATOR_PASSWORD'),

            // Or
            'security_credential' => '',

            // Used in combination with initiator name
            // and password
            'sandbox_certificate_path'    => '',
            'production_certificate_path' => '',

            // Lipa na mpesa online passkey
            'passkey' => getenv('PASSKEY'),

            // Short code
            'short_code'          => getenv('SHORT_CODE'),
            'business_short_code' => getenv('BUSINESS_SHORT_CODE'),

            // Identifier type of the shortcode
            'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL,

            // URLS
            // Webhook URLS
            'confirmation_url' => self::$ngrok_address.'/confirm.php',
            'validation_url'   => self::$ngrok_address.'/validate.php',

            // STK callback URL
            'stk_callback_url' => self::$ngrok_address.'/callback.php',

            // Query results URL
            'queue_timeout_url' => self::$ngrok_address.'/callback.php',
            'result_url'        => self::$ngrok_address.'/callback.php',

            // Pull request
            // 600000 - Shortcode
            'organization_msisdn' => '0722000000',
            'pull_callback_url'   => self::$ngrok_address.'/callback.php',

        ];
    }

    protected function setUp(): void
    {
        $this->mpesa_config = new MpesaConfig(self::$config);
        $this->mpesa = new Mpesa($this->mpesa_config);
    }

    public function testAuthenticationWithCorrectData()
    {
        $this->assertIsString($this->mpesa->getConfig()->getAuth()->getToken());
    }

    public function testAuthenticationWithIncorrectCredentials()
    {
        $this->expectException(MpesaClientException::class);

        $this->mpesa->getConfig()
            ->setConsumerKey('Incorrect')
            ->setConsumerSecret('Data');

        $this->assertIsString($this->mpesa->getConfig()->getAuth()->getToken());
    }

    public function testAuthenticationWithMissingCredentials()
    {
        $this->expectException(MpesaInternalException::class);

        $this->mpesa->getConfig()
            ->setConsumerKey('')
            ->setConsumerSecret('');

        $this->assertIsString($this->mpesa->getConfig()->getAuth()->getToken());
    }
}
