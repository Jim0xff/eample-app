<?php

namespace App\InternalServices\GraphService;

use App\InternalServices\AbstractService;

class Service extends AbstractService
{

    public function baseQuery($param)
    {
        return $this->postData("metis-pump-subgraph", $param);
    }
}
