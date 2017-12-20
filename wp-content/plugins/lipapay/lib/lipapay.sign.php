<?php
/**
 * Created by PhpStorm.
 * User: Marser
 * Date: 2017/12/6
 * Time: 20:34
 */

//lipapay加密函数
function sign($data, $lipapay_key)
{
    ksort($data);
    $str = '';
    foreach ($data as $key => $value) {
        $str = $str . $key . '=' . $value . '&';
    }
    $str = substr($str, 0, strlen($str) - 1);

    $sign = md5($str . $lipapay_key);
    return $sign;
}


