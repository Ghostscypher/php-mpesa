# Specifications

The following describes the main Mpesa class specification standards.

Please check the [example implementation](./example_implementations.md) for a generic pseudocode of the
implementation of  the sepecifiations below.

## Table of contents

- [Specifications](#specifications)
  - [Table of contents](#table-of-contents)
  - [The folder structure](#the-folder-structure)
    - [src folder](#src-folder)
      - [Main folder](#main-folder)
      - [Exceptions folder](#exceptions-folder)
      - [Extras folder](#extras-folder)
    - [Tests folder](#tests-folder)
    - [Base directory](#base-directory)
  - [The mpesa constants class](#the-mpesa-constants-class)
  - [Main class](#main-class)
  - [Exceptions](#exceptions)
    - [Internal error](#internal-error)
    - [Client error](#client-error)
    - [Server error](#server-error)
  - [Mpesa auth class](#mpesa-auth-class)
  - [MPesa configuration class](#mpesa-configuration-class)
  - [Mpesa HTTP client class](#mpesa-http-client-class)
  - [Mpesa response class](#mpesa-response-class)
  - [Mpesa client to business (C2B) class](#mpesa-client-to-business-c2b-class)
  - [Mpesa business to business (B2B) client class](#mpesa-business-to-business-b2b-client-class)
  - [Mpesa bisiness to client (B2C) class](#mpesa-bisiness-to-client-b2c-class)

## The folder structure

Here is the folder structure of the applications, for more details about what each file does scroll
below.
Please note that the `.ext` is a generic extension which stands for the file extension of the library you are
creating for example we can have `Mpesa.php`, `Mpesa.cpp`, `Mpesa.py`, etc.

### src folder

`src/` - Where our source files will be stored

`src/Mpesa.ext` - The main class

#### Main folder

`Main/` - Where we will store all the classes that will be used by the library, excluding main.ext

`Main/MpesaConfig.ext` - The MpesaConfig class

`Main/MpesaAuth.ext` - The MpesaAuth class

`Main/MpesaHttp.ext` - The MpesaHttp class

`Main/MpesaReponse.ext` - The MpesaResponse class

`Main/MpesaC2B.ext` - The MpesaC2B class

`Main/MpesaB2B.ext` - The MpesaB2B class

`Main/MpesaB2C.ext` - The MpesaB2C class

#### Exceptions folder

`src/Exceptions/` - Where to store exceptions

`src/Exceptions/MpesaClientException.ext` - The client exception class

`src/Exceptions/MpesaInternalException.ext` - The internal exception class

`src/Exceptions/MpesaServerException.ext` - The server exception class

#### Extras folder

`src/Extras` - The extras folder will be used to store miscelleneous data

`src/Extras/MpesaConstants.ext` - This file will contain all constants related to our libabry

### Tests folder

`tests/` - Where we will store our tests

### Base directory

`LICENCE` - Copy of the licence, preferably MIT licence

`README.md` - An introduction to our library and how to use it for that particular language.

## The mpesa constants class

This class is used to store our constants, it will make it easier for us to group our constants.
You can copy the configs below and make them language specific.

```Java
    class MpesaConstants
    {
        // The Mpesa endpoints
        const MPESA_ENDPOINTS: dictionary: key: string, value: string = [
            // Base uris
            'base_uri' -> 'https://api.safaricom.co.ke',
            'sandbox_base_uri' -> 'https://sandbox.safaricom.co.ke',

            // Auth endpoint
            'generate_token' -> '/oauth/v1/generate?grant_type=client_credentials',

            // Payment requests
            'payment_request_b2c' -> '/mpesa/b2c/v1/paymentrequest',
            'payment_request_b2b' -> '/mpesa/b2b/v1/paymentrequest',

            // Registering callback/webhook uris
            'register_c2b' -> '/mpesa/c2b/v1/registerurl',
            'simulate_c2b' -> '/mpesa/c2b/v1/simulate',

            // Transaction queries
            'account_balance' -> '/mpesa/accountbalance/v1/query',
            'transaction_status' -> '/mpesa/transactionstatus/v1/query',

            // Reversal
            'reversal' -> '/mpesa/reversal/v1/request',

            // Stk related
            'stk_push' -> '/mpesa/stkpush/v1/processrequest',
            'stk_push_query' -> '/mpesa/stkpushquery/v1/query',
        ];

        // Identifier types
        const MPESA_IDNTIFIER_TYPE_MSISDN = '1';
        const MPESA_IDNTIFIER_TYPE_TILL = '2';
        const MPESA_IDNTIFIER_TYPE_PAYBILL = '4';
        const MPESA_IDNTIFIER_TYPE_SHORTCODE = '4';

        // Http codes
        // 2xx series
        const MPESA_HTTP_OK = 200;

        // 4xx series
        const MPESA_HTTP_BAD_REQUEST = 400;
        const MPESA_HTTP_UNATHORIZED = 401;
        const MPESA_HTTP_FORBIDDDEN = 403;
        const MPESA_HTTP_NOT_FOUND = 404;
        const MPESA_HTTP_METHOD_NOT_ALLOWED = 405;
        const MPESA_HTTP_NOT_ACCEPTABLE = 406;
        const MPESA_HTTP_TOO_MANY_REQUESTS = 429;

        // 5xx series
        const MPESA_HTTP_INTERNAL_SERVER_ERROR = 500;
        const MPESA_HTTP_SERVICE_UNAVAILABLE = 503;


        // Gateway to client status code
        const MPESA_GATEWAY_TO_CLIENT_SUCCESS = 0;

        const MPESA_GATEWAY_TO_CLIENT_INSUFFICIENT_FUNDS = 1;

        const MPESA_GATEWAY_TO_CLIENT_LESS_THAN_MAX_TRANSACTION_VALUE = 2;
        const MPESA_GATEWAY_TO_CLIENT_MORE_THAN_MAX_TRANSACTION_VALUE = 3;

        const MPESA_GATEWAY_TO_CLIENT_WOULD_EXCEED_DAILY_TRANSFER_LIMIT = 4;
        const MPESA_GATEWAY_TO_CLIENT_WOULD_EXCEED_MINIMUM_BALANCE = 5;

        const MPESA_GATEWAY_TO_CLIENT_UNRESOLVED_PRIMARY_PARTY = 6;
        const MPESA_GATEWAY_TO_CLIENT_UNRESOLVED_RECIEVER_PARTY = 7;
        
        const MPESA_GATEWAY_TO_CLIENT_WOULD_EXCEED_MAXIMUM_BALANCE = 8;

        const MPESA_GATEWAY_TO_CLIENT_INVALID_DEBIT_ACCOUNT = 11;
        const MPESA_GATEWAY_TO_CLIENT_INVALID_CREDIT_ACCOUNT = 12;

        const MPESA_GATEWAY_TO_CLIENT_UNRESOLVED_DEBIT_ACCOUNT = 13;
        const MPESA_GATEWAY_TO_CLIENT_UNRESOLVED_CREDIT_ACCOUNT = 14;

        const MPESA_GATEWAY_TO_CLIENT_DUPLICATE_DETECTED = 15;

        const MPESA_GATEWAY_TO_CLIENT_INTERNAL_FAILURE = 17;

        const MPESA_GATEWAY_TO_CLIENT_UNRESOLVED_INITIATOR = 20;

        const MPESA_GATEWAY_TO_CLIENT_TRAFFIC_BLOCKING_CONDITION_IN_PLACE = 26;
        

        // Client to gateway status code
        const MPESA_CLIENT_TO_GATEWAY_SUCCESS_C2B = '0';
        const MPESA_CLIENT_TO_GATEWAY_SUCCESS_OTHERS = '00000000';
        const MPESA_CLIENT_TO_GATEWAY_REJECT = '1';

    }
```

## Main class

This class is the main entry point into our library. Moreover this class will contain functions to perform general
requests such as

1. Account balance query
2. Tranasction status query
3. Reversal request

The code below shows what is expected to be in the mpesa classes

```c#
class Mpesa {

    // Create singleton instance of the following classes
    private static MpesaConfig config;
    private static MpesaB2C B2C;
    private static MpesaC2B C2B;
    private static MpesaB2B B2B;

    // Initialize the class here, also initialize the sibgleton instance
    // only once
    constructor(config: MpesaConfig); 

    // Gets the Mpesa config singleton instance and allows a user to change the configurations before next
    // request
    public MpesaConfig config();

    // Returns an instance of the MpesaC2B class
    public MpesaC2B C2B();

    // Returns an instance of the MpesaB2B class
    public MpesaB2B B2B();

    // Returns an instance of the MpesaB2C class
    public MpesaB2C B2C();

    // The following specifies the general requests found in this class
    // these are:
    //      1. Account balance query
    //      2. Transaction status query
    //      3. Reversal

    // Account balance query
    // Optional remarks sent with the request
    public MpesaReponse checkBalance(optional remarks='remarks': string);

    // Transaction status, check the transaction status of an mpesa code
    // transaction_id: This is the mpesa code
    // Remarks: comments sent along with the tranasction
    // Occasion: additional data sent with the tranasaction
    public MpesaReponse checkTransactionStatus(
        transaction_id: string,
        optional remarks='remarks': string,
        optional occasion='': string
    );

    // Initiate reversal request
    // transaction_id: The npesa code.
    // amount: The amount that is being reversed
    // receiver_party: the shortcode, or MSISDN that recieved the payment
    // receiver_identifier_type: constant that shows the reciever identifier type
    //         possible values are; TODO: write possible values
    public MpesaReponse reverseTransaction(
        transaction_id: string,
        amount: int,
        receiver_party: string,
        receiver_identifier_type: CONSTANT,
        optional remarks='remarks': string,
        optional occasion='': string
    );

}
```

## Exceptions

Here is an outline of the various exceptions that will be used by our library.
There are three main types of errors that can occur in our application

1. Internal error, i.e. we have determined that the user has supplied invalid data and we reject the request
for example the user wants to perform a B2C transaction but he/she hasn't specified the initiator name
and password.
2. Client error - This error occurs once the basic validation has passed and we have confirmed that everything
is ok, so we have sent a request to the mpesa gateway which has returned the 4xx series error codes e.g. 403
to specify that the request is unauthorized.
3. Server error - This error occurs when there is an error in the mpesa gateway, this is not the clients fault.
e.g. error 503 - Service temporarily unavailable.

The following are the specifications for the error codes

### Internal error

```java

    class MpesaInternalException extends Exception
    {
        // Initialize the error here with the details of the 
        // exception
        constructor(message: string);

        // Gets the error message as string
        // you may override the default `getMessage` if you wish
        // examples include, `service temporarily unavailable`
        public String getMessage();
    }

```

### Client error

```java

    class MpesaClientException extends Exception
    {
        // Initialize the error here with the details of the 
        // exception
        constructor(message: string);

        // Gets the https response status code
        public int getCode();

        // Gets the error body response as string
        // return empty string if response body is empty
        public String getErrorBody();

        // Gets the error response status message as string
        // you may override the default `getMessage()` if you wish
        // examples include, `invalid authentication credentials`
        public String getMessage();

    }

```

### Server error

```java

    class MpesaServerException extends Exception
    {
        // Initialize the error here with the details of the 
        // exception
        constructor(message: string);

        // Gets the https response status code
        public int getCode();

        // Gets the error body response as string
        // return empty string if response body is empty
        public String getErrorBody();

        // Gets the error response status message as string
        // you may override the default `getMessage` if you wish
        // examples include, `service temporarily unavailable`
        public String getMessage();

    }

```

The following highlights the requests we are going to make to the mpesa endpoints. To see the endpoints check here [Mpesa endpoints](./mpesa_endpoints.md)

## Mpesa auth class

This is a small helper class whose sole purpose is to store the mpesa authetication credentials, to avoid recreating, the crentials over and over again

```java

class MpesaAuth {

    // Singleton class
    private static MpesaAuth mpesaAuth;

    // Contructor, initialize the singleton class
    MpesaAuth();

    // Used to generate the consumer keys and secret
    public static MpesaAuth getAuthToken(consumer_key: string, consumer_secret: string);

    // Check whether the credentials has expired
    public boolean hasExpired();

    // Return UNIX timestamp of when the token will expire
    public long getExpiresAtTime();

    // Return the authentication token
    public String getToken();

}

```

## MPesa configuration class

This class will hold a list of cinfigurations that will need to be passed between different classes
Please note that this class should allowchaining, will also add helper methods for ease of changing
configs

```java

class MpesaConfig {

    // Default constructor
    // Set defaukt environment as sandbox
    MpesaConfig(); // --> Must be public for java guys

    // This constructor recieves the configuration as an array
    // and parses it, dictionary format will be shown below
    MpesaConfig(config: dictionary: key: string, value: any);

    // This constructor recieves the configuration as a string
    // and parses it, string format will be shown below
    MpesaConfig(config: string);

    // Set development environment
    // Only accepts sandbox, production
    public MpesaConfig setEnvironment(is_sandbox: boolean);

    // Return the development environment as string
    public String getEnvironment();

    // Provides redundancy for the development environment
    public boolean isSandboxEnvironment();
    public boolean isProductionEnvironment();

    // Used to set the consumer key, remember to regenerate the credentials
    // Allow chaining, see example below
    public MpesaConfig setConsumerKey(value: string);

    // Return the consumer key
    public String getConsumerKey();

    // Used to set the consumer secret, remember to regenerate the credentials
    // Allow chaining, see example below
    public MpesaConfig setConsumerSecret(value: string);

    // Return the consumer secret
    public String getConsumerSecret();

    // Generates the credentials and returns it
    public String getCredential();

    // Gets the authentication token as a key value pair
    // token: auth token
    // expires: unsigned integer
    public MpesaAuth getAuth();

    // Used to set the shortcode, this is usually the
    // partyA or partyB depending on the transaction
    // identidier_type, indicates the kind of shortcode this is
    // allowed are IDENTIFIER_TYPE_TILL, IDENTIFIER_TYPE_PAYBILL
    // Allow chaining, see example below
    public MpesaConfig setShortCode(value: string, identifier_type: CONSTANT);

    // Return the shortcode
    public String getShortCode();

    // The business short code is often the head office number
    // this is usually used to initiate an stk tranasction
    // if left blank we assume the business short code is the same as
    // short code
    public MpesaConfig setBusinessShortCode(value: string);

    // Return the business short code
    public string getBusinessShortCode();

    // Return the identifier type
    public String getIdentifierType();

    // Used to set the confirmationURL
    // Allow chaining, see example below
    public MpesaConfig setConfirmationURL(value: string);

    // Return the confirmationURL
    public String getConfirmationURL();

    // Used to set the validationURL
    // Allow chaining, see example below
    public MpesaConfig setValidationURL(value: string);

    // Return the validationURL
    public String getValidationURL();

    // Used to set the queueTimeoutURL
    // Allow chaining, see example below
    public MpesaConfig setQueueTimeoutURL(value: string);

    // Return the queueTimeoutURL
    public String getQueueTimeoutURL();

    // Used to set the resultURL
    // Allow chaining, see example below
    public MpesaConfig setValidationURL(value: string);

    // Return the resultURL
    public String getResultURL();

    // Used to set the STK callback URL
    // Allow chaining, see example below
    public MpesaConfig setSTKCallbackURL(value: string);

    // Return the STK callback
    public String getSTKCallbackURL();   

    // Helper method to get the UTL via url name
    // see example usage below
    public String url(name: string); 

    // Used to set the initiator name/same to initiator
    // Allow chaining, see example below
    public MpesaConfig setInitiatorName(value: string);

    // Return the initiator name
    public String getInitiatorName();

    // Return the initiator name
    // simply call getInitiatorName() in this method
    // provided as a redundancy please note that initiatorName and initiator mean the same thing
    public String getInitiator();

    // Set the password
    // This password will be used in the generation of the security credential
    // itis also used in initializing stk push requests
    public MpesaConfig setPassword(value: string);

    // Returns the mpesa pasword
    public string getPassword();

    // Provides a way of checking if a configuration is set
    // exists is an alias for isConfigSet
    public boolean isConfigSet(key: string);
    public boolean exists(key: string);

    // Provides redundant way of getting a specific configuration easily
    public String getConfig(key: string);

}

```

## Mpesa HTTP client class

This is the class that will contain the logic for sending and recieveing http requests, it is a simple class
with only one purpose i.e. handle the sending and recieving of http requests, mist classes will inherit from this

```java

/**
* This class shouldn't be called directly and is for internal use only
*/
class MpesaHttp {

    // Cobsrructor pass in the authentication data to be used for each request
    MpesaHttp(auth: MpesaAuth);

    // This will be used to send Http requests
    // The method refers to HTTP method to send this can be GET, POST, PUT, PATCH, DELETE
    // body: The body of the request, this is the data that will be sent with the request
    // headers: this refers to extra headests that will be sent with the current request
    //         'accept: application/json' and 'cache-control: no-cache' is included by default
    // options: this is any other configurations not included but need to be added for the request
    public MpesaResponse request(
        method: string, 
        optional body: dictionary(key: string, value: string) = null,
        optional dictionary(key: string, value: string) = null, 
        optional dictionary(key: string, value: string) = null
    );

    // Include any number of helper methods you might find necessary

}

```

## Mpesa response class

This class unifies the JSON results gotten from a request, this allows all libraries to have a common
way of returning the results of a given request, implementation details will be given later on

```java

class MpesaResponse {

    // Constructor, initialize this with json reponse string,
    // You can parse this to produce key value pairs
    MpesaReponse(response: string);

    // Returns the original json response as string
    // the user can parse this if necessary
    public string getJSONString();

    // Returns a specific key in the json reponse
    // You can use recursive walk to find the key
    // even more efficiently, cache the results of the walk
    // for direct access, the data returned might be of any type including
    // another response
    public any get(key: string);

}

```

## Mpesa client to business (C2B) class

This class will contain logic for handling all the client to business requests/logic this includes

1. Register and confirmationURL/ValidationURL endpoints
2. Simulate transaction - Only available in sandbox environment
3. Initiate an STK push request
4. Check the status of the STK push i.e. STK push query

``` java
class MpesaC2B {
    // Constructor
    // We need to pass in the config by ref
    // The command id, of the shortcode this is will be determined by the shortcode identifier type
    MpesaC2B(config: MpesaConfig);

    // Used to overwrite the default config set 
    public MpesaC2B setConfig(config: MpesaConfig);

    // Return the current config
    public MpesaConfig getConfig();

    // Performs the register
    // All data is in config
    public MpesaResponse register();

    // Simulate a transaction, this is only available in sandbox 
    // amount is an unsigned int always check for negative or 0
    public MpesaResponse simulate(MSISDN: string, amount :int);

    // Initiate STK push query
    // amount: is an unsigned int always check for negative or 0
    // to: the MSISDN sending the funds
    // account_reference: 
    // timestamp: must be in the format 'yyyymmddhhiiss'
    public MpesaResponse initiateSTKPush(
        amount: int, 
        to: string, 
        optional account_reference = '': string, 
        optinal timestamp = '': string, 
        optional description = '': string);

    // Query the mpesa client gateway to check for the status of an STK push
    // We generate our timestamp if it is not filled, timestamp must be in the format 'yyyymmddhhiiss'
    public MpesaResponse STKPushQuery(checkout_rquest_id: string, optional timestamp = '': string);

}

```

## Mpesa business to business (B2B) client class

This class will handle the logic of performing a B2B related request, it has one method only

1. B2Bpayment

``` java
class MpesaB2B {
    
    // Constructor
    // Pass in the mpesa config by ref, this configuration will be immutable
    // command_id will be a constant specifiying the commandID
    // TODO: Add the supported command ids here
    MpesaB2B(config: MpesaConfig, command_id: CONSTANT);

    // Used to overwrite the default config set 
    public MpesaB2B setConfig(config: MpesaConfig);

    // Return the current config
    public MpesaConfig getConfig();

    // Perform a B2B transaction
    // amount: amount to transact
    // to: Organization’s short code recieving funds being transacted.
    // receiver_identifier_type: specifies the indetifier type of the receiver
    //       supported types incluce: TODO: add supported constants here
    // account_reference: optional account sent if we are using paybill
    // remarks: comments sent along with the transaction
    public MpesaRepsonse send(
        amount: int, 
        to: string,
        receiver_identifier_type: CONSTANT,
        optional account_reference = '': string,
        optional remarks = 'remarks': string
    );

    // Replace the current command id
    public MpesaB2B setCommandID(command_id: CONSTANT);

    // Returns the current command ID
    public string getCommandID();

}

```

## Mpesa bisiness to client (B2C) class

This class will handle logic of performing B2C related request, it has one method only

1. B2Cpayment

``` java
class MpesaB2C {
    // Constructor
    // Pass in the mpesa config by ref, this configuration will be immutable
    // command_id will be a constant specifiying the commandID
    // TODO: Add the supported command ids here
    MpesaB2C(config: MpesaConfig, command_id: CONSTANT);

    // Used to overwrite the default config set 
    public MpesaB2C setConfig(config: MpesaConfig);

    // Return the current config
    public MpesaConfig getConfig();

    // Replace the current command id
    public MpesaB2C setCommandID(command_id: CONSTANT);

    // Returns the current command ID
    public string getCommandID();

    // Perform a B2C transaction
    // amount: amount to transact
    // to: MSISDN recieving funds being transacted.
    // account_reference: optional account sent if we are using paybill
    // remarks: comments sent along with the transaction
    // occasion: optional description sent along with the tranasction
    public MpesaRepsonse send(
        amount: int, 
        to: string,
        optional account_reference = '': string,
        optional remarks = 'remarks': string,
        optinal occasion = '': string
    );

}

```
