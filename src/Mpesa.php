<?php

namespace HackDelta\Mpesa;

use HackDelta\Mpesa\Main\MpesaB2B;
use HackDelta\Mpesa\Main\MpesaB2C;
use HackDelta\Mpesa\Main\MpesaC2B;
use HackDelta\Mpesa\Main\MpesaConfig;
use HackDelta\Mpesa\Main\MpesaHttp;

/**
 * The main class that will be instantiated
 */
class Mpesa 
{
    protected static ?MpesaHttp $http_client = null;
    
    protected static ?MpesaB2C $B2C = null;
    protected static ?MpesaC2B $C2B = null;
    protected static ?MpesaB2B $B2B = null;

    protected ?MpesaConfig $config = null;

    public function __construct(MpesaConfig $config)
    {
        $this->config = $config;
    }

    public function config(): MpesaConfig
    {
        return $this->config;
    }

    public function C2B(): MpesaC2B
    {
        return self::$C2B === null ?
            new MpesaC2B($this->config) :
            self::$C2B;
    }

    public function B2B(): MpesaB2B
    {
        return self::$B2B === null ?
            new MpesaB2B($this->config) :
            self::$B2B;
    }

    public function B2C(): MpesaB2C
    {
        return self::$B2C === null ?
            new MpesaC2B($this->config) :
            self::$B2C;
    }

    // Account balance query
    // Optional remarks sent with the request
    public function checkBalance(string $remarks='remarks'): MpesaResponse
    {

    }

    // Transaction status, check the transaction status of an mpesa code
    // transaction_id: This is the mpesa code
    // Remarks: comments sent along with the transaction
    // Occasion: additional data sent with the transaction
    public function checkTransactionStatus(
        string $transaction_id,
        string $remarks='remarks',
        string $occasion=''
    ): MpesaResponse
    {

    }

    // Initiate reversal request
    // transaction_id: The Mpesa code.
    // amount: The amount that is being reversed
    // receiver_party: the shortcode, or MSISDN that received the payment
    // receiver_identifier_type: constant that shows the receiver identifier type
    //         possible values are; MPESA_IDENTIFIER_TYPE_MSISDN, MPESA_IDENTIFIER_TYPE_TILL,
    //                MPESA_IDENTIFIER_TYPE_PAYBILL , MPESA_IDENTIFIER_TYPE_SHORTCODE 
    public function reverseTransaction(
        string $transaction_id,
        int $amount,
        string $receiver_party,
        string $receiver_identifier_type,
        string $remarks='remarks',
        string $occasion=''
    ): MpesaResponse
    {

    }

}
