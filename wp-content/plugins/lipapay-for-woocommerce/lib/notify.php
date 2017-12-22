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



$data = $request = $_POST;

// write the log
file_put_contents("log.txt", Date('Y-m-d H:i:s').'notify:'.json_encode($data)."\n", FILE_APPEND);

$lipapay_sign = $data['sign'];
unset($data['sign']);
$my_sign = lipapay_sign($data,$lipapay_key);
if($my_sign==$lipapay_sign){
    if($data['status']=='SUCCESS'){
        //处理逻辑
        finishOrder($data['merchantOrderNo'],$data['orderId']);
		
		//处理返回给lipapay的参数
		$return = [];
		$return['status'] = 'SUCCESS';
		$return['errorCode'] = '100';
		$return['merchantId'] = $request['merchantId'];
		$return['signType'] = 'MD5';
		$return['merchantOrderNo'] = $request['merchantOrderNo'];
		$return['orderId'] = $request['orderId'];

		$my_sign = sign($return,$lipapay_key);
		$return['sign'] =$my_sign;

		echo json_encode($return);
    }
}


