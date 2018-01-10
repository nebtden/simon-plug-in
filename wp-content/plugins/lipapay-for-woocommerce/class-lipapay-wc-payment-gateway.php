<?php

if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly

require_once 'lib/lipapay.php';
require_once 'lib/lipapay.sign.php';

class LIPAPAYWCPaymentGateway extends WC_Payment_Gateway {
    private $config;
    
	public function __construct() {

//		array_push($this->supports,'refunds');

		$this->id = WC_LIPAPAY_ID;
		$this->icon =WC_LIPAPAY_URL. '/images/logo.png';
		$this->has_fields = false;
		
		$this->method_title = 'LipaPay for WooCommerce'; // checkout option title
	    $this->method_description='author:simon.zhang@kilimall.com  ';
	   
		$this->init_form_fields ();
		$this->init_settings ();
		
		$this->title = 'LipaPay';
		$this->description = 'Lipapay is a leading online payment  provider in Africa that guarantee money-back  & data encryption.';
		

	}
	function init_form_fields() {
	    $this->form_fields = array (
	        'enabled' => array (
	            'title' => __ ( 'Enable/Disable', 'LipaPay' ),
	            'type' => 'checkbox',
	            'label' => __ ( 'Enable LipaPay Payment', 'LipaPay' ),
	            'default' => 'yes'
	        ),


	        'LIPAPAY_URL' => array (
	            'title' => __ ( 'LIPAPAY SERVER URL'),
	            'type' => 'select',
                'default' => 'testing',
                'options' => [
                    'testing'=>'Testing environment',
                    'production'=>'Production environment'],
	            'description' => "if you need to test the payment, please select the 'Testing environment', or else select the 'Production environment'",

	        ),
	        'LIPAPAY_MerchantNo' => array (
	            'title' => __ ( 'Merchant  Lipapay No.','Please enter the Merchant  Lipapay No.  （Required）'),
	            'type' => 'text',
	            'description' => "If you do not have, please contact lipapay for application.(info@lipapay.com)",
	            'css' => 'width:400px'
	        ),
	        'LIPAPAY_KEY' => array (
	            'title' => __ ( 'Merchant Lipapay Key', 'Please enter the Merchant  Lipapay No. (Required)' ),
	            'type' => 'text',
	            'description' => "If you do not have, please contact lipapay for application.(info@lipapay.com)",
	            'css' => 'width:400px',
	            //'desc_tip' => true
	        ),
            'LipaPay_monetary_unit' => array (
                'title' => __ ( 'Currency', 'KES' ),
                'type' => 'select',
                'options' => ['KES'=>'KES','NGN'=>'NGN'],
                'description' => 'Please choose the currency.',
                'css' => 'width:400px',
                //'desc_tip' => true
            )

	    );
	}

	
	public function process_payment($order_id) {
	    $order = new WC_Order ( $order_id );
	    return array (
	        'result' => 'success',
	        'redirect' => $order->get_checkout_payment_url ( true )
	    );
	}

    public  function woocommerce_LipaPay_add_gateway( $methods ) {
        $methods[] = 'LIPAPAYWCPaymentGateway';
        return $methods;
    }
	
	/**
	 * 
	 * @param WC_Order $order
	 * @param number $limit
	 * @param string $trimmarker
	 */
	public  function get_order_title($order,$limit=32,$trimmarker='...'){
	    $id = method_exists($order, 'get_id')?$order->get_id():$order->id;
		$title="#{$id}|".get_option('blogname');
		
		$order_items =$order->get_items();
		if($order_items&&count($order_items)>0){
		    $title="#{$id}|";
		    $index=0;
		    foreach ($order_items as $item_id =>$item){
		        $title.= $item['name'];
		        if($index++>0){
		            $title.='...';
		            break;
		        }
		    }    
		}
		
		return apply_filters('LipaPay_wc_get_order_title', mb_strimwidth ( $title, 0,32, '...','utf-8'));
	}
	
