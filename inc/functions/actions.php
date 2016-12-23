<?php
if(!defined('ABSPATH')) {exit;}
add_action('plugins_loaded', 'utalii_plugin_init');
function utalii_plugin_init() 
{
	process_ipn_request_func();
}
function process_ipn_request_func()
{
	if (!empty($_GET['paypal_notify_return']) && $_GET['paypal_notify_return'] == '1') 
	{
    	$ipn_response = !empty($_POST) ? $_POST : false;
		$trace_id = null;
		if( isset( $_GET['trace_id'] ) ){
			$trace_id = trim( $_GET['trace_id'] );
		}
		if(!empty($_POST))
		{
			$response_from_paypal = $_POST;
			//if(isset($response_from_paypal['payment_status']) && $response_from_paypal['payment_status'] == "Completed")
			if(isset($response_from_paypal['payment_status']) && $response_from_paypal['payment_status'] == "Pending")
			{
				$custom_data = explode("|", $response_from_paypal['custom']);
				$user_id = $custom_data[0];
				$additional_info = $custom_data[1];
				$bookings = get_user_meta($user_id, "temp_order_data", true);
				$booking_post = array(
							  'post_title'    	=> 	'New Booking',
							  'post_content'  	=> 	'-',
							  'post_status'   	=>	'publish',
							  'post_type'		=>	UTALII_PREFIX . 'bookings'
							);
				$booking_id = wp_insert_post( $booking_post );
				if($booking_id && !is_null( $trace_id ) )
				{
					$bookings['booking_time'] = time();
					
					update_post_meta($booking_id, UTALII_MKP . "booking_id", $booking_id);
					update_post_meta($booking_id, UTALII_MKP . "trace_id", $trace_id);
					update_post_meta($booking_id, UTALII_MKP . "hotel_id", $bookings['hotel_id']);
					update_post_meta($booking_id, UTALII_MKP . "booking_time", $bookings['booking_time']);
					update_post_meta($booking_id, UTALII_MKP . "user_id", $bookings['user_id']);
					update_post_meta($booking_id, UTALII_MKP . "checkindate", $bookings['checkindate']);
					update_post_meta($booking_id, UTALII_MKP . "checkoutdate", $bookings['checkoutdate']);
					update_post_meta($booking_id, UTALII_MKP . "total_nights", $bookings['total_nights']);
					update_post_meta($booking_id, UTALII_MKP . "rooms", $bookings['rooms']);
					update_post_meta($booking_id, UTALII_MKP . "tax", $bookings['tax']);
					update_post_meta($booking_id, UTALII_MKP . "subtotal", $bookings['subtotal']);
					update_post_meta($booking_id, UTALII_MKP . "grand_total", $bookings['grand_total']);
					update_post_meta($booking_id, UTALII_MKP . "tax_percentage", $bookings['tax_percentage']);
					update_post_meta($booking_id, UTALII_MKP . "tax_html", $bookings['tax_html']);
					update_post_meta($booking_id, UTALII_MKP . "subtotal_html", $bookings['subtotal_html']);
					update_post_meta($booking_id, UTALII_MKP . "grand_total_html", $bookings['grand_total_html']);
					update_post_meta($booking_id, UTALII_MKP . "additional_info", $additional_info);
					update_post_meta($booking_id, UTALII_MKP . "payment_via", "paypal");
					update_post_meta($booking_id, UTALII_MKP . "payment_tx_id", $response_from_paypal['txn_id']);
					$update_booking_post = array(
								  'ID'           => $booking_id,
								  'post_title'   => $booking_id
							  );
					wp_update_post( $update_booking_post );
					$hso = new UTALII_Hotel_Search();
					$hso->book_rooms_on( $bookings['rooms'], $bookings['checkindate'], $bookings['checkoutdate'] );
				}
			}
		}
	}
}