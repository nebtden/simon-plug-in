<?php
/**
 * User: simon.zhang
 * Date: 2017/12/5
 * Time: 17:05
 */
require_once 'config.ini.php';
require_once 'lipapay.sign.php';

//add_action( 'admin_notices', 'hello_dolly' );
 
 //返回组装好的lipay参数
class LipapayPaymentConfig{

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
function tolipapay($param){

    global $lipapay_config;
    $url = $lipapay_config['LIPAPAY_URL'];
    $lipapay_no = $lipapay_config['LIPAPAY_MerchantNo'];
    $lipapay_key = $lipapay_config['LIPAPAY_KEY'];
    $return = $lipapay_config['RETURN'];
    $notify = $lipapay_config['NOTIFY'];

    $data = [];
    $data['merchantId'] = $lipapay_no;
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
    $sign = sign($data,$lipapay_key);
    $data['sign'] = $sign;
    $data['url'] = $url;

    return $data;
}
?>

