<?php
/**
 * Created by PhpStorm.
 * User: Marser
 * Date: 2017/12/6
 * Time: 20:34
 */

//lipapay加密函数
function lipapay_sign($data, $lipapay_key)
{
    //检测参数是否在这个列表，排除其他参数
    $fields = [
        'merchantId',
        'signType',
        'returnUrl',
        'notifyUrl',
        'merchantOrderNo',
        'amount',
        'buyerId',
        'goodsName',
        'goodsType',
        'expirationTime',
        'sourceType',
        'currency'];

    ksort($data);
    $str = '';
    foreach ($data as $key => $value) {
        if(!in_array($key,$fields)){
            continue;
        }
        $str = $str . $key . '=' . $value . '&';
    }
    $str = substr($str, 0, strlen($str) - 1);

    $sign = md5($str . $lipapay_key);
    return $sign;
}


