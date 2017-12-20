<?php
/**
 * User: simon.zhang
 * Date: 2017/12/5
 * Time: 17:23
 */
require_once 'config.ini.php';
require_once 'order.php';
require_once 'lipapay.sign.php';
$lipapay_key = $lipapay_config['LIPAPAY_KEY'];

$data = $_GET;
$order_sn = $data['merchantOrderNo'];
file_put_contents("log.txt", Date('Y-m-d H:i:s').'return:'.json_encode($data).'\n', FILE_APPEND);

$lipapay_sign = $data['sign'];
unset($data['sign']);

$my_sign = sign($data,$lipapay_key);
if($my_sign==$lipapay_sign){
    if($data['status']=='SUCCESS'){
		//处理返回的逻辑
        finishOrder($data['merchantOrderNo'],$data['orderId']);
    }

}