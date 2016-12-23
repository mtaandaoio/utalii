<?php
if( !class_exists( 'UTALII_Activate' ) ){
	class UTALII_Activate{
		private static $_instance;
		
		public static function get_instance(){
			if( is_null( self::$_instance ) ){
				self::$_instance = new self();
			}
			
			return self::$_instance ;
		}
		
		function __construct(){
			add_action( 'utalii_plugin_activate', array( $this, 'create_pages' ) );
		}
		
		function create_pages(){
			$this->hotel_search_page();
			/* $this->view_hotels_page(); */
			/* $this->view_rooms_page(); */
			/* $this->room_cart_page(); */
			$this->my_bookings_page();
		}
		
		function is_published( $obj_id ){
			$obj = get_post( $obj_id );
			if( !is_null( $obj ) ){
				if( 'publish' == $obj->post_status ){
					return true;
				}
			}
			return false;
		}
		
		function hotel_search_page(){
			$flag = false;
			$post_id	=	get_option( 'utalii_hotel_search_page_id', false );
			if( $post_id ){
				if( $this->is_published( $post_id ) ){
					
				} else {
					$flag = true;
				}
			} else {
				$flag = true;
			}
			
			if( $flag ){
				$args	=	array(
								'post_title'	=>	'Hotel Search',
								'post_name'		=>	'hotel-search',
								'post_content'	=>	'[utalii_hotel_search]',
								'post_status'	=>	'publish',
								'post_type'		=>	'page',
							);
				
				$post_id = wp_insert_post( $args );
				
				if( $post_id ){
					update_option( 'utalii_hotel_search_page_id', $post_id );
				}
			}
		}
		
		function view_hotels_page(){
			$flag = false;
			$post_id	=	get_option( 'utalii_view_hotels_page_id', false );
			if( $post_id ){
				if( $this->is_published( $post_id ) ){
					
				} else {
					$flag = true;
				}
			} else {
				$flag = true;
			}
			
			if( $flag ){
				$args	=	array(
								'post_title'	=>	'View Hotels',
								'post_name'		=>	'view-hotels',
								'post_content'	=>	'[utalii_view_hotels]',
								'post_status'	=>	'publish',
								'post_type'		=>	'page',
							);
				
				$post_id = wp_insert_post( $args );
				
				if( $post_id ){
					update_option( 'utalii_view_hotels_page_id', $post_id );
				}
			}
		}
		
		function view_rooms_page(){
			$flag = false;
			$post_id	=	get_option( 'utalii_view_rooms_page_id', false );
			if( $post_id ){
				if( $this->is_published( $post_id ) ){
					
				} else {
					$flag = true;
				}
			} else {
				$flag = true;
			}
			
			if( $flag ){
				$args	=	array(
								'post_title'	=>	'View Rooms',
								'post_name'		=>	'view-rooms',
								'post_content'	=>	'[utalii_view_rooms]',
								'post_status'	=>	'publish',
								'post_type'		=>	'page',
							);
				
				$post_id = wp_insert_post( $args );
				
				if( $post_id ){
					update_option( 'utalii_view_rooms_page_id', $post_id );
				}
			}
		}
		
		function room_cart_page(){
			$flag = false;
			$post_id	=	get_option( 'utalii_room_cart_page_id', false );
			if( $post_id ){
				if( $this->is_published( $post_id ) ){
					
				} else {
					$flag = true;
				}
			} else {
				$flag = true;
			}
			
			if( $flag ){
				$args	=	array(
								'post_title'	=>	'Room Cart',
								'post_name'		=>	'room-cart',
								'post_content'	=>	'[utalii_room_cart]',
								'post_status'	=>	'publish',
								'post_type'		=>	'page',
							);
				
				$post_id = wp_insert_post( $args );
				
				if( $post_id ){
					update_option( 'utalii_room_cart_page_id', $post_id );
				}
			}
		}
		
		function my_bookings_page(){
			$flag = false;
			$post_id	=	get_option( 'utalii_my_bookings_page_id', false );
			if( $post_id ){
				if( $this->is_published( $post_id ) ){
					
				} else {
					$flag = true;
				}
			} else {
				$flag = true;
			}
			
			if( $flag ){
				$args	=	array(
								'post_title'	=>	'My Bookings',
								'post_name'		=>	'my-bookings',
								'post_content'	=>	'[utalii_my_bookings]',
								'post_status'	=>	'publish',
								'post_type'		=>	'page',
							);
				
				$post_id = wp_insert_post( $args );
				
				if( $post_id ){
					update_option( 'utalii_my_bookings_page_id', $post_id );
				}
			}
		}
	} /* end of class */
}
UTALII_Activate::get_instance();