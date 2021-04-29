<?php

namespace HackDelta\Mpesa\Main;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use HackDelta\Mpesa\Exceptions\MpesaClientException;
use HackDelta\Mpesa\Exceptions\MpesaInternalException;
use HackDelta\Mpesa\Exceptions\MpesaServerException;

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
                $ce->getResponse()->getStatusCode()
            );
        } catch (ServerException $se) {
            throw new MpesaServerException(
                $se->getMessage(),
                $se->getResponse()->getBody()->getContents(),
                $se->getResponse()->getStatusCode()
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

        if($headers !== null) {
            $initial_config = array_merge($headers, $initial_config);
        }
        
        $client = new Client($initial_config);

        return $client->request($method, $uri, [
            'json' => $body ?? []
        ]);
    }

}
