<?php

namespace Hackdelta\Mpesa\Main;

/**
 * This class is specialized to Mpesa objects only
 */
class MpesaResponse 
{

    protected string $response_body = '';
    protected string $response_headers = '';
    protected int $status_code = 0;

    public function __construct(
        string $response_body, 
        string $response_headers, 
        int $status_code
    )
    {
        $this->response_body = trim($response_body);
        $this->response_headers = trim($response_headers);

        $this->status_code = $status_code;
    }

    public function getJSONString(): string
    {
        return $this->response_body;
    }

    public function getHeadersJSONString(): string
    {
        return $this->response_headers;
    }

    public function getStatusCode() 
    {
        return $this->status_code;
    }

    public function __toString()
    {
        return $this->getJSONString();
    }


}
