<?php
namespace App\Http\Responses;

use Illuminate\Http\JsonResponse as IlluminateJsonResponse;

class JsonResponse extends IlluminateJsonResponse
{
    /**
     * JsonResponse constructor.
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     */
    public function __construct($data = null, $status = 200, array $headers = [])
    {
        parent::__construct($data, $status, $headers, JSON_UNESCAPED_UNICODE);
    }
}
