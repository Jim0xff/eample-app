<?php
namespace App\InternalServices;

interface ClientContract
{
    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws
     */
    public function request($method, $uri = '', array $options = []);

}