<?php
if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly

require_once 'lib/lipapay.php';
require_once 'lib/lipapay.sign.php';

class LIPAPAYWCPaymentGateway extends WC_Payment_Gateway {
    private $config;
    
	public function __construct() {

		array_push($this->supports,'refunds');

		$this->id = WC_LIPAPAY_ID;
		$this->icon =WC_LIPAPAY_URL. '/images/logo.png';
		$this->has_fields = false;
		
		$this->method_title = 'Lipapay'; // checkout option title
	    $this->method_description=' ..................for simon test....... ';
	   
		$this->init_form_fields ();
		$this->init_settings ();
		
		$this->title = $this->get_option ( 'title' );
		$this->description = $this->get_option ( 'description' );
		
		$lib = WC_LIPAPAY_DIR.'/lib';
		

		include_once ($lib . '/lipapay.php');
// 		include_once ($lib . '/lipapay.form.php');
		include_once ($lib . '/lipapay.sign.php');
		include_once ($lib . '/notify.php');
		include_once ($lib . '/return.php');
//		include_once ($lib . '/log.php');
		$this->config =new LipapayPaymentConfig ($this->get_option('LIPAPAY_URL'),  $this->get_option('wechatpay_mchId'), $this->get_option('wechatpay_key'));
	}
	function init_form_fields() {
	    $this->form_fields = array (
	        'enabled' => array (
	            'title' => __ ( 'Enable/Disable', 'lipapay' ),
	            'type' => 'checkbox',
	            'label' => __ ( 'Enable Lipapay Payment', 'lipapay' ),
	            'default' => 'no'
	        ),
	        'title' => array (
	            'title' => 'Lipapay',
	            'type' => 'text',
	            'description' => __ ( 'This controls the title which the user sees during checkout.', 'Lipapay' ),
	            'default' => 'Lipapay',
	            'css' => 'width:400px'
	        ),
	        'description' => array (
	            'title' => __ ( 'Description', 'Lipapay' ),
	            'type' => 'textarea',
	            'description' =>  'This controls the description which the user sees during checkout.', 'Lipapay' ,
	            'default' => "Pay via lipapay, if you don't have an Lipapay account, you should contact us", 'Lipapay' ,
	            //'desc_tip' => true ,
	            'css' => 'width:400px'
	        ),
	        'LIPAPAY_URL' => array (
	            'title' => __ ( 'Application ID', 'Lipapay' ),
	            'type' => 'text',
	            'description' => __ ( 'Please enter the LIPAPAY URL,Generally not required ', 'Lipapay' ),
	            'css' => 'width:400px'
	        ),
	        'LIPAPAY_MerchantNo' => array (
	            'title' => __ ( 'lipapay Merchant ID', 'Lipapay' ),
	            'type' => 'text',
	            'description' => __ ( 'Please enter the LIPAPAY MerchantNo，required ', 'Lipapay' ),
	            'css' => 'width:400px'
	        ),
	        'LIPAPAY_KEY' => array (
	            'title' => __ ( 'lipapay Key', 'KES' ),
	            'type' => 'text',
	            'description' => __ ( 'Please enter the LIPAPAY KEY，required.', 'Lipapay' ),
	            'css' => 'width:400px',
	            //'desc_tip' => true
	        ),
            'lipapay_monetary_unit' => array (
                'title' => __ ( 'lipapay Key', 'lipapay' ),
                'type' => 'text',
                'description' => __ ( 'Please enter monetary unit，such as kes....', 'Lipapay' ),
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
	
	public  function woocommerce_lipapay_add_gateway( $methods ) {
	    $methods[] = $this;
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
		
		return apply_filters('lipapay_wc_get_order_title', mb_strimwidth ( $title, 0,32, '...','utf-8'));
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
	
	public function check_lipapay_response() {
	    if(defined('WP_USE_THEMES')&&!WP_USE_THEMES){
	        return;
	    }
		$xml = isset($GLOBALS ['HTTP_RAW_POST_DATA'])?$GLOBALS ['HTTP_RAW_POST_DATA']:'';	
		if(empty($xml)){
		    return ;
		}
		
		// 如果返回成功则验证签名
		try {
		    $result = WechatPaymentResults::Init ( $xml );
		    if (!$result||! isset($result['transaction_id'])) {
		        return;
		    }
		    
		    $transaction_id=$result ["transaction_id"];
		    $order_id = $result['attach'];
		    
		    $input = new WechatPaymentOrderQuery ();
		    $input->SetTransaction_id ( $transaction_id );
		    $query_result = WechatPaymentApi::orderQuery ( $input, $this->config );
		    if ($query_result['result_code'] == 'FAIL' || $query_result['return_code'] == 'FAIL') {
                throw new Exception(sprintf("return_msg:%s ;err_code_des:%s "), $query_result['return_msg'], $query_result['err_code_des']);
            }
            
            if(!(isset($query_result['trade_state'])&& $query_result['trade_state']=='SUCCESS')){
                throw new Exception("order not paid!");
            }
		  
		    $order = new WC_Order ( $order_id );
		    if($order->needs_payment()){
		          $order->payment_complete ($transaction_id);
		    }
		    
		    $reply = new WechatPaymentNotifyReply ();
		    $reply->SetReturn_code ( "SUCCESS" );
		    $reply->SetReturn_msg ( "OK" );
		    
		    WxpayApi::replyNotify ( $reply->ToXml () );
		    exit;
		} catch ( WechatPaymentException $e ) {
		    return;
		}
	}

	public function process_refund( $order_id, $amount = null, $reason = ''){		
		$order = new WC_Order ($order_id );
		if(!$order){
			return new WP_Error( 'invalid_order','错误的订单' );
		}
	
		$trade_no =$order->get_transaction_id();
		if (empty ( $trade_no )) {
			return new WP_Error( 'invalid_order', '未找到微信支付交易号或订单未支付' );
		}
	
		$total = $order->get_total ();
		//$amount = $amount;
        $preTotal = $total;
        $preAmount = $amount;
        
		$exchange_rate = floatval($this->get_option('exchange_rate'));
		if($exchange_rate<=0){
			$exchange_rate=1;
		}
			
		$total = round ( $total * $exchange_rate, 2 );
		$amount = round ( $amount * $exchange_rate, 2 );
      
        $total = ( int ) ( $total  * 100);
		$amount = ( int ) ($amount * 100);
        
		if($amount<=0||$amount>$total){
			return new WP_Error( 'invalid_order',__('Invalid refused amount!' ,XH_WECHAT) );
		}
	
		$transaction_id = $trade_no;
		$total_fee = $total;
		$refund_fee = $amount;
	
		$input = new WechatPaymentRefund ();
		$input->SetTransaction_id ( $transaction_id );
		$input->SetTotal_fee ( $total_fee );
		$input->SetRefund_fee ( $refund_fee );
	
		$input->SetOut_refund_no ( $order_id.time());
		$input->SetOp_user_id ( $this->config->getMCHID());
	
		try {
			$result = WechatPaymentApi::refund ( $input,60 ,$this->config);
			if ($result ['result_code'] == 'FAIL' || $result ['return_code'] == 'FAIL') {
				Log::DEBUG ( " XHWechatPaymentApi::orderQuery:" . json_encode ( $result ) );
				throw new Exception ("return_msg:". $result ['return_msg'].';err_code_des:'. $result ['err_code_des'] );
			}
	
		} catch ( Exception $e ) {
			return new WP_Error( 'invalid_order',$e->getMessage ());
		}
	
		return true;
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
        $notifyUrl  = WC()->api_request_url( 'WC_Gateway_PPEC' );

        $order_sn = md5(date ( "YmdHis" ).$order_id);
        $param = [];
        $param['merchantOrderNo'] = $merchantOrderNo = $order_sn;
        $param['goodsName'] = $goodsName = $this->get_order_title($order);
        $param['goodsType'] = $goodsType = '2';
        $param['returnUrl'] = $returnUrl;
        $param['notifyUrl'] = $notifyUrl;
        $param['signType'] = $signType = 'MD5';
        $param['currency'] = $currency = $this->get_option('lipapay_monetary_unit');
        $param['merchantId'] = $merchantId = $this->get_option('LIPAPAY_MerchantNo');
        $param['amount']  = $amount =  $order->get_total()*100;
        $param['buyerId']  = $buyerId = '1';
        $param['expirationTime']  = $expirationTime = '100000';
        $param['sourceType']  = $sourceType = 'B';

        $param['lipapay_key'] = $lipapay_key = $this->get_option('LIPAPAY_KEY');
        $param['url'] =$url =  $this->get_option('LIPAPAY_URL');

        $sign = lipapay_sign($param,$lipapay_key);

        echo "<form action=$url method='post' class='form-horizontal' id='lipapay_form'>
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
        $lipapay_key =$this->get_option('LIPAPAY_KEY');

        $data = $request = $_POST;

// write the log
        file_put_contents("log.txt", Date('Y-m-d H:i:s').'notify:'.json_encode($data)."\n", FILE_APPEND);

        $lipapay_sign = $data['sign'];
        unset($data['sign']);
        $my_sign = lipapay_sign($data,$lipapay_key);
        if($my_sign==$lipapay_sign){
            if($data['status']=='SUCCESS'){
                $order = new WC_Order($data['trade_order_id']);
                try{
                    if(!$order){
                        throw new Exception('Unknow Order (id:'.$data['trade_order_id'].')');
                    }

                    if($order->needs_payment()&&$data['status']=='OD'){
                        $order->payment_complete(isset($data['transacton_id'])?$data['transacton_id']:'');
                    }
                }catch(Exception $e){
                    //looger
                    $logger = new WC_Logger();
                    $logger->add( 'lipapay_payment', $e->getMessage() );

                    $params = array(
                        'action'=>'fail',
                        'appid'=>$this->get_option('appid'),
                        'errcode'=>$e->getCode(),
                        'errmsg'=>$e->getMessage()
                    );

                    $params['hash']= $lipapay_sign;
                    ob_clean();
                    print json_encode($params);
                    exit;
                }

                //处理返回给lipapay的参数
                $return = [];
                $return['status'] = 'SUCCESS';
                $return['errorCode'] = '100';
                $return['merchantId'] = $request['merchantId'];
                $return['signType'] = 'MD5';
                $return['merchantOrderNo'] = $request['merchantOrderNo'];
                $return['orderId'] = $request['orderId'];

                $my_sign = lipapay_sign($return,$lipapay_key);
                $return['sign'] =$my_sign;

                echo json_encode($return);
            }
        }
    }

}

?>
