<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use Pump\User\Dao\UserDAOModel;

final readonly class UserComplexQuery
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        // TODO implement the resolver
        $rt = UserDAOModel::query()->paginate(100);
        $result = [];
        $result['data'] = $rt->items();
        $result['pagination'] = [
           'total' => $rt->total()
        ];
        return $result;
    }
}
