<?php
namespace App\InternalServices;

/**
 * http客户端抽象类
 */
abstract class AbstractGuzzleClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        if (is_null($this->client)) {
            $this->client = $this->initClient();
        }

        return $this->client;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    abstract protected function initClient();
}
