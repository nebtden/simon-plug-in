<?php
/*
 * Plugin Name: Lipapay
 * Plugin URI: https://www.lipapay.com
 * Description:
 * Version: 1.8.3
 * Author:  simon.zhang
 * Author URI:https://www.lipapay.com
 * Text Domain: WeiXin Payments for WooCommerce
 */
if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly

if (! defined ( 'WC_LIPAPAY' )) {
    define ( 'WC_LIPAPAY', 'WC_LIPAPAY' );
} else {
    return;
}
define('WC_LIPAPAY_VERSION','0.1.0');
define('WC_LIPAPAY_ID','lipapaywcpaymentgateway' /*'xh-wechat'*/);
define('WC_LIPAPAY_DIR',rtrim(plugin_dir_path(__FILE__),'/'));
define('WC_LIPAPAY_URL',rtrim(plugin_dir_url(__FILE__),'/'));
load_plugin_textdomain( 'lipapay', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/'  );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'lipapay_payment_gateway_plugin_edit_link' );
add_action( 'init', 'lipapay_wc_payment_gateway_init' );

register_activation_hook ( __FILE__, function(){
    global $wpdb;
    $wpdb->query(
       "update {$wpdb->prefix}postmeta
        set meta_value='lipapay'
        where meta_key='_payment_method'
        and meta_value='lipapaywcpaymentgateway';");
});

if(!function_exists('lipapay_wc_payment_gateway_init')){
    function lipapay_wc_payment_gateway_init() {
        if( !class_exists('WC_Payment_Gateway') )  return;
        require_once WC_LIPAPAY_DIR .'/class-lipapay-wc-payment-gateway.php';
        $api = new LIPAPAYWCPaymentGateway();
        
        $api->check_lipapay_response();
        
        add_filter('woocommerce_payment_gateways',array($api,'woocommerce_lipapay_add_gateway' ),10,1);
        add_action( 'wp_ajax_XH_WECHAT_PAYMENT_GET_ORDER', array($api, "get_order_status" ) );
        add_action( 'wp_ajax_nopriv_LIPAPAY_PAYMENT_GET_ORDER', array($api, "get_order_status") );
        add_action( 'woocommerce_receipt_'.$api->id, array($api, 'receipt_page'));
        add_action( 'woocommerce_update_options_payment_gateways_' . $api->id, array ($api,'process_admin_options') ); // WC >= 2.0
        add_action( 'woocommerce_update_options_payment_gateways', array ($api,'process_admin_options') );
        add_action( 'wp_enqueue_scripts', array ($api,'wp_enqueue_scripts') );
    }
}

function lipapay_payment_gateway_plugin_edit_link( $links ){
    return array_merge(
        array(
            'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section='.WC_LIPAPAY_ID) . '">'.__( 'Settings', 'wechatpay' ).'</a>'
        ),
        $links
    );
}
//simon zhang  meixihu chuangxin zhongxin  hunan changsha CN19 414401 CN
?>