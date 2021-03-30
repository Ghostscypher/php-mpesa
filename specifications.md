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

This class is the main entry point into our library, below is a basic outline of how the mpesa
class might look like

```c#
class Mpesa {
    // Initialize the class here including the base uri endpoints
    constructor(consumer_key: string, consumer_secret: string, is_development = true: boolean, optional); 

    // Used to set the consumer key, remember to regenerate the credentials
    // Allow chaining, see example below
    public Mpesa setConsumerKey(value: string);

    // Used to set the consumer secret, remember to regenerate the credentials
    // Allow chaining, see example below
    public Mpesa setConsumerSecret(value: string);

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
        // you may ovveride the default `getMessage()` if you wish
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
        constructor($message);

        // Gets the https response status code
        public int getCode();

        // Get's the error body response as string
        // return empty string if response body is empty
        public String getErrorBody();

        // Get's the error response status message as string
        // you may ovveride the default `getMessage` if you wish
        // examples include, `service temporarily unavailable`
        public String getMessage();

    }

```

So far this is the basic outline i've managed to come up with. The following highlights the requests we are going to make to the mpesa endpoints. To see the endpoints check here [Mpesa endpoints](./mpesa_endpoints.md)

We now need to decide how we will group the related endpoints, which data they have in common, and how we can abstract things

1. Separate the requests if it is a till, or paybill
2. Create abstractions around each and every action