	public function get_order_status() {
		$order_id = isset($_POST ['orderId'])?$_POST ['orderId']:'';
		$order = new WC_Order ( $order_id );
		$isPaid = ! $order->needs_payment ();
	
		echo json_encode ( array (
		    'status' =>$isPaid? 'paid':'unpaid',
		    'url' => $this->get_return_url ( $order )
		));
		
		exit;
	}
	
	function wp_enqueue_scripts() {
		$orderId = get_query_var ( 'order-pay' );
		$order = new WC_Order ( $orderId );
		$payment_method = method_exists($order, 'get_payment_method')?$order->get_payment_method():$order->payment_method;
		if ($this->id == $payment_method) {
			if (is_checkout_pay_page () && ! isset ( $_GET ['pay_for_order'] )) {
			    
			    wp_enqueue_script ( 'LIPAPAY_JS_QRCODE', XH_WC_WeChat_URL. '/js/qrcode.js', array (), XH_WC_WeChat_VERSION );
				wp_enqueue_script ( 'LIPAPAY_JS_CHECKOUT', XH_WC_WeChat_URL. '/js/checkout.js', array ('jquery','XH_WECHAT_JS_QRCODE' ), XH_WC_WeChat_VERSION );
				
			}
		}
	}

    public function check_LipaPay_response(){

        $LipaPay_key =$this->get_option('LIPAPAY_KEY');
        if($_POST && isset($_POST['merchantOrderNo'])){
            $data = $request = $_POST;


            $LipaPay_sign = $data['sign'];
            unset($data['sign']);
            $my_sign = LipaPay_sign($data,$LipaPay_key);
            $result = $my_sign==$LipaPay_sign?:true;false;
            $result = true;
            if($result){
                if($data['status']=='SUCCESS'){
                    $order = new WC_Order($data['merchantOrderNo']);
                    try{
                        if(!$order){
                            throw new Exception('Unknow Order (id:'.$data['merchantOrderNo'].')');
                        }

                        if($order->needs_payment()){
                            $order->payment_complete(isset($data['merchantOrderNo'])?$data['merchantOrderNo']:'');
                        }
                    }catch(Exception $e){
                        //looger
                        $logger = new WC_Logger();
                        $logger->add( 'LipaPay_payment', $e->getMessage() );

                        $params = array(
                            'action'=>'fail',
                            'appid'=>$this->get_option('LIPAPAY_KEY'),
                            'errcode'=>$e->getCode(),
                            'errmsg'=>$e->getMessage()
                        );

                        $params['hash']= $LipaPay_sign;
                        ob_clean();
                        print json_encode($params);
                        exit;
                    }

                    //处理返回给LipaPay的参数
                    $return = [];
                    $return['status'] = 'SUCCESS';
                    $return['errorCode'] = '100';
                    $return['merchantId'] = $request['merchantId'];
                    $return['signType'] = 'MD5';
                    $return['merchantOrderNo'] = $request['merchantOrderNo'];
                    $return['orderId'] = $request['orderId'];

                    $my_sign = LipaPay_sign($return,$LipaPay_key);
                    $return['sign'] =$my_sign;

                    echo json_encode($return);
                    exit();
                }
            }
        }


    }


