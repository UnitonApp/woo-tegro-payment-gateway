<?php
/**
 * Settings for Tegro Payment Gateway.
 */

defined( 'ABSPATH' ) || exit;

return array(
	'enabled' => array(
		'title' => __( 'Enable/Disable', 'woocommerce' ),
		'type' => 'checkbox',
		'label' => __( 'Enabled', 'woocommerce' ),
		'default' => 'no',
	),
	'title' => array(
		'title' => __( 'Title', 'woocommerce' ),
		'type' => 'safe_text',
		'default' => __( 'Tegro Payment', 'woocommerce' ),
	),
	'description' => array(
		'title' => __( 'Description', 'woocommerce' ),
		'type' => 'text',
		'default' => __( 'Pay via Tegro Payment.', 'woocommerce' ),
	),
    'api_create_order_url' => array(
        'title' => __( 'API create order URL*', 'woocommerce' ),
        'type' => 'text',
        'description' => __( 'API URL to create order.', 'woocommerce' ),
        'default' => 'https://tegro.money/api/createOrder/',
    ),
    'api_check_order_url' => array(
        'title' => __( 'API check order URL*', 'woocommerce' ),
        'type' => 'text',
        'description' => __( 'API URL to check order.', 'woocommerce' ),
        'default' => 'https://tegro.money/api/order/',
    ),
    'shop_id' => array(
        'title' => __( 'Shop ID.(Public Key)*', 'woocommerce' ),
        'type' => 'text',
        'description' => __( 'Public KEY of your shop in tegro.money. See in your Tegro account.', 'woocommerce' ),
        'default' => '',
    ),
    'api_key' => array(
        'title' => __( 'API KEY*', 'woocommerce' ),
        'type' => 'password',
        'description' => __( 'API KEY, used to sign payment info. See in your Tegro account.', 'woocommerce' ),
        'default' => '',
    ),
	'email' => array(
		'title' => __( 'Email', 'woocommerce' ),
		'type' => 'email',
		'default' => get_option( 'admin_email' ),
		'placeholder' => 'you@youremailk.com',
	),
	'test_mode' => array(
		'title' => __( 'Test mode', 'woocommerce' ),
		'type' => 'checkbox',
		'label' => __( 'Enable Test mode', 'woocommerce' ),
		'default' => 'no',
	),
);
