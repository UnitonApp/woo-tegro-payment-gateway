<?php
/*
Plugin Name: WooCommerce Tegro Payment Gateway
Plugin URI: ---
Description: Extends WooCommerce with a'Tegro money' payment gateway.
Version: 0.1
Author: Kopeikin Dmitrii
Author URI: http://woothemes.com/
Copyright: © 2009-2011 WooThemes.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * @class       WC_tegro_payment_gateway
 * @extends     WC_Payment_Gateway
 * @version     2.3.0
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action('plugins_loaded', 'woocommerce_tegro_payment_gateway');
function woocommerce_tegro_payment_gateway() {
	
	if ( !class_exists( 'WC_Payment_Gateway')) {
		return;
	}
	if ( class_exists('WC_tegro_payment_gateway')) {
		return;
	}

	add_filter('woocommerce_payment_gateways', 'woocommerce_add_tegro_payment_gateway' );
	function woocommerce_add_tegro_payment_gateway($methods) {
		$methods[] = 'WC_tegro_payment_gateway';
		return $methods;
	}

	class WC_tegro_payment_gateway extends WC_Payment_Gateway {

		protected static $instance = null;
		private string $api_create_order_url = '';
		private string $api_check_order_url = '';
		private string $shop_id = '';
		private string $api_key = '';
		private string $email = '';
		private string $test_mode = '';

		public static function get_instance() {
			if( null == self::$instance ) {
				self::$instance = new self;
			}
		
			return self::$instance;
			}
		
		public function __construct() {
			$this->id = 'tegro_payment';
			$this->has_fields = false;

			$this->init_form_fields();
			$this->init_settings();

			$this->title = $this->get_option('title');
			$this->description = $this->get_option('description');
			$this->api_create_order_url = $this->get_option('api_create_order_url');
			$this->api_check_order_url = $this->get_option('api_check_order_url');
			$this->shop_id = $this->get_option('shop_id');
			$this->api_key = $this->get_option('api_key');
			$this->email = $this->get_option('email');
			$this->test_mode = $this->get_option('test_mode') === 'no' ? 1 : 0;

			$this->method_title = 'Tegro payment gateway';
			$this->method_description = 'Payments with Tegro payment system.';

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			add_action('woocommerce_pay_order_before_submit', array($this, 'add_button'));
		}
		
		public function needs_setup() {
			return ! is_email( $this->email )
			|| empty( $this->shop_id )
				|| empty( $this->api_key )
				|| empty( $this->api_create_order_url)
				|| empty( $this->api_check_order_url );
		}

		public function add_button($order_id) {
			if (!is_wc_endpoint_url( 'order-pay' )) {
				return;
			}

			global $wp;
			$order_id = intval(str_replace('checkout/order-pay/', "", $wp->request));
			echo $order_id;
			$order = wc_get_order($order_id);

			echo $order->get_payment_method_title();
			echo $order->get_payment_method();

			if ($order->needs_payment() && $order->get_payment_method() === $this->id) {
				echo '<button style="width: 100%;" class="button alt wp-element-button">Button</button>';
			}
		}

		function init_form_fields() {
			$this->form_fields = include __DIR__ . '/includes/settings.php';
		}

		function process_admin_options() {
			$saved = parent::process_admin_options();
			return $saved;
		}

		function admin_options() {
			?>
			<h2><?php _e($this->method_title,'woocommerce'); ?></h2>
			<?php echo __('Fields marked with * are required.'); ?>
			<table class="form-table">
			<?php $this->generate_settings_html($this->form_fields); ?>
			</table> <?php
		}

		//
		function payment_fields() {
			if ($this->description)
			{
				echo wpautop(wptexturize($this->description));
			}
		}

		// Processing payment 
		function process_payment($order_id) {
			$order = wc_get_order( $order_id );
			WC()->cart->empty_cart();

			$redirect = $order->get_checkout_payment_url();
			
			if ($order->get_status() === 'pending') {
				$response = $this->try_create_payment_order($order);
				
				if ($response && $response['type'] === 'success') {
					$order->update_status('on-hold');
					$redirect = $response['data']['url'];
				} else {
					wc_add_notice(__('There is some Error during payment.', 'woocommerce'), 'error');
					wc_add_notice(__('Try later, or try another payment method.', 'woocommerce'), 'error');
					wc_add_notice(__('Or you can contact us, and we will help you with this problem.', 'woocommerce'), 'error');
					wc_add_notice('<a href="' . 'https://mail.ru' . '" class="button wc-forward">mail</a>', 'notice');
				}
			}
			
			if ($order->get_status() === 'on-hold') {
				$this->check_order_is_payed($order);
				$redirect = $order->get_checkout_payment_url(true);
			}
				
			if ($order->get_status() === 'processing') {
				$redirect = $order->get_checkout_order_received_url(); 
			}

			return array (
				'result' => 'success',
				'redirect'	=> $redirect, 
			);
		}

		function try_create_payment_order($order) {
			$receipt = array();
			foreach($order->get_items() as $item) {
				array_push($receipt, array(
					'name' => $item->get_name(),
					'count' => $item->get_quantity(),
					'price' => $item->get_product()->get_price(),
				));
			}

			$data = array(
				'shop_id' => $this->shop_id,
				'nonce' => $order->get_date_created()->getTimestamp(),
				'currency'=>$order->get_currency() == 'RUR' ? 'RUB' : $order->get_currency(),
				'amount' => round($order->get_total(), 2),
				'order_id' => $order->get_id(),
				'fields' => array(
					'email' => $order->get_billing_email(),
					'phone' => wc_sanitize_phone_number($order->get_billing_phone()),
				),
				'receipt' => $receipt,
			);

			$response = $this->make_request($data, $this->api_create_order_url);
			
			return $response;
		}

		// // Checking answer from payment system
		// function check_payments() {
		// 	error_log('checking_payments!');
		// 	$orders = wc_get_orders( array( 'wc_on-hold' ) );

		// 	foreach ($orders as $order) {
		// 		if ($order->get_payment_method_title() === $this->title) {
		// 			$this->check_order_is_payed($order);
		// 		}
		// 	}
		// }

		function check_order_is_payed($order) {
			$data = array(
				'shop_id' => $this->shop_id,
				'nonce' => $order->get_date_created()->getTimestamp(),
				'payment_id' => $order->get_id(),
			);
			$response = $this->make_request($data, $this->api_check_order_url);
			if ($response['type'] == 'success') {
				$order->update_status('processing');
				$order->set_date_paid($response['data']['date_payed']);
				$order->add_order_note(__('Order successfully payed.', 'tegro'));
			} else {
				$order->add_order_note(__('Order not payed.', 'tegro'));
			}
		}

		function make_request($data, $url) {
			ksort($data);
			$body = json_encode($data);
			$sign = hash_hmac('sha256', $body, $this->api_key);

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL =>$url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS =>$body,
				CURLOPT_HTTPHEADER => array(
					"Authorization: Bearer $sign",
					"Content-Type: application/json"
				),
			));
			
			$response = curl_exec($curl);

			curl_close($curl);

			return $response;
		}

	}

}
