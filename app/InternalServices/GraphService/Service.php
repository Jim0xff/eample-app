<?php

namespace App\InternalServices\GraphService;

use App\InternalServices\AbstractService;

class Service extends AbstractService
{
    const QUERY_BASE = "raffle-graph";

    public function baseQuery($param)
    {
        return $this->postData(self::QUERY_BASE, $param);
    }
}
