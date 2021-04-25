# Specifications

The following describes the main Mpesa class specification standards.

## Table of contents

- [Specifications](#specifications)
  - [Table of contents](#table-of-contents)
  - [The folder structure](#the-folder-structure)
    - [src folder](#src-folder)
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
  - [To bring it all together](#to-bring-it-all-together)

## The folder structure

Here is the folder structure of the applications, for more details about what each file does scroll
below.

### src folder

`src/` - Where our source files will be stored

`src/Mpesa.ext` - The main class

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

This class is used to store our constants, it will make it easier for us to group our constants

```Java
    class MpesaConstants
    {
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

    // Singleton instance of the MpesaConfig class
    private static MpesaConfig config;

    // Initialize the class here, also initialize the sibgleton instance
    // only once
    constructor(config: MpesaConfig); 

    // Gets the Mpesa config singleton instance and allows a user to change the configurations before next
    // request
    public MpesaConfig config();

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

        // Get's the error body response as string
        // return empty string if response body is empty
        public String getErrorBody();

        // Get's the error response status message as string
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

        // Get's the error body response as string
        // return empty string if response body is empty
        public String getErrorBody();

        // Get's the error response status message as string
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

    // Used to set the shortcode
    // Allow chaining, see example below
    public MpesaConfig setShortCode(value: int);

    // Return the shortcode
    public int getShortCode();

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

    // Provides a way of checking if a configuration is set
    public boolean isConfigSet(key: string);

    // Provides redundant way of getting a specific configuration easily
    public Any getConfig(key: string);

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

    // TODO: add interfaces later

}

```

## Mpesa response class

This class unifies the JSON results gotten from a request, this allows all libraries to have a common
way of returning the results of a given request, implementation details will be given later on

```java

class MpesaResponse {

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
    // the mode determines which transactions we will be performing
    // mode allows only paybill_mode, till_mode
    MpesaC2B(config: MpesaConfig, mode: CONSTANT);

    // Set a given mode
    // allows only paybill_mode, till_mode
    // See example implementation
    public MpesaC2B setMode(mode: CONSTANT);

    // Returns the mode, since it's a constant it will point to a given mode 
    public String getMode();

    // Performs the register
    // All data is in config
    public MpesaResponse register();

    // Simulate a transaction, this is only available in sandbox 
    // amount is an unsigned int always check for negative or 0
    public MpesaResponse simulate(MSISDN: string, amount :int);

    // Initiate STK push query
    // amount is an unsigned int always check for negative or 0
    // partyB is optional can be the same as business shortcode if left empty
    // timestamp must be in the format 'yyyymmddhhiiss'
    // account reference will be needed in paybill_mode, will be ignored in till_mode
    public MpesaReposnse initiateSTKPush(amount: int, optional partyB = 0: int, 
        optinal timestamp = '': timestamp, optional account_reference = '': string, optional description = '': string);

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
    
}

```

## Mpesa bisiness to client (B2C) class

This class will handle logic of performing B2C related request, it has one method only

1. B2Cpayment

## To bring it all together

``` java
class MpesaB2C {
    
}

```
