<?php

namespace App\Http\Controllers;

use App\Adapters\LoginUser;
use App\InternalServices\S3\S3Service;
use Illuminate\Http\Request;
use Pump\Token\Service\TokenService;

class ImageController extends Controller
{
    public function uploadImg(Request $request)
    {
        $params = $request->all();
        $file = $request->file('img');
        /** @var $s3Service S3Service */
        $s3Service = resolve('s3_service');
        /** @var LoginUser $user */
        $user = auth()->user();
        $userAddress = '';
        if(!empty($user)){
            $userAddress = $user->address;
        }
        $result = $s3Service->uploadObject($file, 'pump', $userAddress, $params['scene']??'default', ['ContentType' => 'image/png']);
        return response()->json(['code' => 200, 'data' => $result]);
    }
}
