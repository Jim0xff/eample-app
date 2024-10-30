<?php

namespace Pump\User\Dao;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserDAOModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'address';

    protected $keyType = 'string';

    /**
     * batch query user by address list
     * @param $addressList
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUsersByAddressList($addressList)
    {

        return self::query()->whereIn('address', $addressList)
            ->orderBy('created_at', 'desc')->get();

    }

    public static function getUserByAddressForLock($address){
        return self::query()->lockForUpdate()->where('address', $address)->first();
    }


}
