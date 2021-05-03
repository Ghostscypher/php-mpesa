<?php

namespace Hackdelta\Mpesa\Main;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Hackdelta\Mpesa\Exceptions\MpesaClientException;
use Hackdelta\Mpesa\Exceptions\MpesaInternalException;
use Hackdelta\Mpesa\Exceptions\MpesaServerException;

/**
 * This class will contain methods to perform HTTP requests
 */
class MpesaHttp 
{

    public function request(
        string $method,
        string $uri, 
        ?array $body = null, 
        ?array $headers = null,
    ): MpesaResponse
    {
        try{
            $response = $this->client($method, $uri, $body, $headers);

            return new MpesaResponse(
                $response->getBody()->getContents(),
                json_encode($response->getHeaders()),
                $response->getStatusCode()
            );
        } catch(ClientException $ce){
            throw new MpesaClientException(
                $ce->getMessage(),
                $ce->getResponse()->getBody()->getContents(),
                $ce->getResponse()->getStatusCode(),
                [
                    "url" => $uri,
                    "method" => $method,
                    "headers" => $headers,
                    "body" => $body,
                ]
            );
        } catch (ServerException $se) {
            throw new MpesaServerException(
                $se->getMessage(),
                $se->getResponse()->getBody()->getContents(),
                $se->getResponse()->getStatusCode(),
                [
                    "url" => $uri,
                    "method" => $method,
                    "headers" => $headers,
                    "body" => $body,
                ]
            );
        } catch (GuzzleException $e){
            throw new MpesaInternalException( $e->getMessage() );
        }
    }

    private function client(
        string $method,
        string $uri, 
        ?array $body = null, 
        ?array $headers = null
    )
    {
        $initial_config = [
            'stream' => true,
            'headers' => [
                'Cache-Control' => 'no-cache',
                'Accept' => 'application/json',
            ]
        ];
        
        // Merge the headers
        if($headers !== null) {
            $initial_config["headers"] = array_merge($headers, $initial_config["headers"]);
        }

        // Init the HTTP client class
        $client = new Client($initial_config);

        return $client->request($method, $uri, [
            'json' => $body ?? []
        ]);
    }

}
