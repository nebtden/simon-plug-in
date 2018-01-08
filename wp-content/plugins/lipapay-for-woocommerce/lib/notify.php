<?php
/**
 * User: simon.zhang
 * Date: 2017/12/5
 * Time: 17:23
 */

require_once 'order.php';
require_once 'lipapay.sign.php';
//$LipaPay_key = $LipaPay_config['LIPAPAY_KEY'];



$data = $request = $_POST;


$LipaPay_sign = $data['sign'];
unset($data['sign']);
$my_sign = LipaPay_sign($data,$LipaPay_key);
if($my_sign==$LipaPay_sign){
    if($data['status']=='SUCCESS'){
        //处理逻辑
        finishOrder($data['merchantOrderNo'],$data['orderId']);
		
		//处理返回给LipaPay的参数
		$return = [];
		$return['status'] = 'SUCCESS';
		$return['errorCode'] = '100';
		$return['merchantId'] = $request['merchantId'];
		$return['signType'] = 'MD5';
		$return['merchantOrderNo'] = $request['merchantOrderNo'];
		$return['orderId'] = $request['orderId'];

		$my_sign = sign($return,$LipaPay_key);
		$return['sign'] =$my_sign;

		echo json_encode($return);
    }
}


