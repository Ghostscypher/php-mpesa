# Specifications

The following describes the main Mpesa class specification standards, this class is the main
entry point into our library

```c#
class Mpesa {

    const MPESA_ENDPOINTS: dictionary: key: string, value: string = [
        // Base uris
        'base_uri' -> 'https://api.safaricom.co.ke/',
        'sandbox_base_uri' -> 'https://sandbox.safaricom.co.ke/',

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

So far this is the basic outline i've managed to come up with. The following highlights the requests we are going to make to the mpesa endpoints. To see the endpoints check here [Mpesa endpoints](./mpesa_endpoints.md)

We now need to decide how we will group the related endpoints, which data they have in common, and how we can abstract things

1. Separate the requests if it is a till, or paybill
2. Create abstractions around each ans every action
