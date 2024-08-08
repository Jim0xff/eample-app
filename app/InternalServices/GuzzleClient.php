<?php
namespace App\InternalServices;

use GuzzleHttp\Client;

class GuzzleClient extends AbstractGuzzleClient implements ClientContract
{
    /**
     * @var string
     */
    protected $baseUri = '';

    /**
     * @var array
     */
    protected $defaultRequestOptions = [
        'timeout' => 30,
    ];

    /**
     * GuzzleClient constructor.
     *
     * @param string $baseUri
     */
    public function __construct($baseUri)
    {
        $this->setBaseUri($baseUri);
    }

    protected function setBaseUri($uri = '')
    {
        $this->baseUri = substr($uri, -1) === '/' ? $uri : $uri . '/';
    }

    public function request($method, $uri = '', array $options = [])
    {
        $options = array_merge($this->defaultRequestOptions, $options);
        return $this->getClient()->request($method, $uri, $options);
    }

    protected function initClient()
    {
        return new Client(['base_uri' => $this->baseUri]);
    }

}