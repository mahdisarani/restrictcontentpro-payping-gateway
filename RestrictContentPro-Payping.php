<?php
/*
Plugin Name: درگاه پرداخت پی‌پینگ برای Restrict Content Pro
Version: 1.0.0
Requires at least: 4.0
Description: درگاه پرداخت <a href="http://www.payping.ir/" target="_blank"> پی‌پینگ </a> برای افزونه Restrict Content Pro [باتشکر از حنان ستوده نویسنده سورس اولیه افزونه]
Plugin URI: http://payping.com
Author: Mahdi Sarani
Author URI: https://mahdisarani.ir
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once( 'includes/MAHDISRN_Session.php' );
if ( ! class_exists( 'RCP_PayPing' ) ) {
    class RCP_PayPing {

        #webhooks
        public function process_webhooks() {}
        /**
         * Use this space to enqueue any extra JavaScript files.
         *
        access public
        @return void
         */
        #script
        public function scripts() {}
        /**
         * Load any extra fields on the registration form
         *
         * @access public
         * @return string
         */
        #fields
        public function fields() {
            /* Example for loading the credit card fields :
            ob_start();
            rcp_get_template_part( 'card-form' );
            return ob_get_clean();
            */
        }

        #validateFields
        public function validate_fields() {
            /* Example :
            if ( empty( $_POST['rcp_card_cvc'] ) ) {
                rcp_errors()->add( 'missing_card_code', __( 'The security code you have entered is invalid', 'rcp' ), 'register' );
            }
            */
        }

        #supports

        // public $supports = array();
        public function supports( $item = '' ) {
            return ;
        }
        /**
         * Generate a transaction ID
         *
         * Used in the manual payments gateway.
         *
         * @return string
         */
        public function __construct() {
            add_action( 'init', array( $this, 'PayPing_Verify_By_MAHDISRN' ) );
            add_action( 'rcp_payments_settings', array( $this, 'PayPing_Setting_By_MAHDISRN' ) );
            add_action( 'rcp_gateway_PayPing', array( $this, 'PayPing_Request_By_MAHDISRN' ) );
            add_filter( 'rcp_payment_gateways', array( $this, 'PayPing_Register_By_MAHDISRN' ) );
            add_filter( 'rcp_currencies', array( $this, 'RCP_IRAN_Currencies_By_MAHDISRN' ) );
            add_filter( 'rcp_irr_currency_filter_before', array( $this, 'RCP_IRR_Before_By_MAHDISRN' ), 10, 3 );
            add_filter( 'rcp_irr_currency_filter_after', array( $this, 'RCP_IRR_After_By_MAHDISRN' ), 10, 3 );
            add_filter( 'rcp_irt_currency_filter_before', array( $this, 'RCP_IRT_Before_By_MAHDISRN' ), 10, 3 );
            add_filter( 'rcp_irt_currency_filter_after', array( $this, 'RCP_IRT_After_By_MAHDISRN' ), 10, 3 );
        }

        public function RCP_IRR_Before_By_MAHDISRN( $formatted_price, $currency_code, $price ) {
            return __( 'ریال', 'rcp' ) . ' ' . $price;
        }

        public function RCP_IRR_After_By_MAHDISRN( $formatted_price, $currency_code, $price ) {
            return $price . ' ' . __( 'ریال', 'rcp' );
        }

        public function RCP_IRT_Before_By_MAHDISRN( $formatted_price, $currency_code, $price ) {
            return __( 'تومان', 'rcp' ) . ' ' . $price;
        }

        public function RCP_IRT_After_By_MAHDISRN( $formatted_price, $currency_code, $price ) {
            return $price . ' ' . __( 'تومان', 'rcp' );
        }

        public function RCP_IRAN_Currencies_By_MAHDISRN( $currencies ) {
            unset( $currencies['RIAL'], $currencies['IRR'], $currencies['IRT'] );
            $iran_currencies = array(
                'IRT' => __( 'تومان ایران (تومان)', 'rcp' ),
                'IRR' => __( 'ریال ایران (ریال)', 'rcp' )
            );

            return array_unique( array_merge( $iran_currencies, $currencies ) );
        }

        public function PayPing_Register_By_MAHDISRN( $gateways ) {
            global $rcp_options;

            if ( version_compare( RCP_PLUGIN_VERSION, '2.1.0', '<' ) ) {
                $gateways['PayPing'] = isset( $rcp_options['payping_name'] ) ? $rcp_options['payping_name'] : __( 'پی‌پینگ', 'rcp_payping' );
            } else {
                $gateways['PayPing'] = array(
                    'label'       => isset( $rcp_options['payping_name'] ) ? $rcp_options['payping_name'] : __( 'پی‌پینگ', 'rcp_payping' ),
                    'admin_label' => isset( $rcp_options['payping_name'] ) ? $rcp_options['payping_name'] : __( 'پی‌پینگ', 'rcp_payping' ),
                    'class'       => 'rcp_payping',
                );
            }

            return $gateways;
        }

        public function PayPing_Setting_By_MAHDISRN( $rcp_options ) {
            ?>
            <hr/>
            <table class="form-table">
                <?php do_action( 'RCP_PayPing_before_settings', $rcp_options ); ?>
                <tr valign="top">
                    <th colspan=2><h3><?php _e( 'تنظیمات پی‌پینگ', 'rcp_payping' ); ?></h3></th>
                </tr>
                <tr valign="top">
                    <th>
                        <label for="rcp_settings[payping_token]"><?php _e( 'توکن پی‌پینگ', 'rcp_payping' ); ?></label>
                    </th>
                    <td>
                        <input class="regular-text" id="rcp_settings[payping_token]" style="width: 300px;"
                               name="rcp_settings[payping_token]"
                               value="<?php if ( isset( $rcp_options['payping_token'] ) ) {
                                   echo $rcp_options['payping_token'];
                               } ?>"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th>
                        <label for="rcp_settings[payping_query_name]"><?php _e( 'نام لاتین درگاه', 'rcp_payping' ); ?></label>
                    </th>
                    <td>
                        <input class="regular-text" id="rcp_settings[payping_query_name]" style="width: 300px;"
                               name="rcp_settings[payping_query_name]"
                               value="<?php echo isset( $rcp_options['payping_query_name'] ) ? $rcp_options['payping_query_name'] : 'PayPing'; ?>"/>
                        <div class="description"><?php _e( 'این نام در هنگام بازگشت از بانک در آدرس بازگشت از بانک نمایان خواهد شد . از به کاربردن حروف زائد و فاصله جدا خودداری نمایید . این نام باید با نام سایر درگاه ها متفاوت باشد .', 'rcp_payping' ); ?></div>
                    </td>
                </tr>
                <tr valign="top">
                    <th>
                        <label for="rcp_settings[payping_name]"><?php _e( 'نام نمایشی درگاه', 'rcp_payping' ); ?></label>
                    </th>
                    <td>
                        <input class="regular-text" id="rcp_settings[payping_name]" style="width: 300px;"
                               name="rcp_settings[payping_name]"
                               value="<?php echo isset( $rcp_options['payping_name'] ) ? $rcp_options['payping_name'] : __( 'پی‌پینگ', 'rcp_payping' ); ?>"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th>
                        <label><?php _e( 'تذکر ', 'rcp_payping' ); ?></label>
                    </th>
                    <td>
                        <div class="description"><?php _e( 'از سربرگ مربوط به ثبت نام در تنظیمات افزونه حتما یک برگه برای بازگشت از بانک انتخاب نمایید . ترجیحا نامک برگه را لاتین قرار دهید .<br/> نیازی به قرار دادن شورت کد خاصی در برگه نیست و میتواند برگه ی خالی باشد .', 'rcp_payping' ); ?></div>
                    </td>
                </tr>
                <?php do_action( 'RCP_PayPing_after_settings', $rcp_options ); ?>
            </table>
            <?php
        }

        public function PayPing_Request_By_MAHDISRN( $subscription_data ){

            $new_subscription_id = get_user_meta( $subscription_data['user_id'], 'rcp_subscription_level', true );
            if ( ! empty( $new_subscription_id ) ) {
                update_user_meta( $subscription_data['user_id'], 'rcp_subscription_level_new', $new_subscription_id );
            }

            $old_subscription_id = get_user_meta( $subscription_data['user_id'], 'rcp_subscription_level_old', true );
            update_user_meta( $subscription_data['user_id'], 'rcp_subscription_level', $old_subscription_id );

            global $rcp_options;
            ob_start();
            $query  = isset( $rcp_options['payping_query_name'] ) ? $rcp_options['payping_query_name'] : 'PayPing';
            $amount = str_replace( ',', '', $subscription_data['price'] );
            //fee is just for paypal recurring or ipn gateway ....

            $payping_payment_data = array(
                'user_id'           => $subscription_data['user_id'],
                'subscription_name' => $subscription_data['subscription_name'],
                'subscription_key'  => $subscription_data['key'],
                'amount'            => $amount
            );

            $MAHDISRN_session = MAHDI_Session::get_instance();
            @session_start();
            $MAHDISRN_session['payping_payment_data'] = $payping_payment_data;
            $_SESSION["payping_payment_data"]          = $payping_payment_data;

            //Action For PayPing or RCP Developers...
            do_action( 'RCP_Before_Sending_to_PayPing', $subscription_data );

            if ( ! in_array( $rcp_options['currency'], array(
                'irt',
                'IRT',
                'تومان',
                __( 'تومان', 'rcp' ),
                __( 'تومان', 'rcp_payping' )
            ) ) ) {
                $amount = $amount / 10;
            }

            //Start of PayPing
            $MerchantID  = isset( $rcp_options['payping_token'] ) ? $rcp_options['payping_token'] : '';
            $Amount      = intval( $amount );
            $Email       = isset( $subscription_data['user_email'] ) ? $subscription_data['user_email'] : '-';
            $CallbackUrl = add_query_arg( 'gateway', $query, $subscription_data['return_url'] );
            $Description = sprintf( __( 'خرید اشتراک %s برای کاربر %s', 'rcp_payping' ), $subscription_data['subscription_name'], $subscription_data['user_name'] );
            $Mobile      = '-';
            $Order_Number = $subscription_data['post_data']['rcp_register_nonce'];

            //Filter For PayPing or RCP Developers...
            $Description = apply_filters( 'RCP_PayPing_Description', $Description, $subscription_data );
            $Mobile      = apply_filters( 'RCP_Mobile', $Mobile, $subscription_data );

/* Create Pay */
$pay_data = array(
    'payerName'=> $Mobile,
    'Amount' => $Amount,
    'payerIdentity'=> $Email ,
    'returnUrl' => $CallbackUrl,
    'Description' => $Description ,
    'clientRefId' => $Order_Number
);
$pay_args = array(
    'body' => json_encode( $pay_data ),
    'timeout' => '45',
    'redirection' => '5',
    'httpsversion' => '1.0',
    'blocking' => true,  
    'headers' => array(   
        'Authorization' => 'Bearer ' . $MerchantID,  
        'Content-Type'  => 'application/json',    
        'Accept' => 'application/json'  ),
    'cookies' => array()
);

$pay_url = 'https://api.payping.ir/v2/pay';
$pay_response = wp_remote_post( $pay_url, $pay_args );
            
$PAY_XPP_ID = $pay_response["headers"]["x-paypingrequest-id"];
if( is_wp_error( $pay_response ) ){
    $Status = 'failed';
    $fault = $pay_response->get_error_message();
    $Message = 'خطا در ارتباط به پی‌پینگ : شرح خطا '.$pay_response->get_error_message();
}else{
    $code = wp_remote_retrieve_response_code( $pay_response );
    if( $code === 200 ){
        ob_end_clean();
        if ( isset( $pay_response["body"] ) and $pay_response["body"] != '' ) {
            $code_pay = wp_remote_retrieve_body( $pay_response );
            $code_pay =  json_decode( $code_pay, true );
            wp_redirect( sprintf( 'https://api.payping.ir/v1/pay/gotoipg/%s', $code_pay["code"] ) );
            exit;
        }else{
            $Message = ' تراکنش ناموفق بود- کد خطا : '.$PAY_XPP_ID;
            $fault = $Message;
        }
    }elseif( $code == 400){
        $Message = wp_remote_retrieve_body( $pay_response ).'<br /> کد خطا: '.$PAY_XPP_ID;
    }else{
        $Message = wp_remote_retrieve_body( $pay_response ).'<br /> کد خطا: '.$PAY_XPP_ID;
    }
}
            //End of PayPing

            exit;
        }

        public function PayPing_Verify_By_MAHDISRN(){

            if ( ! isset( $_GET['gateway'] ) ) {
                return;
            }

            if ( ! class_exists( 'RCP_Payments' ) ) {
                return;
            }

            global $rcp_options, $wpdb, $rcp_payments_db_name;
            @session_start();
            $MAHDISRN_session = MAHDI_Session::get_instance();
            if ( isset( $MAHDISRN_session['payping_payment_data'] ) ) {
                $payping_payment_data = $MAHDISRN_session['payping_payment_data'];
            } else {
                $payping_payment_data = isset( $_SESSION["payping_payment_data"] ) ? $_SESSION["payping_payment_data"] : '';
            }

            $query = isset( $rcp_options['payping_query_name'] ) ? $rcp_options['payping_query_name'] : 'PayPing';
            
            if( strpos( $_GET['gateway'], $query ) !== false && $payping_payment_data ){

                $user_id           = $payping_payment_data['user_id'];
                $user_id           = intval( $user_id );
                $subscription_name = $payping_payment_data['subscription_name'];
                $subscription_key  = $payping_payment_data['subscription_key'];
                $amount            = $payping_payment_data['amount'];

                $payment_method = isset( $rcp_options['payping_name'] ) ? $rcp_options['payping_name'] : __( 'پی‌پینگ', 'rcp_payping' );
                
                $new_payment = 1;
                if( $wpdb->get_results( $wpdb->prepare( "SELECT id FROM " . $rcp_payments_db_name . " WHERE `subscription_key`='%s' AND `payment_type`='%s';", $subscription_key, $payment_method ) ) ){
                    $new_payment = 0;
                }

                unset( $GLOBALS['payping_new'] );
                $GLOBALS['payping_new'] = $new_payment;
                global $new;
                $new = $new_payment;

                if ( $new_payment == 1 ) {

                    //Start of PayPing
                    $TokenCode = isset( $rcp_options['payping_token'] ) ? $rcp_options['payping_token'] : '';
                    $Amount     = intval( $amount );
                    if ( ! in_array( $rcp_options['currency'], array(
                        'irt',
                        'IRT',
                        'تومان',
                        __( 'تومان', 'rcp' ),
                        __( 'تومان', 'rcp_payping' )
                    ) ) ) {
                        $Amount = $Amount / 10;
                    }
                    
                    if( isset( $_POST['refid'] ) && $_POST['refid'] != '' ){
                        $refId = $_POST['refid'];    
                    }else{
                        $refId = null;
                    }
                    
                    if( isset( $_POST['clientrefid'] ) && $_POST['clientrefid'] !== '' ){
                        $ClientrefId = $_POST['clientrefid'];    
                    }else{
                        $ClientrefId = null;
                    }
/* Verify Pay */
$__param = $refId;
RCP_check_verifications( __CLASS__, $__param );
                    
$veridy_data = array( 'refId' => $refId,'amount' => $Amount );
$varify_args = array(
    'body' => json_encode( $veridy_data ),
    'timeout' => '45',
    'redirection' => '5',
    'httpsversion' => '1.0',
    'blocking' => true,
    'headers' => array(
        'Authorization' => 'Bearer ' . $TokenCode,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ),
    'cookies' => array()
);
                    
$verify_url = 'https://api.payping.ir/v2/pay/verify';
$verify_response = wp_remote_post( $verify_url, $varify_args );
                        
$VERIFY_XPP_ID = $verify_response["headers"]["x-paypingrequest-id"];
if( is_wp_error( $verify_response ) ){
    $payment_status = 'failed';
    $fault = $verify_response->get_error_message();
    $Message = 'خطا در ارتباط به پی‌پینگ : شرح خطا '.$verify_response->get_error_message();
}else{
    $code = wp_remote_retrieve_response_code( $verify_response );
    if( $code === 200 ){
        if( isset( $_POST["refid"]) and $_POST["refid"] != '' ){
            $payment_status = 'completed';
            $transaction_id = $_POST["refid"];
            $fault = '';
            $Message = '';
        }else{
            $payment_status = 'failed';
            $transaction_id = $_POST['refid'];
            $Message = 'متافسانه سامانه قادر به دریافت کد پیگیری نمی باشد! نتیجه درخواست : ' .wp_remote_retrieve_body( $response ).'<br /> شماره خطا: '.$VERIFY_XPP_ID;
            $fault = $code;
        }
    }elseif( $code == 400) {
        $payment_status = 'failed';
        $transaction_id = $_POST['refid'];
        $Message = wp_remote_retrieve_body( $verify_response ).'<br /> شماره خطا: '.$VERIFY_XPP_ID;
        $fault = $code;
    }else{
        $payment_status = 'failed';
        $transaction_id = $_POST['refid'];
        $Message = wp_remote_retrieve_body( $verify_response ).'<br /> شماره خطا: '.$VERIFY_XPP_ID;
        $fault = $code;
    }
}
                    //End of PayPing

                    unset( $GLOBALS['payping_payment_status'] );
                    unset( $GLOBALS['payping_transaction_id'] );
                    unset( $GLOBALS['payping_fault'] );
                    unset( $GLOBALS['payping_subscription_key'] );
                    
                    $GLOBALS['payping_payment_status']   = $payment_status;
                    $GLOBALS['payping_transaction_id']   = $transaction_id;
                    $GLOBALS['payping_subscription_key'] = $subscription_key;
                    $GLOBALS['payping_fault']            = $fault;
                    
                    global $payping_transaction;
                    $payping_transaction                             = array();
                    $payping_transaction['payping_payment_status']   = $payment_status;
                    $payping_transaction['payping_transaction_id']   = $transaction_id;
                    $payping_transaction['payping_subscription_key'] = $subscription_key;
                    $payping_transaction['payping_fault']            = $fault;

                    if( $payment_status == 'completed' ){

                        $payment_data = array(
                            'date'             => date( 'Y-m-d g:i:s' ),
                            'subscription'     => $subscription_name,
                            'payment_type'     => $payment_method,
                            'subscription_key' => $subscription_key,
                            'amount'           => $amount,
                            'user_id'          => $user_id,
                            'transaction_id'   => $transaction_id
                        );

                        //Action For PayPing or RCP Developers...
                        do_action( 'RCP_PayPing_Insert_Payment', $payment_data, $user_id );

                        $rcp_payments = new RCP_Payments();
                        RCP_set_verifications($rcp_payments->insert( $payment_data ), __CLASS__, $__param);

                        $membership = rcp_get_membership( $user_id );
                        $old_status_level = $membership->get_status();
                        $new_subscription_id = get_user_meta( $user_id, 'rcp_subscription_level_new', true );
                        $new_subscription_id = rcp_get_membership_meta( $user_id , "rcp_subscription_level_new", true );
                        if ( ! empty( $new_subscription_id ) ) {
                            update_user_meta( $user_id, 'rcp_subscription_level', $new_subscription_id );
                            rcp_update_membership_meta( $user_id , "rcp_subscription_level_new", 'active' );
                        }
                        
                        rcp_set_status( $user_id, 'active' );

                        if ( version_compare( RCP_PLUGIN_VERSION, '2.1.0', '<' ) ) {
                            rcp_email_subscription_status( $user_id, 'active' );
                            if ( ! isset( $rcp_options['disable_new_user_notices'] ) ) {
                                wp_new_user_notification( $user_id );
                            }
                        }

                        $subscription          = rcp_get_subscription_details( rcp_get_subscription_id( $user_id ) );
                        $member_new_expiration = date( 'Y-m-d H:i:s', strtotime( '+' . $subscription->duration . ' ' . $subscription->duration_unit . ' 23:59:59' ) );
                        rcp_set_expiration_date( $user_id, $member_new_expiration );
                        delete_user_meta( $user_id, '_rcp_expired_email_sent' );
                        
                        $log_data = array(
                            'post_title'   => __( 'تایید پرداخت', 'rcp_payping' ),
                            'post_content' => __( 'پرداخت با موفقیت انجام شد . کد تراکنش : ', 'rcp_payping' ) . $transaction_id . __( ' .  روش پرداخت : ', 'rcp_payping' ) . $payment_method,
                            'post_parent'  => 0,
                            'log_type'     => 'gateway_error'
                        );

                        $log_meta = array(
                            'user_subscription' => $subscription_name,
                            'user_id'           => $user_id
                        );

                        $log_entry = WP_Logging::insert_log( $log_data, $log_meta );

                        //Action For PayPing or RCP Developers...
                        do_action( 'RCP_PayPing_Completed', $user_id );
                    }

                    if ( $payment_status == 'cancelled' ) {

                        $log_data = array(
                            'post_title'   => __( 'انصراف از پرداخت', 'rcp_payping' ),
                            'post_content' => __( 'تراکنش به دلیل انصراف کاربر از پرداخت ، ناتمام باقی ماند .', 'rcp_payping' ) . __( ' روش پرداخت : ', 'rcp_payping' ) . $payment_method,
                            'post_parent'  => 0,
                            'log_type'     => 'gateway_error'
                        );

                        $log_meta = array(
                            'user_subscription' => $subscription_name,
                            'user_id'           => $user_id
                        );

                        $log_entry = WP_Logging::insert_log( $log_data, $log_meta );

                        //Action For PayPing or RCP Developers...
                        do_action( 'RCP_PayPing_Cancelled', $user_id );

                    }

                    if ( $payment_status == 'failed' ) {

                        $log_data = array(
                            'post_title'   => __( 'خطا در پرداخت', 'rcp_payping' ),
                            'post_content' => __( 'تراکنش به دلیل خطای رو به رو ناموفق باقی باند :', 'rcp_payping' ) . $this->Fault( $fault ) . __( ' روش پرداخت : ', 'rcp_payping' ) . $payment_method,
                            'post_parent'  => 0,
                            'log_type'     => 'gateway_error'
                        );

                        $log_meta = array(
                            'user_subscription' => $subscription_name,
                            'user_id'           => $user_id
                        );

                        $log_entry = WP_Logging::insert_log( $log_data, $log_meta );

                        //Action For PayPing or RCP Developers...
                        do_action( 'RCP_PayPing_Failed', $user_id );

                    }

                }
                add_filter( 'the_content', array( $this, 'PayPing_Content_After_Return_By_MAHDISRN' ) );
                //session_destroy();
            }
        }


        public function PayPing_Content_After_Return_By_MAHDISRN( $content ) {
            
            global $payping_transaction, $new;

            $MAHDISRN_session = MAHDI_Session::get_instance();
            @session_start();

            $new_payment = isset( $GLOBALS['payping_new'] ) ? $GLOBALS['payping_new'] : $new;

            $payment_status = isset( $GLOBALS['payping_payment_status'] ) ? $GLOBALS['payping_payment_status'] : $payping_transaction['payping_payment_status'];
            $transaction_id = isset( $GLOBALS['payping_transaction_id'] ) ? $GLOBALS['payping_transaction_id'] : $payping_transaction['payping_transaction_id'];
            $fault          = isset( $GLOBALS['payping_fault'] ) ? $this->Fault( $GLOBALS['payping_fault'] ) : $this->Fault( $payping_transaction['payping_fault'] );
            
            if ( $new_payment == 1 ) {

                $payping_data = array(
                    'payment_status' => $payment_status,
                    'transaction_id' => $transaction_id,
                    'fault'          => $fault
                );

                $MAHDISRN_session['payping_data'] = $payping_data;
                $_SESSION["payping_data"]          = $payping_data;

            } else {
                if ( isset( $MAHDISRN_session['payping_data'] ) ) {
                    $payping_payment_data = $MAHDISRN_session['payping_data'];
                } else {
                    $payping_payment_data = isset( $_SESSION["payping_data"] ) ? $_SESSION["payping_data"] : '';
                }

                $payment_status = isset( $payping_payment_data['payment_status'] ) ? $payping_payment_data['payment_status'] : '';
                $transaction_id = isset( $payping_payment_data['transaction_id'] ) ? $payping_payment_data['transaction_id'] : '';
                $fault          = isset( $payping_payment_data['fault'] ) ? $this->Fault( $payping_payment_data['fault'] ) : '';
            }

            $message = '';

            if ( $payment_status == 'completed' ) {
                $message = '<br/>' . __( 'پرداخت با موفقیت انجام شد . کد تراکنش : ', 'rcp_payping' ) . $transaction_id . '<br/>';
            }

            if ( $payment_status == 'cancelled' ) {
                $message = '<br/>' . __( 'تراکنش به دلیل انصراف شما نا تمام باقی ماند .', 'rcp_payping' );
            }

            if ( $payment_status == 'failed' ) {
                $message = '<br/>' . __( 'تراکنش به دلیل خطای زیر ناموفق باقی باند :', 'rcp_payping' ) . '<br/>' . $fault . '<br/>';
            }

            return $content . $message;
        }

        private function Fault( $error ){
        switch ($error){
        case 200 :
        return 'عملیات با موفقیت انجام شد';
        break ;
        case 400 :
        return 'مشکلی در ارسال درخواست وجود دارد';
        break ;
        case 500 :
        return 'مشکلی در سرور رخ داده است';
        break;
        case 503 :
        return 'سرور در حال حاضر قادر به پاسخگویی نمی‌باشد';
        break;
        case 401 :
        return 'عدم دسترسی';
        break;
        case 403 :
        return 'دسترسی غیر مجاز';
        break;
        case 404 :
        return 'آیتم درخواستی مورد نظر موجود نمی‌باشد';
        break;
        }
        }

    }
}
new RCP_PayPing();
if ( ! function_exists( 'change_cancelled_to_pending_By_MAHDISRN' ) ) {
    add_action( 'rcp_set_status', 'change_cancelled_to_pending_By_MAHDISRN', 10, 2 );
    function change_cancelled_to_pending_By_MAHDISRN( $user_id, $payment_status ) {
        if( 'cancelled' == $payment_status ){
            rcp_set_status( $user_id, 'expired' );
        }
        return true;
    }
}

