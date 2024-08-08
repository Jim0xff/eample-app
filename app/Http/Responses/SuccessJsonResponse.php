<?php
namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SuccessJsonResponse extends JsonResponse
{
    public function __construct($data, LengthAwarePaginator $paginator = null)
    {
        parent::__construct($this->envelop($data, $paginator), self::HTTP_OK);
    }

    public function envelop($data, LengthAwarePaginator $paginator = null)
    {
        return $paginator ? $this->envelopDataWithPaginator($data, $paginator) : $this->envelopData($data);
    }

    public function envelopData($data)
    {
        return [
            'code' => self::HTTP_OK,
            'data' => $data,
        ];
    }

    public function envelopDataWithPaginator($data, LengthAwarePaginator $paginator)
    {
        return array_merge($this->envelop($data), [
                'pagination' => [
                    'page' => $paginator->currentPage(),
                    'totalPage' => $paginator->lastPage(),
                    'total' => $paginator->total(),
                    'pageSize' => $paginator->perPage(),
                    'perPage' => $paginator->perPage()
                ]
            ]
        );
    }

}
