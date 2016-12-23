<?php
if( !class_exists( 'UTALII_Template_Loader' ) ){
	class UTALII_Template_Loader{
		private static $_instance;
		
		public static function get_instance(){
			if( is_null( self::$_instance ) ){
				self::$_instance = new self();
			}
			
			return self::$_instance;
		}
		
		function __construct(){
			add_filter( 'template_include', array( $this, 'load_templates' ) );
			
			$this->global_requests();
		}
		
		function load_templates( $template ){
			
			$theme_template = array();
			$file_template = '';
			$view_rooms_page_id = get_option( 'utalii_view_rooms_page_id', -1 );
			$view_hotels_page_id = get_option( 'utalii_view_hotels_page_id', -1 );
			
			if ( is_single() && get_post_type() == UTALII_PREFIX . 'rooms' ) {
				$file_template = 'single-' . UTALII_PREFIX . 'rooms.php';
				$theme_template[] = $file_template;
				$theme_template[] = UTALII_OVERRIDE_TEMPLATEPATH . $file_template;
			} else if( is_single() && get_post_type() == UTALII_PREFIX . 'hotels' ){
				$file_template = 'single-' . UTALII_PREFIX . 'hotels.php';
				$theme_template[] = $file_template;
				$theme_template[] = UTALII_OVERRIDE_TEMPLATEPATH . $file_template;
			} else if ( is_page( $view_rooms_page_id ) || is_post_type_archive( UTALII_PREFIX . 'rooms' ) ) {
				
				$file_template 	= 'archive-' . UTALII_PREFIX . 'rooms.php';
				$theme_template[] = $file_template;
				$theme_template[] = UTALII_OVERRIDE_TEMPLATEPATH . $file_template;
			} else if ( is_page( $view_hotels_page_id ) || is_post_type_archive( UTALII_PREFIX . 'hotels' ) ) {
				
				$file_template 	= 'archive-' . UTALII_PREFIX . 'hotels.php';
				$theme_template[] = $file_template;
				$theme_template[] = UTALII_OVERRIDE_TEMPLATEPATH . $file_template;
			}
			
			if ( $file_template ) {
				$template = locate_template( array_unique( $theme_template ) );
				if ( ! $template ) {
					$template = UTALII_TEMPLATEPATH . $file_template;
				}
			}
			
			return $template;
		}
		
		function global_requests(){
			add_action( 'admin_post_utaliihotelsearchsubmit', array($this, 'hotel_search_submit_handler' ) );
			add_action( 'admin_post_nopriv_utaliihotelsearchsubmit', array($this, 'hotel_search_submit_handler' ) );
			
			add_action( 'admin_post_utaliiviewroomsbyhotelsubmit', array($this, 'view_rooms_by_hotel_submit_handler' ) );
			add_action( 'admin_post_nopriv_utaliiviewroomsbyhotelsubmit', array($this, 'view_rooms_by_hotel_submit_handler' ) );
			
			add_action( 'admin_post_utaliibookroomnow', array($this, 'book_rooms_now_submit_handler' ) );
			add_action( 'admin_post_nopriv_utaliibookroomnow', array($this, 'book_rooms_now_submit_handler' ) );
			
			add_action( 'admin_post_utaliiaddtoroomcart', array($this, 'add_room_cart_submit_handler' ) );
			add_action( 'admin_post_nopriv_utaliiaddtoroomcart', array($this, 'add_room_cart_submit_handler' ) );
		}
		
		function hotel_search_submit_handler(){
			/* validation start */
			$flag = false;
			$http_referer = null;
			$goback = null;
			$post_id = null;
			$form_errors	=	array(
									'error'	=>	false,
									'data'	=>	array(),
								);
			
			if( isset( $_SERVER['HTTP_REFERER'] ) ){
				$http_referer = trim($_SERVER['HTTP_REFERER']);
				if( empty( $http_referer ) ){
					$http_referer = null;
				}
			} else {
				/* if http_referer is not set, then go to home */
				wp_redirect( home_url() );
				exit;
			}
			
			if( isset( $_POST['utalii_post_id'] ) ){
				$post_id = trim( $_POST['utalii_post_id'] );
				if( empty( $post_id ) ){
					/* if post id from referer is not set, then go to home */
					wp_redirect( home_url() );
					exit;
				} else {
					$goback = get_permalink( $post_id );
				}
			} else {
				/* if post id from referer is not set, then go to home */
				wp_redirect( home_url() );
				exit;
			}
			
			/* if( strstr( $http_referer, $goback ) ){
				//if http_referer and post_id url doesn't match ten go to home
				wp_redirect( home_url() );
				exit;
			}
			*/
			
			if( !isset( $_POST['utalii_hotel_search_submit'] ) ){
				wp_redirect( home_url() );
				exit;
			}
			
			if(
				!isset(
					$_POST['location'],
					$_POST['checkindate'],
					$_POST['checkoutdate']
				)
			){
				
			}
			/* validation stop */
			$location = trim( $_POST['location'] );
			$checkindate = trim($_POST['checkindate']);
			$checkoutdate = trim($_POST['checkoutdate']);
			
			if( empty( $location ) ){
				$form_errors['error'] = true;
				$form_errors['data']['location'] = "Please choose location";
			}
			
			if( empty( $checkindate ) ){
				$form_errors['error'] = true;
				$form_errors['data']['checkindate'] = "Please choose check-in date";
			}
			
			if( empty( $checkoutdate ) ){
				$form_errors['error'] = true;
				$form_errors['data']['checkoutdate'] = "Please choose check-out date";
			}
			
			/* check any form error and put in session array */
			if( $form_errors['error'] ){
				$_SESSION['utalii_error']['hotelsearchsubmit']	=	$form_errors;
				$goback = add_query_arg( array( 'error' => 1 ), $goback );
				wp_redirect( $goback );
				exit;
			}
			/* check any form error and put in session array end */
			
			$hotel_search_form_data = array();
			$hotel_search_form_data['location'] = $location;
			$hotel_search_form_data['checkindate'] = $checkindate;
			$hotel_search_form_data['checkoutdate'] = $checkoutdate;
			$_SESSION['hotel_search_form_data'] = $hotel_search_form_data;
			
			$view_hotels_url = UTALII_Misc::view_hotels_page_url();
			wp_redirect( $view_hotels_url );
			exit;
		}
		
		function view_rooms_by_hotel_submit_handler(){
			/* validation start */
			$flag = false;
			$http_referer = null;
			$goback = null;
			$post_id = null;
			$form_errors	=	array(
									'error'	=>	false,
									'data'	=>	array(),
								);
			
			if( isset( $_SERVER['HTTP_REFERER'] ) ){
				$http_referer = trim($_SERVER['HTTP_REFERER']);
				if( empty( $http_referer ) ){
					$http_referer = null;
				}
			} else {
				/* if http_referer is not set, then go to home */
				wp_redirect( home_url() );
				exit;
			}
			
			if( isset( $_POST['utalii_post_id'] ) ){
				$post_id = trim( $_POST['utalii_post_id'] );
				if( empty( $post_id ) ){
					/* if post id from referer is not set, then go to home */
					wp_redirect( home_url() );
					exit;
				} else {
					$goback = get_permalink( $post_id );
				}
			} else {
				/* if post id from referer is not set, then go to home */
				wp_redirect( home_url() );
				exit;
			}
			
			/* if( strstr( $http_referer, $goback ) ){
				//if http_referer and post_id url doesn't match ten go to home
				wp_redirect( home_url() );
				exit;
			}
			*/
			
			if( !isset( $_POST['utalii_view_rooms_by_hotel'] ) ){
				wp_redirect( home_url() );
				exit;
			}
			
			if( !isset( $_POST['hotel_id'] ) ){
				wp_redirect( $goback );
				exit;
			} else {
				$hotel_id = trim( $_POST['hotel_id'] );
			}
			/* validation stop */
			
			/* check any form error and put in session array */
			/* form error checks goes here */
			if( $form_errors['error'] ){
				$_SESSION['utalii_error']['viewroomsbyhotelsubmit']	=	$form_errors;
				$goback = add_query_arg( array( 'error' => 1 ), $goback );
				wp_redirect( $goback );
				exit;
			}
			/* check any form error and put in session array end */
			
			$view_rooms_by_hotel_data = array();
			$view_rooms_by_hotel_data['hotel_id'] = $hotel_id;
			$_SESSION['view_rooms_by_hotel_data'] = $view_rooms_by_hotel_data;
			
			$view_room_url = UTALII_Misc::view_rooms_page_url();
			wp_redirect( $view_room_url );
			exit;
		}
		
		function book_rooms_now_submit_handler(){
			/* validation start */
			$flag = false;
			$http_referer = null;
			$goback = null;
			$post_id = null;
			$form_errors	=	array(
									'error'	=>	false,
									'data'	=>	array(),
								);
			
			if( isset( $_SERVER['HTTP_REFERER'] ) ){
				$http_referer = trim($_SERVER['HTTP_REFERER']);
				if( empty( $http_referer ) ){
					$http_referer = null;
				}
			} else {
				/* if http_referer is not set, then go to home */
				wp_redirect( home_url() );
				exit;
			}
			
			if( isset( $_POST['utalii_post_id'] ) ){
				$post_id = trim( $_POST['utalii_post_id'] );
				if( empty( $post_id ) ){
					/* if post id from referer is not set, then go to home */
					wp_redirect( home_url() );
					exit;
				} else {
					$goback = get_permalink( $post_id );
				}
			} else {
				/* if post id from referer is not set, then go to home */
				wp_redirect( home_url() );
				exit;
			}
			
			/* if( strstr( $http_referer, $goback ) ){
				//if http_referer and post_id url doesn't match ten go to home
				wp_redirect( home_url() );
				exit;
			}
			*/
			
			if( !isset( $_POST['utaliibookroomnow'] ) ){
				wp_redirect( home_url() );
				exit;
			}
			
			if(
				!isset(
					$_POST['bookroomnow_location'],
					$_POST['bookroomnow_checkindate'],
					$_POST['bookroomnow_checkoutdate'],
					$_POST['bookroomnow_hotel_id'],
					$_POST['bookroomnow_room_id']
				)
			){
				wp_redirect( $goback );
				exit;
			}
			/* validation stop */
			
			/* check any form error and put in session array */
			
			$location = trim ( $_POST['bookroomnow_location'] );
			$checkindate = trim ( $_POST['bookroomnow_checkindate'] );
			$checkoutdate = trim ( $_POST['bookroomnow_checkoutdate'] );
			$hotel_id = trim ( $_POST['bookroomnow_hotel_id'] );
			$room_id = trim ( $_POST['bookroomnow_room_id'] );
			
			if( empty( $location ) || ( $_SESSION['hotel_search_form_data']['location'] != $location ) ){
				$form_errors['error'] = true;
				$form_errors['data']['location'] = "Please choose correct location.";
			}
			
			if( empty( $checkindate ) || ( $_SESSION['hotel_search_form_data']['checkindate'] != $checkindate ) ){
				$form_errors['error'] = true;
				$form_errors['data']['checkindate'] = "Please choose correct check-in date.";
			}
			
			if( empty( $checkoutdate ) || ( $_SESSION['hotel_search_form_data']['checkoutdate'] != $checkoutdate ) ){
				$form_errors['error'] = true;
				$form_errors['data']['checkoutdate'] = "Please choose correct check-out date.";
			}
			
			if( empty( $checkoutdate ) || ( $_SESSION['view_rooms_by_hotel_data']['hotel_id'] != $hotel_id ) ){
				$form_errors['error'] = true;
				$form_errors['data']['hotel_id'] = "Please choose correct hotel.";
			}
			
			if( empty( $room_id ) ){
				$form_errors['error'] = true;
				$form_errors['data']['room_id'] = "Please choose correct room.";
			}
			
			if( $form_errors['error'] ){
				$_SESSION['utalii_error']['viewroomsbyhotelsubmit']	=	$form_errors;
				$goback = add_query_arg( array( 'error' => 1 ), $goback );
				wp_redirect( $goback );
				exit;
			}
			/* check any form error and put in session array end */
			
			$book_rooms_now_data = array();
			$book_rooms_now_data['room_id'] = $room_id;
			$_SESSION['book_rooms_now_data'] = $book_rooms_now_data;
			
			$hotel_search_page_url  = UTALII_Misc::hotel_search_page_url();
			wp_redirect( $hotel_search_page_url );
			exit;
		}
		
		function add_room_cart_submit_handler(){
			/* validation start */
			$flag = false;
			$http_referer = null;
			$goback = null;
			$post_id = null;
			$form_errors	=	array(
									'error'	=>	false,
									'data'	=>	array(),
								);
			
			if( isset( $_SERVER['HTTP_REFERER'] ) ){
				$http_referer = trim($_SERVER['HTTP_REFERER']);
				if( empty( $http_referer ) ){
					$http_referer = null;
				}
			} else {
				/* if http_referer is not set, then go to home */
				wp_redirect( home_url() );
				exit;
			}
			
			if( isset( $_POST['utalii_post_id'] ) ){
				$post_id = trim( $_POST['utalii_post_id'] );
				if( empty( $post_id ) ){
					/* if post id from referer is not set, then go to home */
					wp_redirect( home_url() );
					exit;
				} else {
					$goback = get_permalink( $post_id );
				}
			} else {
				/* if post id from referer is not set, then go to home */
				wp_redirect( home_url() );
				exit;
			}
			
			/* if( strstr( $http_referer, $goback ) ){
				//if http_referer and post_id url doesn't match ten go to home
				wp_redirect( home_url() );
				exit;
			}
			*/
			
			if( !isset( $_POST['utaliibookroomnow'] ) ){
				wp_redirect( home_url() );
				exit;
			}
			
			if(
				!isset(
					$_POST['hotel_id'],
					$_POST['room_id'],
					$_POST['checkindate'],
					$_POST['checkoutdate']
				)
			){
				wp_redirect( $goback );
				exit;
			}
			/* validation stop */
			
			$hotel_id = trim( $_POST['hotel_id'] );
			$room_id = trim( $_POST['room_id'] );
			$checkindate = trim( $_POST['checkindate'] );
			$checkoutdate = trim( $_POST['checkoutdate'] );
			
			/* check any form error and put in session array */
			if( empty( $hotel_id ) ){
				$form_errors['error'] = true;
				$form_errors['data']['hotel_id'] = "Please choose correct hotel.";
			}
			
			if( empty( $room_id ) ){
				$form_errors['error'] = true;
				$form_errors['data']['room_id'] = "Please choose correct room.";
			}
			
			if( empty( $checkindate ) ){
				$form_errors['error'] = true;
				$form_errors['data']['checkindate'] = "Please choose check-in date.";
			}
			
			if( empty( $checkoutdate ) ){
				$form_errors['error'] = true;
				$form_errors['data']['checkoutdate'] = "Please choose check-out date.";
			}
			
			if( $form_errors['error'] ){
				$_SESSION['utalii_error']['viewroomsbyhotelsubmit']	=	$form_errors;
				$goback = add_query_arg( array( 'error' => 1 ), $goback );
				wp_redirect( $goback );
				exit;
			}
			/* check any form error and put in session array end */
			
		}
		
	} /* end of class */
}
UTALII_Template_Loader::get_instance();