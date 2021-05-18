<?php

namespace Hackdelta\Mpesa\Tests;

// Autoload required files
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/bootstrap.php'; // Must come after autoload

use Hackdelta\Mpesa\Exceptions\MpesaClientException;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Mpesa;

// Pre-fill the base addresses
$ngrok_address = getenv('NGROK_ADDRESS');

$mpesa = new Mpesa(new MpesaConfig($config));

try{
    // echo $mpesa->C2B()->initiateSTKPush(1, "254708263715", "Test", description: "Test");

    // Prepare this for STK push simulation
    echo $mpesa->C2B()->registerURL();
    echo $mpesa->C2B()->simulate('254708374149', 100, 'test_account')->getJSONString();

    // STK push tests
    // $mpesa->getConfig()->setConfig("short_code", '174379')
    //     ->setConfig("business_short_code", '174379');

    // echo $mpesa->C2B()->initiateSTKPush("254708263715", 1, "Test", "Test");
    // echo $mpesa->C2B()->STKPushQuery("ws_CO_030520210342393444");

    // echo $mpesa->checkBalance();
    // echo $mpesa->reverseTransaction('PE341HJ3Q8', 100, '254708263715', MpesaConstants::MPESA_IDENTIFIER_TYPE_MSISDN);

    // Transaction status
    // echo $mpesa->checkTransactionStatus('PE331HJ3RV');

    // B2C
    // 601497
    // 600000
    // 174379
    // echo $mpesa->B2C()->send(
    //     100, 
    //     '254708263715',
    //     MpesaConstants::MPESA_COMMAND_ID_SALARY_PAYMENT 
    // );

    // echo $mpesa->B2B()->send(
    //     10, 
    //     '600000',
    //     MpesaConstants::MPESA_COMMAND_ID_BUSINESS_PAY_BILL,
    //     MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL,
    //     "account" 
    // );

    // Register pull request
    // $mpesa->getConfig()
    //     ->setShortCode('600000', MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL);

    // echo $mpesa->C2B()->pullRequestQuery("2019-07-31 20:00:00", "2019-07-31 22:00:00");
    
    echo "done";
    
} catch (MpesaClientException | MpesaServerException $e) {
    print_r(
        sprintf("Error: %s, body: %s, error code: %d", 
            $e->getMessage(), 
            $e->getErrorBody(), 
            $e->getStatusCode(),
        )
    );
    
    print_r("\n");
    // print_r($e->getRequestParameters());

} catch (MpesaInternalException $e){
    print_r(
        sprintf("Error: %s", $e->getMessage())
    );
}
