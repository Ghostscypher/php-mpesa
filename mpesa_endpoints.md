# Mpesa endpoints

The following specifies the mpesa endpoints, their data and their expected responses

## Table of contents

- [Mpesa endpoints](#mpesa-endpoints)
  - [Table of contents](#table-of-contents)
  - [The endpoints](#the-endpoints)
  - [Get auth token](#get-auth-token)
    - [_Request format_](#request-format)
  - [Client to business register (C2B register)](#client-to-business-register-c2b-register)
    - [_Request format_](#request-format-1)
  - [Client to business simulate (C2B Simulate)](#client-to-business-simulate-c2b-simulate)
    - [_Request format_](#request-format-2)
  - [Business to client (B2C)](#business-to-client-b2c)
    - [_Request format_](#request-format-3)
  - [Business to business (B2B) (Deprecated)](#business-to-business-b2b-deprecated)
    - [_Request format_](#request-format-4)
  - [Account balance query](#account-balance-query)
    - [_Request format_](#request-format-5)
  - [Transaction status query](#transaction-status-query)
    - [_Request format_](#request-format-6)
  - [Reversal request](#reversal-request)
    - [_Request format_](#request-format-7)
  - [Initiate stk push](#initiate-stk-push)
    - [_Request format_](#request-format-8)
  - [Initiate stk push query](#initiate-stk-push-query)
    - [_Request format_](#request-format-9)
  - [Register pull transaction API URL](#register-pull-transaction-api-url)
    - [_Request format_](#request-format-10)
  - [Pull transaction API](#pull-transaction-api)
    - [_Request format_](#request-format-11)
  - [Errors, and error code](#errors-and-error-code)
  - [Identifier Types](#identifier-types)
  - [M-Pesa Result and Response Codes (From gateway to client)](#m-pesa-result-and-response-codes-from-gateway-to-client)
  - [M-Pesa Response Codes (from client back to gateway)](#m-pesa-response-codes-from-client-back-to-gateway)
  - [Mpesa command ids](#mpesa-command-ids)

## The endpoints

- `get auth token` - Endpoint to get the authentication token
- `C2B Register` - Client to business register endpoint
- `C2B Simulate` - Simulate transactions, only works during development
- `B2C` - Business to client payments
- `B2B` - Business to business payments
- `Account balance query` - Used to check the account balance
- `Transaction status query` - Used to query the transaction status
- `Initialize stk push` - Used to initialize an stk push
- `Stk push query` - Used to check the status of an stk push
- `Reversal` - Used to initiate a reversal request

Let's have a look at each of the endpoints and their expected results

## Get auth token

Method: `GET`

Endpoint: `{base_uri}/oauth/v1/generate?grant_type=client_credentials`

Description: Gets the auth token that is used by the rest of the transactions

### _Request format_

headers:

`Authorization: Bearer {base64(consumer_key + consumer_secret)}` - Required

`cache-control: no-cache` - optional

Body:

`None`

Response:

```json
{
    "access_token": "SGWcJPtNtYNPGm6uSYR9yPYrAI3Bm",
    "expires_in": "3599"
}
```

## Client to business register (C2B register)

Method: `POST`

Endpoint: `{base_uri}/mpesa/c2b/v1/registerurl`

Description: Used to register a callback url/ webhook that will receive notifications
of an mpesa payment. Note that during production this can only be done once

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`cache-control: no-cache` - optional

Body:

```json
    {
        "ShortCode": " ", // The short code of the organization.
        "ResponseType": " ", // Default response type for timeout. Can be blank
        "ConfirmationURL": "http://ip_address:port/confirmation", // Confirmation URL for the client. The URL that receives the complete transaction
        "ValidationURL": "http://ip_address:port/validation_url" // Validation URL for the client. URL to receive requests of validating a transaction, whether to allow a transaction to proceed or not
    }
```

Response:

```json
 // Validation Response
  {
    "TransactionType":"",
    "TransID":"LGR219G3EY",
    "TransTime":"20170727104247",
    "TransAmount":"10.00",
    "BusinessShortCode":"600134",
    "BillRefNumber":"xyz",
    "InvoiceNumber":"",
    "OrgAccountBalance":"",
    "ThirdPartyTransID":"",
    "MSISDN":"254708374149",
    "FirstName":"John",
    "MiddleName":"Doe",
    "LastName":""
  }
  
  //Confirmation Response
  {
    "TransactionType":"",
    "TransID":"LGR219G3EY",
    "TransTime":"20170727104247",
    "TransAmount":"10.00",
    "BusinessShortCode":"600134",
    "BillRefNumber":"xyz",
    "InvoiceNumber":"",
    "OrgAccountBalance":"49197.00",
    "ThirdPartyTransID":"1234567890",
    "MSISDN":"254708374149",
    "FirstName":"John",
    "MiddleName":"Doe",
    "LastName":""
  }
```

## Client to business simulate (C2B Simulate)

Method: `POST`

Endpoint: `{base_uri/mpesa/c2b/v1/simulate`

Description: Used to simulate an mpesa transaction. Note: This is only available in sandbox environment

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`cache-control: no-cache` - optional

Body:

```json
{
    "ShortCode":" ", // Organization short code
    "CommandID":"CustomerPayBillOnline", // This is a unique identifier of the transaction type: There are two types of these Identifiers: 
    // CustomerPayBillOnline: This is used for Pay Bills shortcodes 
    // CustomerBuyGoodsOnline: This is used for Buy Goods shortcodes.
    "Amount":" ", // The amount been transacted.
    "Msisdn":"254701234567", // MSISDN (phone number) sending the transaction, start with country code without the plus(+) sign.
    "BillRefNumber":" "  // M-Pesa Till Number or PayBill Number
}
```

Response:

```json
{
    "ConversationID" : "", // A unique numeric code generated by the M-Pesa system of the response to a request.
    "OriginatorConversationID" : "", // A unique numeric code generated by the M-Pesa system of the request.
    "ResponseDescription" : "" // A response message from the M-Pesa system accompanying the response to a request.
}
```

## Business to client (B2C)

Method: `POST`

Endpoint: `{base_uri}/mpesa/b2c/v1/paymentrequest`

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`cache-control: no-cache` - optional

Body:

```json
    {
        "InitiatorName": " ", // This is the credential/username used to authenticate the transaction request.
        "SecurityCredential":" ", // Base64 encoded string of the B2C short code and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system.
        "CommandID": " ", // Unique command for each transaction type e.g. SalaryPayment, BusinessPayment, PromotionPayment
        "Amount": " ",  // The amount being transacted
        "PartyA": " ", // Organization’s shortcode initiating the transaction.
        "PartyB": " ", // Phone number receiving the transaction
        "Remarks": " ", // Comments that are sent along with the transaction.
        "QueueTimeOutURL": "http://your_timeout_url", // The timeout end-point that receives a timeout response.
        "ResultURL": "http://your_result_url", // The end-point that receives the response of the transaction
        "Occasion": " " // Optional description to be sent along with the transaction
    }
```

Response:

```json
{
    "Result": {
      "ResultType":0,
      "ResultCode":0,
      "ResultDesc":"The service request has been accepted successfully.",
      "OriginatorConversationID":"19455-424535-1",
      "ConversationID":"AG_20170717_00006be9c8b5cc46abb6",
      "TransactionID":"LGH3197RIB",
      "ResultParameters": {
        "ResultParameter": [
          {
            "Key":"TransactionReceipt",
            "Value":"LGH3197RIB"
          },
          {
            "Key":"TransactionAmount",
            "Value":8000
          },
          {
            "Key":"B2CWorkingAccountAvailableFunds",
            "Value":150000
          },
          {
            "Key":"B2CUtilityAccountAvailableFunds",
            "Value":133568
          },
          {
            "Key":"TransactionCompletedDateTime",
            "Value":"17.07.2017 10:54:57"
          },
          {
            "Key":"ReceiverPartyPublicName",
            "Value":"254708374149 - John Doe"
          },
          {
            "Key":"B2CChargesPaidAccountAvailableFunds",
            "Value":0
          },
          {
            "Key":"B2CRecipientIsRegisteredCustomer",
            "Value":"Y"
          }
        ]
      },
      "ReferenceData":{
        "ReferenceItem":{
          "Key":"QueueTimeoutURL",
          "Value":"https://internalsandbox.safaricom.co.ke/mpesa/b2cresults/v1/submit"
        }
      }
    }
  }
  
```

## Business to business (B2B) (Deprecated)

This API is no longer supported

Method: `POST`

Endpoint: `{base_uri}/mpesa/b2b/v1/paymentrequest`

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`cache-control: no-cache` - optional

Body:

```json
  {
    "Initiator": " ", // This is the credential/username used to authenticate the transaction request.
    "SecurityCredential": " ", // Base64 encoded string of the B2B short code and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system.
    "CommandID": " ", // Unique command for each transaction type, possible values are: BusinessPayBill, MerchantToMerchantTransfer, MerchantTransferFromMerchantToWorking, MerchantServicesMMFAccountTransfer, AgencyFloatAdvance
    "SenderIdentifierType": " ", // Type of organization sending the transaction.
    "RecieverIdentifierType": " ", // Type of organization receiving the funds being transacted.
    "Amount": " ", // The amount being transacted.
    "PartyA": " ", // Organization’s short code initiating the transaction.
    "PartyB": " ", // Organization’s short code receiving the funds being transacted.
    "AccountReference": " ", // Account Reference mandatory for “BusinessPaybill” CommandID.
    "Remarks": " ", // Comments that are sent along with the transaction.
    "QueueTimeOutURL": "http://your_timeout_url", // The path that stores information of time out transactions.it should be properly validated to make sure that it contains the port, URI and domain name or publicly available IP.
    "ResultURL": "http://your_result_url" // The path that receives results from M-Pesa it should be properly validated to make sure that it contains the port, URI and domain name or publicly available IP.
  }
```

Response:

```json
{
    "Result":{
      "ResultType":0,
      "ResultCode":0,
      "ResultDesc":"The service request has been accepted successfully.",
      "OriginatorConversationID":"8551-61996-3",
      "ConversationID":"AG_20170727_00006baee344f4ce0796",
      "TransactionID":"LGR519G2QV",
      "ResultParameters":{
        "ResultParameter":[
          {
            "Key":"InitiatorAccountCurrentBalance",
            "Value":"{ Amount={BasicAmount=46713.00, MinimumAmount=4671300, CurrencyCode=KES}}"
          },
          {
            "Key":"DebitAccountCurrentBalance",
            "Value":"{Amount={BasicAmount=46713.00, MinimumAmount=4671300, CurrencyCode=KES}}"
          },
          {
            "Key":"Amount",
            "Value":10
          },
          {
            "Key":"DebitPartyAffectedAccountBalance",
            "Value":"Working Account|KES|46713.00|46713.00|0.00|0.00"
          },
          {
            "Key":"TransCompletedTime",
            "Value":20170727102524
          },
          {
            "Key":"DebitPartyCharges",
            "Value":"Business Pay Bill Charge|KES|77.00"
          },
          {
            "Key":"ReceiverPartyPublicName",
            "Value":"603094 - Safaricom3117"
          },
          {
            "Key":"Currency",
            "Value":"KES"
          }
        ]
      },
      "ReferenceData":{
        "ReferenceItem":[
          {
            "Key":"BillReferenceNumber",
            "Value":"aaa"
          },
          {
            "Key":"QueueTimeoutURL",
            "Value":"https://internalsandbox.safaricom.co.ke/mpesa/b2bresults/v1/submit"
          },
          {
            "Key":"Occasion"
          }
        ]
      }
    }
}
```

## Account balance query

Method: `POST`

Endpoint: `{base_uri}/mpesa/accountbalance/v1/query`

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`cache-control: no-cache` - optional

Body:

```json
    {
        "Initiator":" ", // This is the credential/username used to authenticate the transaction request.
        "SecurityCredential":" ", // Base64 encoded string of the M-Pesa short code and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system
        "CommandID":"AccountBalance", // A unique command passed to the M-Pesa system.
        "PartyA":"shortcode", // 
        "IdentifierType":"4", // 
        "Remarks":"Remarks", // Comments that are sent along with the transaction
        "QueueTimeOutURL":"https://ip_address:port/timeout_url",
        "ResultURL":"https://ip_address:port/result_url"
    }
```

Response:

```json
{
    "Result":{
      "ResultType":0,
      "ResultCode":0,
      "ResultDesc":"The service request has b een accepted successfully.",
      "OriginatorConversationID":"19464-802673-1",
      "ConversationID":"AG_20170728_0000589b6252f7f25488",
      "TransactionID":"LGS0000000",
      "ResultParameters":{
        "ResultParameter":[
          {
            "Key":"AccountBalance",
            "Value":"Working Account|KES|46713.00|46713.00|0.00|0.00&Float Account|KES|0.00|0.00|0.00|0.00&Utility Account|KES|49217.00|49217.00|0.00|0.00&Charges Paid Account|KES|-220.00|-220.00|0.00|0.00&Organization Settlement Account|KES|0.00|0.00|0.00|0.00"
          },
          {
            "Key":"BOCompletedTime",
            "Value":20170728095642
          }
        ]
      },
      "ReferenceData":{
        "ReferenceItem":{
          "Key":"QueueTimeoutURL",
          "Value":"https://internalsandbox.safaricom.co.ke/mpesa/abresults/v1/submit"
        }
      }
    }
}
  
```

## Transaction status query

Method: `POST`

Endpoint: `{base_uri}/mpesa/transactionstatus/v1/query`

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`cache-control: no-cache` - optional

Body:

```json
{
    "Initiator":" ", // The name of Initiator to initiating the request. 
    "SecurityCredential":" ", // Base64 encoded string of the M-Pesa short code and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system.
    "CommandID":"TransactionStatusQuery", // Unique command for each transaction type, possible values are: TransactionStatusQuery.
    "TransactionID":" ", // Id of transaction
    "PartyA":" ", // 
    "IdentifierType":"1", // Type of organization receiving the transaction
    "ResultURL":"https://ip_address:port/result_url",
    "QueueTimeOutURL":"https://ip_address:port/timeout_url",
    "Remarks":" ", // Comments that are sent along with the transaction.
    "Occasion":" " // Optional
}
```

Response:

```json
{
    "Result":{
      "ResultType":0,
      "ResultCode":0,
      "ResultDesc":"The service request has been accepted successfully.",
      "OriginatorConversationID":"10816-694520-2",
      "ConversationID":"AG_20170727_000059c52529a8e080bd",
      "TransactionID":"LGR0000000",
      "ResultParameters":{
        "ResultParameter":[
          {
            "Key":"ReceiptNo",
            "Value":"LGR919G2AV"
          },
          {
            "Key":"Conversation ID",
            "Value":"AG_20170727_00004492b1b6d0078fbe"
          },
          {
            "Key":"FinalizedTime",
            "Value":20170727101415
          },
          {
            "Key":"Amount",
            "Value":10
          },
          {
            "Key":"TransactionStatus",
            "Value":"Completed"
          },
          {
            "Key":"ReasonType",
            "Value":"Salary Payment via API"
          },
          {
            "Key":"TransactionReason"
          },
          {
            "Key":"DebitPartyCharges",
            "Value":"Fee For B2C Payment|KES|33.00"
          },
          {
            "Key":"DebitAccountType",
            "Value":"Utility Account"
          },
          {
            "Key":"InitiatedTime",
            "Value":20170727101415
          },
          {
            "Key":"Originator Conversation ID",
            "Value":"19455-773836-1"
          },
          {
            "Key":"CreditPartyName",
            "Value":"254708374149 - John Doe"
          },
          {
            "Key":"DebitPartyName",
            "Value":"600134 - Safaricom157"
          }
        ]
      },
      "ReferenceData":{
        "ReferenceItem":{
          "Key":"Occasion",
          "Value":"aaaa"
        }
      }
    }
  }
```

## Reversal request

Method: `POST`

Endpoint: `{base_uri}/mpesa/reversal/v1/request`

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`cache-control: no-cache` - optional

Body:

```json
{
    "Initiator":" ", // This is the credential/username used to authenticate the transaction request.
    "SecurityCredential":" ", // Base64 encoded string of the M-Pesa short code and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system.
    "CommandID":"TransactionReversal", //  	Unique command for each transaction type, possible values are: TransactionReversal.
    "TransactionID":" ", // Organization/MSISDN sending the transaction.
    "Amount":" ", //
    "ReceiverParty":" ", // Type of organization receiving the transaction.
    "RecieverIdentifierType":"11",
    "ResultURL":"https://ip_address:port/result_url",
    "QueueTimeOutURL":"https://ip_address:port/timeout_url",
    "Remarks":" ",
    "Occasion":" " // Optional
}
```

Response:

```json
{
    "Result":{
        "ResultType":0,
        "ResultCode":0,
        "ResultDesc":"The service request has been accepted successfully.",
        "OriginatorConversationID":"10819-695089-1",
        "ConversationID":"AG_20170727_00004efadacd98a01d15",
        "TransactionID":"LGR019G3J2",
        "ReferenceData":{
            "ReferenceItem":{
                "Key":"QueueTimeoutURL",
                "Value":"https://internalsandbox.safaricom.co.ke/mpesa/reversalresults/v1/submit"
            }
        }
    }
}
```

## Initiate stk push

Method: `POST`

Endpoint: `{base_uri}/mpesa/stkpush/v1/processrequest`

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`cache-control: no-cache` - optional

Body:

```json
{
    "BusinessShortCode": " ", // The organization shortcode used to receive the transaction.
    "Password": " ", // The password for encrypting the request. This is generated by base64 encoding BusinessShortcode, Passkey and Timestamp.
    "Timestamp": " ", // The timestamp of the transaction in the format yyyymmddhhiiss.
    "TransactionType": "CustomerPayBillOnline", // The transaction type to be used for this request. Only CustomerPayBillOnline is supported.
    "Amount": " ", // The amount to be transacted.
    "PartyA": " ", // The MSISDN sending the funds.
    "PartyB": " ", // The organization shortcode receiving the funds
    "PhoneNumber": " ", // The MSISDN sending the funds.
    "CallBackURL": "https://ip_address:port/callback", // The url to where responses from M-Pesa will be sent to.
    "AccountReference": " ", // Used with M-Pesa PayBills.
    "TransactionDesc": " " // A description of the transaction.
}
```

Response:

```json
  // A cancelled request
  {
    "Body":{
      "stkCallback":{
        "MerchantRequestID":"8555-67195-1",
        "CheckoutRequestID":"ws_CO_27072017151044001",
        "ResultCode":1032,
        "ResultDesc":"[STK_CB - ]Request cancelled by user"
      }
    }
  }
  
  // An accepted request
  {
    "Body":{
      "stkCallback":{
        "MerchantRequestID":"19465-780693-1",
        "CheckoutRequestID":"ws_CO_27072017154747416",
        "ResultCode":0,
        "ResultDesc":"The service request is processed successfully.",
        "CallbackMetadata":{
          "Item":[
            {
              "Name":"Amount",
              "Value":1
            },
            {
              "Name":"MpesaReceiptNumber",
              "Value":"LGR7OWQX0R"
            },
            {
              "Name":"Balance"
            },
            {
              "Name":"TransactionDate",
              "Value":20170727154800
            },
            {
              "Name":"PhoneNumber",
              "Value":254721566839
            }
          ]
        }
      }
    }
  }
  
```


## Initiate stk push query

Method: `POST`

Endpoint: `{base_uri}/mpesa/stkpushquery/v1/query`

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`cache-control: no-cache` - optional

Body:

```json
{
    "BusinessShortCode": " ", // Business Short Code
    "Password": " ", // Password
    "Timestamp": " ", // Timestamp
    "CheckoutRequestID": " " // Checkout RequestID
}
```

Response:

```json
{
    "ResponseCode":"0",
    "ResponseDescription":"The service request has been accepted successfully",
    "MerchantRequestID":"8555-67195-1",
    "CheckoutRequestID":"ws_CO_27072017151044001",
    "ResultCode":"1032",
    "ResultDesc":"[STK_CB - ]Request cancelled by user"
}
```

## Register pull transaction API URL

The original documentation can be found [here](https://documenter.getpostman.com/view/1724456/SVtTy8sd#a4230cb9-1a34-4300-949b-43cfe0844968)
This is a relatively new API, and will need a bit of introduction
The Pull Transactions API is a reconciliation API that allows a partner to query all transactions performed under their shortcode for C2B.
Pull API stores all C2B transactions done under a shortcode and are available for 48hrs. In the event that two transactions failed to reach the C2B callback URLs registered, e.g. between 1400-1415hrs, a partner can query all transactions done during that period on the shortcode. The result will have all transactions including those that were successfully sent to the C2B callback URLs and those that failed to reach the callback URLs for the period specified (1400-1415hrs)

To make use of pull, a user will need to register their urls and shortcode. The shortcode used must be for a user who has gone live and is on production

NB: Register URL API is only called once to enable pull transactions.

Below are the steps taken to make a register url call

Method: `POST`

Endpoint: `{base_uri}/pulltransactions/v1/register`

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`Content-Type: application/json` - Required

`Accept-Encoding: application/json` - optional

`cache-control: no-cache` - optional

Body:

```json
{
  "ShortCode":"600000", // Organization ShortCode that was used during Go-Live process.
  "RequestType":"Pull", // Defines the type of operation, default value is Pull
  "NominatedNumber":"0722000000", // This is Safaricom MSISDN associated with the organization account using Pull API(07XXXXXXXX or 2547XXXXXXX).
  "CallBackURL": "https://domain/path" // A CallBack URL is a valid secure URL that is used to receive notifications.
}
```

Response:

Sample Response Codes:

1000: Short code Registered Successfully

1001: ShortCode already Registered

```json
{
  "ResponseRefID": "18633-7271215-1",
  "Response Status": "1001",
  "ShortCode": "600000",
  "Response Description": "ShortCode already Registered"
}
```

## Pull transaction API

To make a pull of the missed transactions. Populate the request body with the following parameters.

NB: This API pulls transactions for a period not exceeding 48hrs.

Method: `POST`

Endpoint: `{base_uri}/pulltransactions/v1/query`

### _Request format_

headers:

`Authorization: Bearer {access_token}` - Required

`Accept: application/json` - Required

`Content-Type: application/json` - Required

`Accept-Encoding: application/json` - optional

`cache-control: no-cache` - optional

Body:

```json
{
  "ShortCode":"600000", // This is your paybill number/till number, which you expect to receive payments notifications about.
  "StartDate":"2020-08-04 8:36:00", // The start period of the missing transactions in the format of 2019-07-31 20:35:21 / 2019-07-31 19:00
  "EndDate":"2020-08-16 10:10:000", // The end of the period for the missing transactions in the format of 2019-07-31 20:35:21 / 2019-07-31 22:35
  "OffSetValue":"0" // Starts from 0. The service uses offset as opposed to page numbers. The OFF SET value allows you to specify which row to start from retrieving data. 
  // Suppose you wanted to show results 101-200. With the OFFSET keyword you type the (page number/index/offset value) 100.
}
```

Response:

Sample Response Codes:

1000: Success, transactions fetched successfully

1001: Null, No transactions available for the selected time period.The response body is "Transaction": "[[]]"

500: Failed to retrieve transactions. The short code does not have any available transactions

```json
{
  "ResponseRefID": "26178-42530161-2",
  "ResponseCode": "1000",
  "ResponseMessage": "Success",
  "Response": [
    [
      {
        "transactionId": "yzlyrEsRG1",
        "trxDate": "2020-08-05T10:13:00Z",
        "msisdn": 72200000,
        "sender": "UAT2",
        "transactiontype": "c2b-pay-bill-debit",
        "billreference": "37207636392",
        "amount": "168.00",
        "organizationname": "Daraja Pull API Test"
      },
      {
        "transactionId": "ZobjR29vdz",
        "trxDate": "2020-08-05T10:13:00Z",
        "msisdn": 72200000,
        "sender": "UAT2",
        "transactiontype": "c2b-pay-bill-debit",
        "billreference": "37207636392",
        "amount": "647.00",
        "organizationname": "Daraja Pull API Test"
      },
      {
        "transactionId": "nZ84WxLx8p",
        "trxDate": "2020-08-05T10:13:00Z",
        "msisdn": 72200000,
        "sender": "UAT2",
        "transactiontype": "c2b-pay-bill-debit",
        "billreference": "37207636392",
        "amount": "876.00",
        "organizationname": "Daraja Pull API Test"
      },
      {
        "transactionId": "9MFsu3cihH",
        "trxDate": "2020-08-05T10:13:00Z",
        "msisdn": 72200000,
        "sender": "UAT2",
        "transactiontype": "c2b-pay-bill-debit",
        "billreference": "37207636392",
        "amount": "687.00",
        "organizationname": "Daraja Pull API Test"
      },
      {
        "transactionId": "LjFzAtI088",
        "trxDate": "2020-08-05T10:13:00Z",
        "msisdn": 72200000,
        "sender": "UAT2",
        "transactiontype": "c2b-pay-bill-debit",
        "billreference": "37207636392",
        "amount": "93.00",
        "organizationname": "Daraja Pull API Test"
      }
    ]
  ]
}
```

## Errors, and error code

This is the error codes returned from http requests and their meaning

| Error Code | Meaning                                                                                  |
| ---------- | :--------------------------------------------------------------------------------------- |
| 400        | Bad Request                                                                              |
| 401        | Unauthorized                                                                             |
| 403        | Forbidden                                                                                |
| 404        | Not Found                                                                                |
| 405        | Method Not Allowed                                                                       |
| 406        | Not Acceptable – You requested a format that isn’t json                                  |
| 429        | Too Many Requests                                                                        |
| 500        | Internal Server Error – We had a problem with our server. Try again later.               |
| 503        | Service Unavailable – We’re temporarily offline for maintenance. Please try again later. |

## Identifier Types

Identifier Types

Identifier types - both sender and receiver - identify an M-Pesa transaction’s sending and receiving party as either a shortcode, a till number or a MSISDN (phone number). There are three identifier types that can be used with M-Pesa APIs.

| Identifier | Identity    |
| ---------- | :---------- |
| 1          | MSISDN      |
| 2          | Till Number |
| 4          | Shortcode   |

## M-Pesa Result and Response Codes (From gateway to client)

This describes the response codes gotten from the gateway
M-Pesa Result Codes

| Result Code  | Description
| ------------ | :------------------------------------ |
| 0            | Success                               |
| 1            | Insufficient Funds                    |
| 2            | Less Than Minimum Transaction Value   |
| 3            | More Than Maximum Transaction Value   |
| 4            |Would Exceed Daily Transfer Limit      |
| 5            | Would Exceed Minimum Balance          |
| 6            | Unresolved Primary Party              |
| 7            | Unresolved Receiver Party             |
| 8            | Would Exceed Maximum Balance          |
| 11           | Debit Account Invalid                 |
| 12           | Credit Account Invalid                |
| 13           | Unresolved Debit Account              |
| 14           | Unresolved Credit Account             |
| 15           | Duplicate Detected                    |
| 17           | Internal Failure                      |
| 20           | Unresolved Initiator                  |
| 26           | Traffic blocking condition in place   |

## M-Pesa Response Codes (from client back to gateway)

Response codes are sent from the clients endpoints back to the gateway. This is done to acknowledge that the client has received the results.

| Result Code           | Description |
| --------------------- | :---------- |
| 0                     | Success (for C2B)
| 00000000              | Success (For APIs that are not C2B)
| 1 or any other number | Rejecting the transaction

## Mpesa command ids

This are the command ids supported by mpesa, they tell the mpesa gateway which commands to perform

| Command ID | Description  |
| ---------- | :-------------------------------------------------- |
| TransactionReversal | Reversal for an erroneous C2B transaction. |
| SalaryPayment | Used to send money from an employer to employees e.g. salaries |
| BusinessPayment | Used to send money from business to customer e.g. refunds |
| PromotionPayment | Used to send money when promotions take place e.g. raffle winners. |
| AccountBalance | Used to check the balance in a paybill/buy goods account (includes utility, MMF, Merchant, Charges paid account). |
| CustomerPayBillOnline | Used to simulate a transaction taking place in the case of C2B Simulate Transaction or to initiate a transaction on behalf of the customer (STK Push). |
| TransactionStatusQuery | Used to query the details of a transaction. |
| CheckIdentity | Similar to STK push, uses M-Pesa PIN as a service. |
| BusinessPayBill | Sending funds from one paybill to another paybill |
| BusinessBuyGoods | sending funds from buy goods to another buy goods. |
| DisburseFundsToBusiness | Transfer of funds from utility to MMF account. |
| BusinessToBusinessTransfer | Transferring funds from one paybills MMF to another paybills MMF account. |
| BusinessTransferFromMMFToUtility | Transferring funds from paybills MMF to another paybills utility account. |
