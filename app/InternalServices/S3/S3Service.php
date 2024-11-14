<?php

namespace App\InternalServices\S3;

use Aws\S3\S3Client;

class S3Service
{

     public $s3Client;

     public function __construct()
     {

         $this->s3Client = new S3Client([
             'version' => 'latest',
             'region'  => 'us-east-1',
             'endpoint' => 'https://sgp1.digitaloceanspaces.com',
             'use_path_style_endpoint' => false, // Configures to use subdomain/virtual calling format.
             'credentials' => [
                 'key'    => 'DO00X7XU744W9BRQBJ83',
                 'secret' => 'KDCZLUeRgOM1r/YEQMzoLZzFghhRrefGKFG/p9m/r78',
             ],
         ]);
     }

     public function uploadObject($file, $bucket, $user, $scene){
         $key = $this->randomKey($scene, $user, 10);
         $result = $this->s3Client->upload($bucket, $key, $file->get(), 'public-read');
         $rt = [];
         if(!empty($result) && !empty($result->get('@metadata'))){
             $rt['url'] = $result->get('@metadata')['effectiveUri'];
         }
         return $rt;
     }

     private function randomKey($scene, $user, $length):string
     {
         $bytes = random_bytes($length);
         $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
         $charCount = strlen($chars);
         $str = '';

         for ($i = 0; $i < $length; $i++) {
             $index = ord($bytes[$i]) % $charCount;
             $str .= $chars[$index];
         }

         return $scene . '_' . $user. '_' . $str;
     }
}
