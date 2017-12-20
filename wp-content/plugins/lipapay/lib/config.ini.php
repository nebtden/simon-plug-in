<?php
/**
 * User: Simon
 * Date: 2017/12/5
 * Time: 13:45
 */

 
 //
 //$return配置 当支付完成的时候，点击返回，会携带参数返回到此页面，需要配置好，并且处理逻辑
 //
 //$notify配置 当支付完成的时候，如果用户没有点击返回页面，则会通过nofify通知，为post请求。
 //return 与notify 可能同时请求，因此，请注意逻辑仅需处理一次       
 
 //下面配置的return仅供参考，实际上可直接配置为http://yourweb.com/return.php  ....
 //如果有写rewrite 也可以http://yourweb.com/lipapay/return
 //你需要保证return可以直接访问到
 
$uri = dirname($_SERVER['DOCUMENT_URI']);
if($uri!='\\'){
    $return  = 'http://'.$_SERVER['HTTP_HOST'] .$uri. '/return.php';
    $notify  = 'http://'.$_SERVER['HTTP_HOST'] .$uri. '/nitify.php';
}else{
    $return  = 'http://'.$_SERVER['HTTP_HOST'] . '/return.php';
    $notify  = 'http://'.$_SERVER['HTTP_HOST'] . '/notify.php';
}

$lipapay_config =  [
    "LIPAPAY_URL"       =>'http://sandbox.lipapay.com/api/excashier.html',
    "LIPAPAY_MerchantNo"=>'test',
    "LIPAPAY_KEY"       =>'Gw416RCMO8tD5MSUg5dok5uQGvR3rPpx',
    "RETURN" =>$return,
    "NOTIFY"=>$notify
];

//注意，不要更改lipapay_config 文件，其他地方也不要使用，否则支付不成功
global $lipapay_config;