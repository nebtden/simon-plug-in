<?php
/**
 * User: simon.zhang
 * Date: 2017/12/6
 * Time: 19:25
 */

/**
 * @param $order_sn
 * @param $lipapay_order
 */
function lipapay_finishOrder($order_sn,$lipapay_order){

    if(!hasOrderHandle($order_sn)){
        //
        handleOrder($order_sn,$lipapay_order);
    }
}

/**
 * @param $order_sn
 * 检查此order_sn 是否处理过，如果处理过，则不再处理
 */
function hasOrderHandle($order_sn){
   
}

/**
 * 真实处理订单步骤，比如更改订单状态，添加积分，发货，添加余额等操作。。
   
 */
function handleOrder($order_sn,$lipapay_order){

}

