<?php

/**
 * Class Give_QuickBooks_API
 *
 * @since      1.0
 * @package    Give_QuickBooks_Payments
 * @subpackage Give_QuickBooks_Payments/includes
 * @author     WordImpress
 */
class Give_QuickBooks_API {

	/**
	 * GoCardless Base URL
	 *
	 * @var     string $baseurl QuickBooks base url for the payment.
	 * @since    1.0.0
	 * @access   private
	 */
	private static $baseurl = 'https://api.intuit.com/';

	/**
	 * GoCardless Sandbox URL
	 *
	 * @var     string $sandbox_base url QuickBooks sandbox API url.
	 * @since    1.0.0
	 * @access   private
	 */
	private static $sandbox_baseurl = 'https://sandbox.api.intuit.com/';


	/**
	 * Get access token from the Customer card information.
	 *
	 * @param $payment_data
	 *
	 * @return array|mixed|object
	 */
	public static function get_access_token( $payment_data ) {

		$card_expiry = explode("/",$payment_data["post_data"]["card_expiry"]);
		$card_expiry_month = trim($card_expiry[0]);

		$card_array["card"] = array(
			"expYear" => $payment_data["post_data"]["card-expiry-year"],
			"expMonth" => $card_expiry_month,
			"cvc" => $payment_data["post_data"]["card_cvc"],
			"number" => $payment_data["post_data"]["card_number"],
			"name" => $payment_data["post_data"]["card_name"],
		);

		$data = wp_json_encode( $card_array );

		$result = wp_remote_post( 'https://sandbox.api.intuit.com/quickbooks/v4/payments/tokens', array(
			'headers' => array(
				'content-type' => 'application/json',
			),
			'body'    => $data,
		) );

		$response_body    = wp_remote_retrieve_body( $result );
		$response_obj     = json_decode( $response_body );

		return $response_obj;
	}


	/**
	 * QuickBooks Payment process request.
	 *
	 * @since 1.0
	 *
	 * @param $payment_data
	 * @param $access_token
	 */
	public static function quickbooks_payment_request( $payment_data, $access_token ) {

		//$card_expiry = explode("/",$payment_data["post_data"]["card_expiry"]);
		//$card_expiry_month = trim($card_expiry[0]);

		$request_data = array(
			'amount' => "14.00",
			'token' => $access_token,
			'currency' => give_get_currency(),
			'context' => array(
				'mobile'=> false,
                'isEcommerce'=> true,
			),
		);

		$data = wp_json_encode( $request_data );
		$access_token =  give_qb_get_oauth_access_token();

		$authorization = 'Bearer ' . $access_token;

		$result = wp_remote_post( 'https://sandbox.api.intuit.com/quickbooks/v4/payments/charges', array(
			'headers' => array(
				'content-type' => 'application/json',
				'Request-Id' => give_qb_generate_unique_request_id(),
				'Authorization' => $authorization,
			),
			'body'    => $data,
		) );

		$response_body    = wp_remote_retrieve_body( $result );
		$response_obj     = json_decode( $response_body );

		echo "<pre>";
		print_r($response_obj);

		exit;

		//return $response_obj;
	}

}
