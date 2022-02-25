# Php mpesa library

[![Latest Stable Version](https://poser.pugx.org/hackdelta/mpesa/v)](//packagist.org/packages/hackdelta/mpesa)
[![Total Downloads](https://poser.pugx.org/hackdelta/mpesa/downloads)](//packagist.org/packages/hackdelta/mpesa)
[![Latest Unstable Version](https://poser.pugx.org/hackdelta/mpesa/v/unstable)](//packagist.org/packages/hackdelta/mpesa)
[![License](https://poser.pugx.org/hackdelta/mpesa/license)](//packagist.org/packages/hackdelta/mpesa)
[![PHPUnit Status](https://github.com/Ghostscypher/php-mpesa/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/Ghostscypher/php-mpesa/actions?query=branch%3Amain)

## Table of contents

- [Php mpesa library](#php-mpesa-library)
  - [Table of contents](#table-of-contents)
  - [Introduction](#introduction)
  - [Getting Started](#getting-started)
  - [Installation](#installation)
  - [Usage](#usage)
    - [Initiating the library](#initiating-the-library)
    - [Editing configurations](#editing-configurations)
    - [Example usage for the whole library](#example-usage-for-the-whole-library)
    - [Register validation and confirmation urls (C2B)](#register-validation-and-confirmation-urls-c2b)
    - [Simulate a transaction (C2B)](#simulate-a-transaction-c2b)
    - [Initiate STK push (C2B)](#initiate-stk-push-c2b)
    - [Checking the stk push query status (C2B)](#checking-the-stk-push-query-status-c2b)
    - [Sending money (B2B)](#sending-money-b2b)
    - [Sending money (B2C)](#sending-money-b2c)
    - [Check transaction status (General)](#check-transaction-status-general)
    - [Request for reversal (General)](#request-for-reversal-general)
    - [Check for balance (General)](#check-for-balance-general)
    - [Register pull request callback URL (General)](#register-pull-request-callback-url-general)
    - [Send pull request (General)](#send-pull-request-general)
  - [Advanced usage](#advanced-usage)
  - [How To Contribute](#how-to-contribute)
  - [Getting Help](#getting-help)
  - [Security](#security)
  - [Testing](#testing)

## Introduction

A PHP Library that wraps around the mpesa APIs

## Getting Started

This library runs on the following:

- PHP ^7.4
- PHP ^8.0

## Installation

You can install the package via composer
`composer require hackdelta/mpesa`

## Usage

In order to check for recent changes kindly check the [Changelog](CHANGELOG.md)

The purpose of this package is to wrap around Mpesa APIs in order to provide an easier
and much neater methods. This library uses [Guzzle http library](https://github.com/guzzle/guzzle)
under the hood for http requests.

This documentation will be divided into five sections covering specific areas. Any other
additional notes are welcomed. Feel free to contribute.

1. Introduction to the library
2. C2B transactions
3. B2B transactions
4. B2C transactions
5. General transactions

For further details that might not have been mention here check:

1. [Library specifications](specifications.md)
2. [Mpesa endpoints](mpesa_endpoints.md)
3. [Safaricom developer portal](https://developer.safaricom.co.ke)

### Initiating the library

The library requires one to pass a configuration that will be used in various parts of the library

```php

use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         // 'environment' => 'production', // on production

         // User credential
         'initiator_name'     => 'INITIATOR_NAME',
         'initiator_password' => 'INITIATOR_PASSWORD',

         // Or
         'security_credential' => '',

         // Used in combination with initiator name
         // and password
         // If you have different mpesa cert paths you can set them here 
         'sandbox_certificate_path'    => '',
         'production_certificate_path' => '',

         // Lipa na mpesa online passkey
         // For STK push
         'passkey' => 'PASSKEY',

         // Short code
         'short_code'          => 'SHORT_CODE', // This is your till or paybill
         'business_short_code' => 'BUSINESS_SHORT_CODE', // This is your head office number which for
                                                         // paybills it's the same, leave this blank
                                                         // if you are using paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For till

         // URLS
         // Webhook URLS
         'confirmation_url' => 'https://domain.example/confirm.php', // Where confirmation
                                                                     // callbacks will hit
         'validation_url'   => 'https://domain.example/validate.php',

         // STK callback URL
         'stk_callback_url' => 'https://domain.example/callback.php',

         // Query results URL
         'queue_timeout_url' => 'https://domain.example/callback.php',
         'result_url'        => 'https://domain.example/callback.php',

         // Pull request
         'organization_msisdn' => '0722000000', // Used for pull requests, this is the
                                                // number that receives confirmation messages
         'pull_callback_url'   => 'https://domain.example/callback.php',
      ]
   );

$mpesa = new Mpesa($config);

```

You don't have to set all those configurations, it's just listed here for all configuration
options available to you.

### Editing configurations

After initiation, each and every class provides you with a way of editing the configuration class
on the fly

For example

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;

$config = new MpesaConfig(...); // Remember to pass in the configurations here
$mpesa = new Mpesa($config);

$mpesa->getConfig()
      ->setSandboxEnvironment(false) // Change environment to production, true means sandbox
      ...
      ->setConsumerKey('new consumer key');

```

**Please note that the set methods can be chained.**

For a full list of available methods check the [specifications](specifications.md#mpesa-configuration-class)

### Example usage for the whole library

This just shows an overview of a typical scenario this might be used for testing purposes, in this
example we use test credentials, you are however required to provide all the callback URLS, the
consumer key, the consumer secret, and your phone number for stk push test.
You can get the test credentials from [Safaricom developer portal](https://developers.safaricom.co.ke)
**Note: Some of this tests will fail because of having different shortcodes for B2B, anc C2B, in short B2B, C2B, reversal, and pull transaction requests are expected to fail**

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = [
   // API Credentials
   'consumer_key'    => 'YOUR_CONSUMER_KEY', // <--- Change this
   'consumer_secret' => 'YOUR_CONSUMER_SECRET', // <--- Change this

   // Environment
   'environment' => 'sandbox', // on sandbox environment
   // 'environment' => 'production', // on production

   // User credential
   'initiator_name'     => 'testAPI497',
   'initiator_password' => 'Safaricom111!',

   // Lipa na mpesa online passkey
   // For STK push
   'passkey' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',

   // Short code
   'short_code'          => '601497', // This is your till or paybill
   'business_short_code' => '601497', // This is your head office number which for
                                                   // paybills it's the same, leave this blank
                                                   // if you are using paybill

   // Identifier type of the shortcode
   'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
   // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For till

   // URLS
   // Webhook URLS
   'confirmation_url' => 'https://domain.example.com/confirm.php', // Where confirmation
                                                               // callbacks will hit
   'validation_url'   => 'https://domain.example.com/validate.php',

   // STK callback URL
   'stk_callback_url' => 'https://domain.example.com/callback.php',

   // Query results URL
   'queue_timeout_url' => 'https://domain.example.com/callback.php',
   'result_url'        => 'https://domain.example.com/callback.php',

   // Pull request
   'organization_msisdn' => '0722000000', // Used for pull requests, this is the
                                          // number that receives confirmation messages
   'pull_callback_url'   => 'https://domain.example.com/callback.php',

];

$mpesa = new Mpesa($config);

// Check internal
function test_getting_auth_token() {
   echo $mpesa->getConfig()->getToken();
}

// C2B
function test_registering_c2b_callbacks() {
   echo $mpesa->registerURL();
}

function test_simulating_transaction() {
   echo $mpesa->simulate('254708374149', 100, 'test account reference');
}

function test_initiating_stk_push() {
   $mpesa->getConfig()
         ->setShortcode("short_code", '174379')
         ->setBusinessShortCode("business_short_code", '174379');
   
   // Change "254700000000" to your phone number
   echo $mpesa->STKPush("254700000000", 1, "Test", "Test");

   // Reset config back
   $mpesa->getConfig()
         ->setShortcode("short_code", '601497')
         ->setBusinessShortCode("business_short_code", '601497');
   
}

function test_getting_Stk_push_status() {
   $mpesa->getConfig()
         ->setConfig("short_code", '174379')
         ->setConfig("business_short_code", '174379');

   // Change the checkout id to the one you git from running the above
   echo $mpesa->STKPushQuery("ws_CO_030520210342393444");

   // Reset config back
   $mpesa->getConfig()
         ->setConfig("short_code", '601497')
         ->setConfig("business_short_code", '601497');
}

// General
function test_getting_balance() {
   echo $mpesa->checkBalance();
}

function test_reversal() {
   // Change 'PE341HJ3Q8' to a test value gotten after simulating the transaction
   echo $mpesa->reverseTransaction('PE341HJ3Q8', 100, '2547000000000',                       MpesaConstants::MPESA_IDENTIFIER_TYPE_MSISDN);

}

function test_checking_transaction_Status() {
   echo $mpesa->checkTransactionStatus('PE341HJ3Q8');
}

function test_register_pull_url() {
   echo $mpesa->pullRequestRegisterURL();
}

function test_pull() {
   echo $mpesa->pullRequestQuery("2019-07-31 20:00:00", "2019-07-31 22:00:00");
}

// B2C
function test_B2C() {
   echo $mpesa->B2C()->send(
        100, 
        '254700000000',
        MpesaConstants::MPESA_COMMAND_ID_SALARY_PAYMENT 
    );
}

// B2B
function test_B2B() {
   echo $mpesa->sendB2B(
        10, 
        '600000',
        MpesaConstants::MPESA_COMMAND_ID_BUSINESS_PAY_BILL,
        MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL,
        "account" 
    );
}

// In order to run the test uncomment one or more of the following
try {

   /**
    * This tests should pass provided you've set the correct configurations
   */

   /* Pre request check */
   test_getting_auth_token();

   /* C2B */
   // test_registering_c2b_callbacks();
   // test_simulating_transaction();
   // test_initiating_stk_push();
   // test_getting_Stk_push_status();

   /* B2B */
   // test_B2B(); // Expected to fail on sandbox

   /* B2C */
   // test_B2C(); // Expected to fail on sandbox

   /* General */
   // test_getting_balance();
   // test_checking_transaction_Status();
   // test_reversal(); // Expected to fail on sandbox
   // test_register_pull_url(); // Expected to fail on sandbox
   // test_pull(); // Expected to fail on sandbox

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method
}

```

### Register validation and confirmation urls (C2B)

To register the confirmation and validation urls you'll need to set the following configurations

**Note: This can be done only once in production***

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For till


         // Webhook URLS
         'confirmation_url' => 'https://domain.example/confirm.php',
         'validation_url'   => 'https://domain.example/validate.php',
      
      ]
);

$mpesa = new Mpesa($config);

// Register urls

try{

   $response = $mpesa->C2B()->registerURL(); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}

```

### Simulate a transaction (C2B)

This is used to simulate a transaction during sandbox testing, if used in production this will throw
an error

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For til

      ]
);

$mpesa = new Mpesa($config);

// Simulate a transaction

try{

   $response = $mpesa->C2B()->simulate(
      '25470000000', // Phone number to simulate, use test credentials
      100, // Amount to transact
      'account number' // if simulating for paybill otherwise should be blank
   ); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}
```

### Initiate STK push (C2B)

This is used for lipa na mpesa online transaction and requires the following configs

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill
         'business_short_code' => 'BUSINESS_SHORT_CODE', // This is your head office number which for
                                                         // paybills it's the same, leave this blank
                                                         // if you are using paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For til

         // Lipa na mpesa online passkey
         // For STK push
         'passkey' => 'PASSKEY',

         // STK callback URL
         'stk_callback_url' => 'https://domain.example/callback.php',

      ]
);

$mpesa = new Mpesa($config);

// Initiate an STK push request

try{

   $response = $mpesa->C2B()->initiateSTKPush(
      '25470000000', // Phone number to send request to
      100, // Amount to transact

      // Optional parameters
      'account number', // The account reference
      'description', // Description accompanying the transaction 
      'timestamp' // Your own timestamp

   ); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}
```

### Checking the stk push query status (C2B)

This is used to check the status of an stk push request

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill
         'business_short_code' => 'BUSINESS_SHORT_CODE', // This is your head office number which for
                                                         // paybills it's the same, leave this blank
                                                         // if you are using paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For til

         // Lipa na mpesa online passkey
         // For STK push
         'passkey' => 'PASSKEY',

      ]
);

$mpesa = new Mpesa($config);

// Check for STK request status

try{

   $response = $mpesa->C2B()->STKPushQuery('checkout request id'); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}
```

### Sending money (B2B)

This is used for sending money from a business to a business, the following configurations
are required

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For til

          // Query results URL
         'queue_timeout_url' => 'https://domain.example/callback.php',
         'result_url'        => 'https://domain.example/callback.php',

      ]
);

$mpesa = new Mpesa($config);

// Perform B2B transaction

try{

   $response = $mpesa->B2B()->send(
         50, // Amount
         'to', // The shortcode of the other organization
         'command id', // Check for appropriate command ids, some can be found in 
                        // the MpesaConstant class
         'receiver identifier type', // identifier of the reciever, expected   
                                    // MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL or
                                    // MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL or
                                    // MpesaConstants::MPESA_IDENTIFIER_TYPE_SHORTCODE

         // Optional parameters
         'account reference', // The account number
         'remarks' // The remarks for the transactions
      ); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}
```

### Sending money (B2C)

This is used for sending money from a business to a client, e,g. salary payments,
the following configurations are required

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For til

          // Query results URL
         'queue_timeout_url' => 'https://domain.example/callback.php',
         'result_url'        => 'https://domain.example/callback.php',

      ]
);

$mpesa = new Mpesa($config);

// Perform B2C transaction

try{

   $response = $mpesa->B2C()->send(
         50, // Amount
         'to', // The shortcode of the other organization
         'command id', // Check for appropriate command ids, some can be found in 
                        // the MpesaConstant class

         // Optional parameters
         'remarks', // The remarks for the transactions
         'occasion' // Occasion for transaction
      ); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}
```

### Check transaction status (General)

Used to check for account balance for the shortcode

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For til

         // User credential
         'initiator_name'     => 'YOUR_INITIATOR_NAME',
         'initiator_password' => 'YOUR_INITIATOR_PASSWORD',

          // Query results URL
         'queue_timeout_url' => 'https://domain.example/callback.php',
         'result_url'        => 'https://domain.example/callback.php',

      ]
);

$mpesa = new Mpesa($config);

// Check transaction status
try{

   $response = $mpesa->checkTransactionStatus(
         'transaction id', // The mpesa transaction id e.g. MXO1DGH5
         
         // Optional parameters
         'remarks', // The remarks for the transactions
         'occasion' // The occasion for the request
      ); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs hen a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}
```

### Request for reversal (General)

Used to reverse a transaction

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For til

         // User credential
         'initiator_name'     => 'YOUR_INITIATOR_NAME',
         'initiator_password' => 'YOUR_INITIATOR_PASSWORD',

          // Query results URL
         'queue_timeout_url' => 'https://domain.example/callback.php',
         'result_url'        => 'https://domain.example/callback.php',

      ]
);

$mpesa = new Mpesa($config);

// Start a reversal
try{

   $response = $mpesa->reverseTransaction(
         'transaction id', // The mpesa transaction id to reverse e.g. MX1C1K2LIM
         10, // The amount to be reversed
         
         // Optional parameters
         'remarks', // The remarks for the transactions
         'occasion' // Occasion for the transaction
         
      ); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}
```

### Check for balance (General)

Used to check for account balance for the shortcode

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For til

         // User credential
         'initiator_name'     => 'YOUR_INITIATOR_NAME',
         'initiator_password' => 'YOUR_INITIATOR_PASSWORD',

          // Query results URL
         'queue_timeout_url' => 'https://domain.example/callback.php',
         'result_url'        => 'https://domain.example/callback.php',

      ]
);

$mpesa = new Mpesa($config);

// Check balance
try{

   $response = $mpesa->checkBalance(
         // Optional parameters
         'remarks', // The remarks for the transactions
         
      ); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}
```

### Register pull request callback URL (General)

This is a relatively new API and i'll advice that one first checks the specifications and the
mpesa endpoints documentations. This API is used to request for transaction statement from safaricom
here we are registering the callback URL that will receive the results of a pull request call.

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For till

         // Pull request
         'organization_msisdn' => '0722000000', // Used for pull requests, this is the
                                                // number that receives confirmation messages
         'pull_callback_url'   => 'https://domain.example/callback.php'

      ]
);

$mpesa = new Mpesa($config);

// Register pull request callback URL
try{

   $response = $mpesa->pullRequestRegisterURL(); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}
```

### Send pull request (General)

This is a relatively new API and i'll advice that one first checks the specifications and the
mpesa endpoints documentations. This API is used to request for transaction statement from safaricom

```php
use Hackdelta\Mpesa\Mpesa;
use Hackdelta\Mpesa\Main\MpesaConfig;
use Hackdelta\Mpesa\Extras\MpesaConstants;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaClientExceptions;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

$config = new MpesaConfig(
      [
         // API Credentials
         'consumer_key'    => 'YOUR_CONSUMER_KEY',
         'consumer_secret' => 'YOUR_CONSUMER_SECRET',

         // Environment
         'environment' => 'sandbox', // on sandbox environment
         
         // Short code
         'short_code'  => 'SHORT_CODE', // This is your till or paybill

         // Identifier type of the shortcode
         'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_PAYBILL, // For paybills
         // 'identifier_type' => MpesaConstants::MPESA_IDENTIFIER_TYPE_TILL, // For till

         // Pull request
         'organization_msisdn' => '0722000000', // Used for pull requests, this is the
                                                // number that receives confirmation messages
         'pull_callback_url'   => 'https://domain.example/callback.php'

      ]
);

$mpesa = new Mpesa($config);

// Initiate a pull request
try{

   $response = $mpesa->pullRequestQuery(
      '2019-07-31 20:35:21', // The start date in the format shown
      '2019-08-01 20:35:21', // The end date in the format shown
      0 // The offset
   ); // Returns the MpesaResponse class object

   echo $response;
   // or
   echo $response->getJSONString();

} catch(MpesaInternalException $e) {
   // This exception occurs when a configuration is missing e.g. consumer key
   echo $e->getMessage();

} catch(MpesaClientException $ce){
   // This occurs when the request has been sent to the mpesa servers but some invalid
   // data was supplied e.g. invalid consumer key
   
   echo $ce->getStatusCode(); // Returns http status code of the error
   echo $ce->getMessage(); // Returns the error message
   echo $ce->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($ce->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

} catch (MpesaServerException $se) {
   // This occurs when the request has been sent to the mpesa servers
   // but the server experienced some issues this is an error from the gateway and
   // is not the client fault

   echo $se->getStatusCode(); // Returns http status code of the error
   echo $se->getMessage(); // Returns the error message
   echo $se->getErrorBody(); //  Returns the json string of the error body returned from server
   print_r($se->getRequestParameters()); // An array of the request parameters sent i.e. the headers,
                                          // the request body, the URL hit, and method

}
```

## Advanced usage

If you have any questions about advanced usage raise an issue in the issues tab.
Examples may include:

1. Persist token if the token is stored in database.
2. Switching between different types of shortcodes e.g. from normal C2B shortcode to a B2C shortcode

The above can be achieved by just changing the configs before sending the next request. Most
of this revolve around the config file itself, the config is passed by reference therefore a
change in one affects changes in the rest of the configuration. This is by design to enable easy
manipulation of the configurations without having to change the config for multiple classes.
This behavior forms some sort of shared data between the classes;

TODO: Add example of advanced usage

## How To Contribute

Please have a look at the [library specifications](specifications.md) to see what methods and any other specification your contributions should follow, before continuing.

Git is our version control system of choice and GitHub is our current
repository platform. Here is how we work with Git:

1. Generally we prefer branches over forks to ease internal collaboration.

2. When in doubt, use feature branches and gitflow as your branch
   naming scheme.

3. We have decided to adopt the Git Feature Branch Workflow.

4. Keep your repository clean; delete merged branches and avoid
   committing files specific to your dev environment (e.g. .DS_Store).

5. Follow this guidance about good commit messages.

6. Consider signing commits with a GPG key.

7. Feature branches have the following prefix: feature/.

8. Get your code approved by the project lead before pushing to master
   and deploying to production.

## Getting Help

In case of any assistance regarding the project, you can escalate the
issue on the project's issue board.

## Security

If you discover any security related issues, please email bngetich69@gmail.com instead of using the issue tracker.

## Testing

TODO
