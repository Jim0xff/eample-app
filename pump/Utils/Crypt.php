<?php namespace Pump\Utils;
class Crypt
{
    const AES_IV = '12345678123456xx';
    const AES_PRIVATE_KEY = '12345678123456xx';

    const AES_IV_PASSWORD = '12395678123456xx';
    const AES_PRIVATE_KEY_PASSWORD = '12345378123456xx';

    public static function cryptEncode($data)
    {
        return self::crypt('aesEncode', $data);
    }

    public static function cryptDecode($data)
    {
        return self::crypt('aesDecode', $data);
    }

    private static function crypt($action, $data)
    {
        return self::$action(self::AES_PRIVATE_KEY, self::AES_IV, $data);
    }

    private static function aesEncode($private_key, $iv, $data)
    {
        if (!is_numeric($data) && !is_string($data)) {
            $data = rawurlencode(json_encode($data));
        }
        $padded_data = self::zeroPad($data, 16);
        $encrypted = openssl_encrypt(
            $data,
            'aes-128-cbc',
            $private_key,
            false,
            $iv
        );
        return $encrypted;
    }

    private static function aesDecode($private_key, $iv, $encrypted_data)
    {
        $data = rawurldecode(rtrim(openssl_decrypt(
            $encrypted_data,
            'aes-128-cbc',
            $private_key,
            false,
            $iv
        ), "\0"));
        $rt = json_decode($data, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            $rt = $data;
        }
        return $rt;
    }

    private static function zeroPad($data, $pad_len)
    {
        if (strlen($data) % $pad_len == 0) {
            return $data;
        } else {
            $pad_len = $pad_len * (intval(strlen($data) / $pad_len) + 1);
            $data = str_pad($data, $pad_len, "\0");
            return $data;
        }
    }

    /**
     * 高级加密,不可解
     * @param $string
     * @return string
     */
    public static function highCrypt($string)
    {
        return md5(self::AES_IV_PASSWORD . '_' . self::AES_PRIVATE_KEY_PASSWORD . $string);
    }
}
