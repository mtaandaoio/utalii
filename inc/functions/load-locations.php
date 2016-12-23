<?php
add_action( 'wp_ajax_utaliiajax_load_country_by_state_id', 'utaliiajax_load_country_by_state_id' );
function utaliiajax_load_country_by_state_id(){
	$data = array( 'country' => false );
	if( isset( $_REQUEST['state_id'] ) ){
		$state_id = trim( $_REQUEST['state_id'] );
		
		if( !empty( $state_id ) ){
			if( UTALII_Location::get_state( $state_id ) ){
				$country_id = get_post_meta( $state_id, '_utalii_country_id', true );
				$country_id = trim( $country_id );
				if( !empty( $country_id ) ){
					$data['country_id'] = $country_id;
				}
			}
		}
	}
	
	if( !empty( $data ) ){
		wp_send_json_success( $data );
	}
	
	wp_send_json_error($data);
}

add_action( 'wp_ajax_utaliiajax_load_state_by_city_id', 'utaliiajax_load_state_by_city_id' );
function utaliiajax_load_state_by_city_id(){
	$data = array( 'state' => false, 'country' => false );
	if( isset( $_REQUEST['city_id'] ) ){
		$city_id = trim( $_REQUEST['city_id'] );
		
		if( !empty( $city_id ) ){
			if( UTALII_Location::get_city( $city_id ) ){
				$state_id = get_post_meta( $city_id, '_utalii_state_id', true );
				$country_id = get_post_meta( $city_id, '_utalii_country_id', true );
				
				$state_id = trim( $state_id );
				$country_id = trim( $country_id );
				if( !empty( $state_id ) ){
					$data['state_id'] = $state_id;
				}
				if( !empty( $country_id ) ){
					$data['country_id'] = $country_id;
				}
			}
		}
	}
	
	if( !empty( $data ) ){
		wp_send_json_success( $data );
	}
	
	wp_send_json_error($data);
}

add_action( 'wp_ajax_utaliiajax_load_hotels_by_location_id', 'utaliiajax_load_hotels_by_location_id' );
add_action( 'wp_ajax_nopriv_utaliiajax_load_hotels_by_location_id', 'utaliiajax_load_hotels_by_location_id' );
function utaliiajax_load_hotels_by_location_id(){
	$data = array();
	
	if( isset( $_REQUEST['location_id'] ) ){
		$location_id = trim( $_REQUEST['location_id'] );
		$hsobj = new UTALII_Hotel_Search();
		$param = array(
			'orderby'	=>	'title',
			'order'		=>	'ASC',
		);
		$hotel_ids = $hsobj->get_hotels_by_location( array( $location_id ), $param );
		
		if( $hotel_ids ){
			$hotellist = array();
			foreach( $hotel_ids as $hid ) {
				$title = get_the_title( $hid );
				$hotellist[] = array( 'id' => $hid, 'title' => $title );
			}
			if( !empty( $hotellist ) ){
				$data = $hotellist;
			}
		}
	}
	
	if( !empty( $data ) ){
		wp_send_json_success( $data );
	}
	
	wp_send_json_error($data);
}

add_action( 'wp_ajax_utaliiajax_load_rooms_by_hotel_id', 'utaliiajax_load_rooms_by_hotel_id' );
add_action( 'wp_ajax_nopriv_utaliiajax_load_rooms_by_hotel_id', 'utaliiajax_load_rooms_by_hotel_id' );
function utaliiajax_load_rooms_by_hotel_id(){
	$data = array();
	
	if( isset( $_REQUEST['hotel_id'] ) ){
		$hotel_id = trim( $_REQUEST['hotel_id'] );
		$hsobj = new UTALII_Hotel_Search();
		$param = array(
			'orderby'	=>	'title',
			'order'		=>	'ASC',
		);
		$room_ids = $hsobj->get_rooms_by_hotel( array( $hotel_id ), $param );
		
		if( $room_ids ){
			$roomlist = array();
			foreach( $room_ids as $rid ) {
				$title = get_the_title( $rid );
				$roomlist[] = array( 'id' => $rid, 'title' => $title );
			}
			if( !empty( $roomlist ) ){
				$data = $roomlist;
			}
		}
	}
	
	if( !empty( $data ) ){
		wp_send_json_success( $data );
	}
	
	wp_send_json_error($data);
}
