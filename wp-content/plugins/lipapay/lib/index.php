<?php
/**
 * User: simon.zhang
 * Date: 2017/12/5
 * Time: 13:45
 */
require_once 'lipapay.php';
require_once 'lipapay.sign.php';

//参数
$order_sn = date('YmdHis') .  time() . rand(1111, 9999);
$param = [];
$param['order_sn'] = $order_sn;
$param['goodsName'] = 'XXXX';
$param['goodsType'] = '2';
$param['currency'] = 'KES';
$param['amount']  = '1';
$param['buyerId']  = '1';
$data = tolipapay($param);

//file_put_contents("log.txt", Date('Y-m-d H:i:s').'request:'.json_encode($data).'\n', FILE_APPEND);


//调用表单
require_once 'lipapay.form.php';

?>



