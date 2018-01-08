<?php
/**
 * User: simon.zhang
 * Date: 2017/12/5
 * Time: 17:05
 */

require_once 'lipapay.sign.php';

//add_action( 'admin_notices', 'hello_dolly' );
 
 //返回组装好的lipay参数
class LipaPayPaymentConfig{

    private $LIPAPAY_URL;
    private $LIPAPAY_MerchantNo;
    private $LIPAPAY_KEY;

    public function __construct($LIPAPAY_URL, $LIPAPAY_MerchantNo, $LIPAPAY_KEY)
    {
        $this->LIPAPAY_URL = $LIPAPAY_URL;
        $this->LIPAPAY_MerchantNo = $LIPAPAY_MerchantNo;
        $this->LIPAPAY_KEY = $LIPAPAY_KEY;

    }
}
function toLipaPay($param){


    $url = $param['url'];


//    $return = $LipaPay_config['RETURN'];
//    $notify = $LipaPay_config['NOTIFY'];
    $uri = dirname($_SERVER['DOCUMENT_URI']);
    $return  = 'http://'.$_SERVER['HTTP_HOST'] .$uri. '/return.php';
    $notify  = 'http://'.$_SERVER['HTTP_HOST'] .$uri. '/nitify.php';


    $LipaPay_key =$param['LipaPay_key'];
    $data = [];
    $data['merchantId'] = $param['merchantId'];
    $data['signType'] = 'MD5';
    $data['returnUrl'] = $return;
    $data['notifyUrl'] = $notify;
    $data['merchantOrderNo'] = $param['order_sn'];
    $data['amount'] = $param['amount'] * 100;
    $data['buyerId'] = $param['buyerId'];  //
    $data['goodsName'] = $param['goodsName'];
    $data['goodsType'] = $param['goodsType'];
    $data['expirationTime'] = '100000';
    $data['sourceType'] = 'B';
    $data['currency'] =$param['currency'];
    $sign = sign($data,$LipaPay_key);
    $data['sign'] = $sign;
    $data['url'] = $url;

    return $data;
}


