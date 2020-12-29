<?php

namespace FS\SolrBundle\Tests;

class SolrResponseFake
{
    private $response = [];

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}
