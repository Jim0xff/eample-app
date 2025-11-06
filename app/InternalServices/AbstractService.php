<?php

namespace App\InternalServices;


use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Mockery\Exception\RuntimeException;

abstract class AbstractService
{
    /**
     * @var ClientContract
     */
    protected $client;

    /**
     * AbstractService constructor.
     *
     * @param ClientContract $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array
     */
    protected function parseJSON($response)
    {
        $body = $response->getBody();

        if (empty($body)) {
            // TODO: throw
        }

        $contents = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            // TODO: throw
        }

        $contents = $this->fillEnvelope($contents);

        return $contents;
    }


    /**
     * @param mixed $contents
     *
     * @return mixed
     */
    protected function fillEnvelope($contents)
    {
        if (is_null($contents)) {
            $contents = [];
        }
        if (is_array($contents) && isset($contents['code']) && !isset($contents['data'])) {
            $contents['data'] = [];
        }

        return $contents;
    }

    /**
     * @param array $contents
     */
    protected function checkCode(array $contents)
    {
        if (isset($contents['code'])) {
            $code = intval($contents['code']);
            if ($code < 0) {
                $code = 600 - $code;
            }
            if ($code == 404 || $code == 500) {
                throw new TransferException($contents['resultMessage'], $code);
            }
            if ($code >= 400) {
                throw new DomainException($contents['resultMessage'], $code);
            }
        }
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     * @throws TransferException
     */
    protected function requestAndParse($method, $uri = '', array $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (\RuntimeException $e) {
            throw new TransferException($e->getMessage(), $e->getCode(), $e);
        }
        $contents = $this->parseJSON($response);
        //记录错误日志
        \Log::info("InternalServices request", [$method, $uri, $options]);
        //$this->checkCode($contents);

        return $contents;
    }

    /**
     * @param string $uri
     * @param array $params
     * @return array
     */
    public function getData($uri, array $params = [])
    {
        $startTime = microtime(true);
        $rt = $this->requestAndParse('GET', $uri, ['query' => $params]);
        Log::info("http get,uri:".$uri." params:".json_encode($params)." duration:" . (microtime(true) - $startTime)*1000);
        return $rt;
    }

    public function getDataWithHeaders($uri, array $options = [])
    {
        $startTime = microtime(true);
        $rt = $this->requestAndParse('GET', $uri, $options);
        Log::info("http get,uri:".$uri." params:".json_encode($options)." duration:" . (microtime(true) - $startTime)*1000);
        return $rt;
    }

    /**
     * @param $uri
     * @param array $params
     * @param null $prefix
     * @return array
     */
    public function postData($uri, $params = [], $prefix = null)
    {
        $startTime = microtime(true);
        $key = is_array($params) ? 'json' : 'body';

        if ($prefix) $key = $prefix;

        $rt = $this->requestAndParse('POST', $uri, [$key => $params]);
        Log::info("http post,uri:".$uri." params:".json_encode($params)." duration:" . (microtime(true) - $startTime)*1000);
        return $rt;
    }

    public function postDataWithHeaders($uri, $options = [], $prefix = null)
    {
        $startTime = microtime(true);

        if ($prefix) $key = $prefix;

        $rt = $this->requestAndParse('POST', $uri, $options);
        Log::info("http post,uri:".$uri." params:".json_encode($options)." duration:" . (microtime(true) - $startTime)*1000);
        return $rt;
    }

    /**
     * @param string $uri
     * @param array|string $params
     * @return array
     */
    public function postStringData($uri, $params = [])
    {
        $key = is_array($params) ? 'json' : 'body';

        return $this->requestAndParseOther('POST', $uri, [$key => $params]);

    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     * @throws TransferException
     */
    protected function requestAndParseOther($method, $uri = '', array $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (\RuntimeException $e) {
            throw new TransferException($e->getMessage(), $e->getCode(), $e);
        }

        $content = $response->getBody()->getContents();
        preg_match_all("/(?<=data\":)\s*\{(.*?)\}/", $content, $data);

        return $data[1][0];
    }
}