if ( ! function_exists( 'RCP_User_Registration_Data_By_MAHDISRN' ) && ! function_exists( 'RCP_User_Registration_Data' ) ) {
    add_filter( 'rcp_user_registration_data', 'RCP_User_Registration_Data_By_MAHDISRN' );
    function RCP_User_Registration_Data_By_MAHDISRN( $user ) {
        $old_subscription_id = get_user_meta( $user['id'], 'rcp_subscription_level_new', true );
        if ( ! empty( $old_subscription_id ) ) {
            update_user_meta( $user['id'], 'rcp_subscription_level_old', $old_subscription_id );
        }

        $user_info     = get_userdata( $user['id'] );
        $old_user_role = implode( ', ', $user_info->roles );
        if ( ! empty( $old_user_role ) ) {
            update_user_meta( $user['id'], 'rcp_user_role_old', $old_user_role );
        }

        return $user;
    }
}

if ( ! function_exists( 'RCP_check_verifications' ) ) {
    function RCP_check_verifications( $gateway, $params ) {

        if ( ! function_exists( 'rcp_get_payment_meta_db_name' ) ) {
            return;
        }

        if ( is_array( $params ) || is_object( $params ) ) {
            $params = implode( '_', (array) $params );
        }
        if ( empty( $params ) || trim( $params ) == '' ) {
            return;
        }

        $gateway = str_ireplace( array( 'RCP_', 'bank' ), array( '', '' ), $gateway );
        $params  = trim( strtolower( $gateway ) . '_' . $params );

        $table = rcp_get_payment_meta_db_name();

        global $wpdb;
        $check = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE meta_key='_verification_params' AND meta_value='%s'", $params ) );

        if ( ! empty( $check ) ) {
            wp_die( 'وضعیت این تراکنش قبلا مشخص شده بود.' );
        }
    }
}

if ( ! function_exists( 'RCP_set_verifications' ) ) {
    function RCP_set_verifications( $payment_id, $gateway, $params ) {

        if ( ! function_exists( 'rcp_get_payment_meta_db_name' ) ) {
            return;
        }

        if ( is_array( $params ) || is_object( $params ) ) {
            $params = implode( '_', (array) $params );
        }
        if ( empty( $params ) || trim( $params ) == '' ) {
            return;
        }

        $gateway = str_ireplace( array( 'RCP_', 'bank' ), array( '', '' ), $gateway );
        $params  = trim( strtolower( $gateway ) . '_' . $params );

        $table = rcp_get_payment_meta_db_name();

        global $wpdb;
        $wpdb->insert( $table, array(
            'rcp_payment_id' => $payment_id,
            'meta_key'   => '_verification_params',
            'meta_value' => $params
        ), array( '%d', '%s', '%s' ) );
    }
}