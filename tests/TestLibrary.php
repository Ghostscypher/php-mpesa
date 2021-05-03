<?php

namespace Hackdelta\Mpesa\Tests;

require __DIR__ . '/../vendor/autoload.php';

use Hackdelta\Mpesa\Exceptions\MpesaClientException;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Mpesa;

$ngrok_address = "http://6521c7bddb03.ngrok.io";

$config = [
    // API Credentials
    'consumer_key' => 'dKlS8pi198zG2bDQlRFpatCFCofXdNXs',
    'consumer_secret' => 'J8MaOmAIauDB09HG',

    // Environment
    'environment' => 'sandbox',

    // User credential
    'initiator_name' => 'testAPI497',
    'initiator_password' => 'Safaricom111!',
    'passkey' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',

    // Short code
    'short_code' => '601497',
    'business_short_code' => '601497',

    // Identifier type of the shortcode
    'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL,

    // URLS
    // Webhook URLS
    'confirmation_url' => "{$ngrok_address}/confirm.php",
    'validation_url' => "{$ngrok_address}/validate.php",

    // STK callback URL
    'stk_callback_url' => "{$ngrok_address}/callback.php",

    // Query results URL
    'queue_timeout_url' => "{$ngrok_address}/callback.php",
    'result_url' => "{$ngrok_address}/callback.php",

];

$mpesa = new Mpesa(new MpesaConfig($config));

try{
    // echo $mpesa->C2B()->initiateSTKPush(1, "254708263715", "Test", description: "Test");

    // Prepare this for STK push simulation
    // echo $mpesa->C2B()->register();
    // echo $mpesa->C2B()->simulate('254708374149', 100, 'test_account')->getJSONString();

    // STK push tests
    // $mpesa->getConfig()->setConfig("short_code", '174379')
    //     ->setConfig("business_short_code", '174379');

    // echo $mpesa->C2B()->initiateSTKPush(1, "254708263715", "Test", "Test");
    // echo $mpesa->C2B()->STKPushQuery("ws_CO_030520210342393444");

    echo $mpesa->checkBalance();

} catch (MpesaClientException | MpesaServerException $e) {
    print_r(
        sprintf("Error: %s, body: %s, code: %d", 
            $e->getMessage(), 
            $e->getErrorBody(), 
            $e->getStatusCode(),
        )
    );
    
    print_r($e->getRequestParameters());

} catch (MpesaInternalException $e){
    print_r(
        sprintf("Error: %s", $e->getMessage())
    );
}
