<?php namespace Pump\Utils;
class TimeSwitcher
{
    public static function secondsToHIS($seconds)
    {
        $H = floor($seconds / 3600);
        $M = floor(($seconds - $H * 3600) / 60);
        $S = $seconds - $H * 3600 - $M * 60;
        return [
            'H' => $H,
            'M' => $M,
            'S' => $S,
        ];
    }

    public static function secondsToHourStr($seconds, $msg)
    {
        $H = round($seconds / 3600);
        $H = $H ? $H : 1;
        return $H . $msg;
    }

    public static function getMicroSeconds()
    {
        $t = microtime();
        $t_arr = explode(' ', $t);
        $t_arr[0] = substr($t_arr[0], 2);
        $t_arr = array_reverse($t_arr);
        $t_format = implode('', $t_arr);
        return $t_format;
    }

}
