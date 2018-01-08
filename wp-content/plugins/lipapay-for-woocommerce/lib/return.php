<?php
/**
 * User: simon.zhang
 * Date: 2017/12/5
 * Time: 17:23
 */
require_once 'order.php';
require_once 'lipapay.sign.php';
$LipaPay_key = $LipaPay_config['LIPAPAY_KEY'];

$data = $_GET;
$order_sn = $data['merchantOrderNo'];

$LipaPay_sign = $data['sign'];
unset($data['sign']);

$my_sign = LipaPay_sign($data,$LipaPay_key);
if($my_sign==$LipaPay_sign){
    if($data['status']=='SUCCESS'){
		//处理返回的逻辑
        finishOrder($data['merchantOrderNo'],$data['orderId']);
    }

}