	/**
	 * 
	 * @param WC_Order $order
	 */
	function receipt_page($order_id) {
	    $order = new WC_Order($order_id);
	    if(!$order||!$order->needs_payment()){
	        wp_redirect($this->get_return_url($order));
	        exit;
	    }



        //参数
        $returnUrl  = $this->get_return_url ( $order );
        $notifyUrl  = WC()->api_request_url( 'LIPAPAYWCPaymentGateway' );

        $order_sn = $order_id;
        $param = [];
        $param['merchantOrderNo'] = $merchantOrderNo = $order_sn;
        $param['goodsName'] = $goodsName = $this->get_order_title($order);
        $param['goodsType'] = $goodsType = '2';
        $param['returnUrl'] = $returnUrl;
        $param['notifyUrl'] = $notifyUrl;
        $param['signType'] = $signType = 'MD5';
        $param['currency'] = $currency = $this->get_option('LipaPay_monetary_unit');
        $param['merchantId'] = $merchantId = $this->get_option('LIPAPAY_MerchantNo');
        $param['amount']  = $amount =  $order->get_total()*100;
        $param['buyerId']  = $buyerId = '1';
        $param['expirationTime']  = $expirationTime = '100000';
        $param['sourceType']  = $sourceType = 'B';

        $param['LipaPay_key'] = $LipaPay_key = $this->get_option('LIPAPAY_KEY');

        $env = $this->get_option('LIPAPAY_URL');

        if($env=='testing'){
            $param['url'] =$url = 'http://sandbox.lipapay.com/api/excashier.html';
        }else{
            $param['url'] =$url =  'http://www.lipapay.com/api/excashier.html';
        }


        $sign = LipaPay_sign($param,$LipaPay_key);

        echo "<form action=$url method='post' class='form-horizontal' id='LipaPay_form'>
    <div class='box-body'>

        <div class='fields-group'>
            <input name='version' id='version' type='hidden' value='1.3' >
            <input name='merchantId' id='merchantId'  type='hidden' value='$merchantId' >
            <input name='signType' id='signType' type='hidden' value='$signType' >
            <input name='sign' id='sign' type='hidden' value='$sign' >
            <input name='notifyUrl' id='notifyUrl' type='hidden' value='$notifyUrl' >
            <input name='returnUrl' id='returnUrl' type='hidden' value='$returnUrl' >
            <input name='merchantOrderNo' id='merchantOrderNo' type='hidden' value='$merchantOrderNo' >
            <input name='buyerId' id='buyerId' type='hidden' value='$buyerId' >
            <input name='amount' id='amount' type='hidden' value='$amount' >
            <input name='goodsName' id='goodsName' type='hidden' value='$goodsName' >
            <input name='goodsType' id='goodsType' type='hidden' value='$goodsType' >
            <input name='expirationTime' id='expirationTime' type='hidden' value='$expirationTime' >
            <input name='sourceType' id='sourceType' type='hidden' value='$sourceType' >
            <input name='currency' id='currency' type='hidden' value='$currency' ><div class='box-footer'>
            </div>
            <input type='submit' value='submit'>


        </div>
    </div>
</form>";


	}



    public function notify(){
        $LipaPay_key =$this->get_option('LIPAPAY_KEY');

        $data = $request = $_POST;


        $LipaPay_sign = $data['sign'];
        unset($data['sign']);
        $my_sign = LipaPay_sign($data,$LipaPay_key);
        if($my_sign==$LipaPay_sign){
            if($data['status']=='SUCCESS'){
                $order = new WC_Order($data['trade_order_id']);
                try{
                    if(!$order){
                        throw new Exception('Unknow Order (id:'.$data['trade_order_id'].')');
                    }

                    if($order->needs_payment()){
                        $order->payment_complete(isset($data['trade_order_id'])?$data['trade_order_id']:'');
                    }
                }catch(Exception $e){
                    //looger
                    $logger = new WC_Logger();
                    $logger->add( 'LipaPay_payment', $e->getMessage() );

                    $params = array(
                        'action'=>'fail',
                        'appid'=>$this->get_option('appid'),
                        'errcode'=>$e->getCode(),
                        'errmsg'=>$e->getMessage()
                    );

                    $params['hash']= $LipaPay_sign;
                    ob_clean();
                    print json_encode($params);
                    exit;
                }

                //处理返回给LipaPay的参数
                $return = [];
                $return['status'] = 'SUCCESS';
                $return['errorCode'] = '100';
                $return['merchantId'] = $request['merchantId'];
                $return['signType'] = 'MD5';
                $return['merchantOrderNo'] = $request['merchantOrderNo'];
                $return['orderId'] = $request['orderId'];

                $my_sign = LipaPay_sign($return,$LipaPay_key);
                $return['sign'] =$my_sign;

                echo json_encode($return);
            }
        }
    }

}

?>
