<?php
if( !class_exists( 'UTALII_Room_Search' ) ){
	class UTALII_Room_Search{
		private $room_id = null;
		private $total_rooms = null;
		private $hotel_id = null;
		private $room_name = null;
		private $hotel_name = null;
		
		function __construct( $room_id ){
			$this->room_id = $room_id;
			$this->hotel_id = (int) get_post_meta( $this->room_id, UTALII_MKP . 'hotel_id', true );
			$this->total_rooms = (int) get_post_meta( $this->room_id, UTALII_MKP . 'total_rooms', true );
			
		}
		
		function is_available_on( $dates ){
			if( is_array( $dates ) ){
				foreach( $dates as $d ){
					if( !$this->is_available_on_the_day( $d ) ){
						return false;
					}
				}
				return true;
			}
			
			return $this->is_available_on_the_day( $dates );
		}
		
		function is_available_on_the_day( $day, $return = false ){
			$remaining_rooms = $this->get_remaining_rooms_on( $day );
			if( $remaining_rooms > 0 ){
				if( $return ){
					return $remaining_rooms;
				}
				return true;
			}
			return false;
		}
		
		function get_remaining_rooms_on( $date ){
			$on_date_bookings = (int) get_post_meta( $this->room_id, UTALII_MKP . 'bookings_on_' . $date , true );
			$remaining_rooms = $this->total_rooms - $on_date_bookings;
			
			return $remaining_rooms;
		}
		
		function get_total_rooms(){
			return $this->total_rooms;
		}
		
		function get_min_available_rooms( $dates ){
			$return = 0;
			$min_rooms = array();
			
			if( is_array( $dates ) ){
				foreach( $dates as $d ){
					$min_rooms[] = $this->is_available_on_the_day( $d, true );
				}
				
			}
			
			if( !empty( $min_rooms ) ){
				$return = min( $min_rooms );
			}
			
			return $return;
		}
		
		
	}/* end of class*/
}

if( !class_exists( 'UTALII_Hotel_Search' ) ){
	class UTALII_Hotel_Search{
		
		public function search_rooms( $args = array(
			'location'		=>	null,
		) ){
			
			$location_ids = array();
			
			$hotel_ids = false;
			$room_ids = false;
			
			if( !is_null( $args['location'] ) ){
				$location_ids = $args['location'];
			}
			
			$hotel_ids = $this->get_hotels_by_location( $location_ids );
			
			if( $hotel_ids ){
				$room_ids = $this->get_rooms_by_hotel( $hotel_ids );
			}
			
			return $room_ids;
		}
		
		public function get_hotels_by_location( $in_ids = array(), $param = array() ){
			
			$meta_query	=	array();
			if( !empty( $in_ids ) ){
				foreach( $in_ids as $in_id ){
					$meta_query[]	=	array(
											'key'	=>	UTALII_MKP . 'city_id',
											'value'	=>	$in_id,
											'compare'		=>	'=',
										);
					$meta_query[]	=	array(
											'key'	=>	UTALII_MKP . 'state_id',
											'value'	=>	$in_id,
											'compare'		=>	'=',
										);
					$meta_query[]	=	array(
											'key'	=>	UTALII_MKP . 'country_id',
											'value'	=>	$in_id,
											'compare'		=>	'=',
										);
				}
				
				if( !empty( $meta_query ) ){
					$meta_query['relation'] = 'OR';
				}
			}
			
			$args	=	array(
							'post_type'			=>	'utalii_hotels',
							'post_status'		=>	'publish',
							'posts_per_page'	=>	'-1',
							'fields'			=>	'ids',
						);
			if( !empty( $meta_query ) ){
				$args['meta_query'] = $meta_query;
			}
			
			if( isset( $param['orderby'], $param['order'] ) ){
				$args['order'] = $param['order'];
				$args['orderby'] = $param['orderby'];
			}
			
			$posts	=	get_posts( $args );
			
			if( $posts ){
				if( !empty( $posts ) && count( $posts ) ){
					return $posts;
				}
			}
			
			return false;
		}
		
		public function get_rooms_by_hotel( $in_ids=array(), $param = array() ){
			
			$meta_query	=	array();
			if( !empty( $in_ids ) ){
				foreach( $in_ids as $in_id ){
					$meta_query[]	=	array(
											'key'	=>	UTALII_MKP . 'hotel_id',
											'value'	=>	$in_id,
											'compare'		=>	'=',
										);
				}
				
				if( !empty( $meta_query ) ){
					$meta_query['relation'] = 'OR';
				}
			}
			
			$args	=	array(
							'post_type'			=>	'utalii_rooms',
							'post_status'		=>	'publish',
							'posts_per_page'	=>	'-1',
							'fields'			=>	'ids',
						);
			if( !empty( $meta_query ) ){
				$args['meta_query'] = $meta_query;
			}
			
			if( isset( $param['orderby'], $param['order'] ) ){
				$args['order'] = $param['order'];
				$args['orderby'] = $param['orderby'];
			}
			
			$posts	=	get_posts( $args );
			if( $posts ){
				if( !empty( $posts ) && count( $posts ) ){
					return $posts;
				}
			}
			
			return false;
		}
		
		function get_available_rooms( $data ){
			$available_rooms = array();
			
			/* get hotels for location */
			$hotels = $this->get_hotels_by_location( array( $data['location'] ) );
			
			if( $hotels ){
				$daterange = $this->get_date_range( $data['checkindate'], $data['checkoutdate'] );
				if( $daterange ){
					if( isset( $data['hotel_id'] ) ){
						$hotels = array( $data['hotel_id'] );
					}
					$rooms = $this->get_non_booked_rooms_by_hotel( $hotels, $daterange );
					if( $rooms ){
						$available_rooms = $rooms;
					}
				}
			}
			
			if( !empty( $available_rooms ) ){
				$available_rooms = array_unique( $available_rooms );
				return $available_rooms;
			}
			
			return false;
		}
		
		function get_available_hotels( $data ){
			$available_hotels = array();
			
			$available_rooms = $this->get_available_rooms( $data );
			
			if( $available_rooms ){
				$hotels = array();
				foreach( $available_rooms as $ar ){
					$hotel_id = get_post_meta( $ar, UTALII_MKP . 'hotel_id', true );
					$hotel_id = trim( $hotel_id );
					if( !empty( $hotel_id ) ){
						$hotels[] = $hotel_id;
					}
				}
				if( !empty( $hotels ) ){
					$available_hotels = $hotels;
					$available_hotels = array_unique( $available_hotels );
				}
			}
			
			if( !empty( $available_hotels ) ){
				return $available_hotels;
			}
			
			return false;
		}
		
		function get_non_booked_rooms_by_hotel( $hotels, $daterange ){
			$rooms = $this->get_rooms_by_hotel( $hotels );
			$filtered_rooms = array();
			if( $rooms ){
				foreach( $rooms as $room ){
					$rsobj = new UTALII_Room_Search( $room );
					if( $rsobj->is_available_on( $daterange ) ){
						$filtered_rooms[] = $room;
					}
				}
				$rooms = $filtered_rooms;
			}
			
			if( !empty( $rooms ) ){
				return $rooms;
			}
			
			return false;
		}
		
		function get_all_non_booked_rooms( $location, $checkindate, $checkoutdate ){
			$rooms = array();
			$args	=	array(
							'post_type'			=>	'utalii_rooms',
							'post_status'		=>	'publish',
							'posts_per_page'	=>	'-1',
							'fields'			=>	'ids',
						);
			$hotel_ids = $this->get_hotels_by_location( array( $location ) );
			UTALII_Misc::print_r($hotel_ids);
			if( $hotel_ids ){
				$args['post__in'] = array(631);
			}
			$posts	=	get_posts( $args );
			foreach( $posts as $p ){
				echo '<br />'.get_post_meta( $p, UTALII_MKP . 'hotel_id', true);
			}
			
			if( $posts ){
				if( !empty( $posts ) && count( $posts ) ){
					$daterange = $this->get_date_range( $checkindate, $checkoutdate );
					
					$available_rooms = $this->filter_rooms_available_on_dates( $posts, $daterange );
					UTALII_Misc::print_r($available_rooms);
					if( $available_rooms ){
						$rooms = $available_rooms;
					}
				}
			}
			
			if( !empty( $rooms ) ){
				return $rooms;
			}
			
			return false;
		}
		
		function filter_rooms_available_on_dates( $rooms, $daterange ){
			$filtered_rooms = array();
			if( $rooms ){
				foreach( $rooms as $room ){
					$rsobj = new UTALII_Room_Search( $room );
					if( $rsobj->is_available_on( $daterange ) ){
						$filtered_rooms[] = $room;
					}
				}
				$rooms = $filtered_rooms;
			}
			
			if( !empty( $rooms ) ){
				return $rooms;
			}
			
			return false;
		}
		
		function get_non_booked_rooms_by_hotel_dep( $hotels, $daterange ){
			$meta_query	=	array();
			$hotel_metas_flag = false;
			$booking_date_meta_flag = false;
			
			if( !empty( $hotels ) ){
				$hotel_metas = array();
				foreach( $hotels as $hotel_id ){
					$hotel_metas[]	=	array(
											'key'		=>	UTALII_MKP . 'hotel_id',
											'value'		=>	$hotel_id,
											'compare'	=>	'=',
										);
				}
				if( !empty( $hotel_metas ) ){
					$hotel_metas['relation'] = 'OR';
					$hotel_metas_flag = true;
				}
				
				
				$booking_date_meta = array();
				if( !empty( $daterange ) ){
					$booking_date_meta[]	=	array(
													'key'		=>	UTALII_MKP . 'booking_date',
													'value'		=>	$daterange,
													'compare'	=>	'IN',
												);
					$booking_date_meta[]	=	array(
													'key'		=>	UTALII_MKP . 'booking_date',
													'compare'	=>	'NOT EXISTS',
												);
					$booking_date_meta['relation']	=	'OR';
					
					$booking_date_meta_flag = true;
				}
				
				if( $hotel_metas_flag && $booking_date_meta_flag ){
					$meta_query[] = $hotel_metas;
					$meta_query[] = $booking_date_meta;
					$meta_query['relation'] = 'AND';
				}
			}
			
			$args	=	array(
							'post_type'			=>	'utalii_rooms',
							'post_status'		=>	'publish',
							'posts_per_page'	=>	'-1',
							'fields'			=>	'ids',
						);
			
			if( !empty( $meta_query ) ){
				$args['meta_query'] = $meta_query;
			}
			
			$posts	=	get_posts( $args );
			if( $posts ){
				if( !empty( $posts ) && count( $posts ) ){
					return $posts;
				}
			}
			
			return false;
		}
		
		function get_date_range( $from_date, $to_date){ /* Y-m-d */
			$date_range = array();
			
			$from_date = mktime(
				1, 0, 0,
				substr( $from_date, 5, 2 ),
				substr( $from_date, 8, 2),
				substr($from_date,0,4)
			);
			
			$to_date = mktime(
				1, 0, 0,
				substr( $to_date, 5, 2 ),
				substr( $to_date, 8, 2),
				substr( $to_date, 0, 4)
			);
			
			if( $to_date >= $from_date ){
				array_push( $date_range, date( 'Y-m-d', $from_date ) );
				
				while( $from_date < $to_date ){
					$from_date += 86400;
					array_push( $date_range, date( 'Y-m-d', $from_date ) );
				}
			}
			
			if( !empty( $date_range ) ){
				return $date_range;
			}
			
			return false;
		}
		
		function is_the_room_available( $data ){
			$error = false;
			if( empty( $data ) ){
				$error = true;
			}
			
			if( !$error ){
				if( !isset( $data['room_id'], $data['checkindate'], $data['checkoutdate'] ) ){
					$error = true;
				} else {
					$room_id = $data['room_id'];
					$checkindate = $data['checkindate'];
					$checkoutdate = $data['checkoutdate'];
				}
			}
			
			if( !$error ){
				$is_published = UTALII_Misc::is_published( $room_id );
				if( !$is_published ){
					$error = true;
				}
			}
			
			if( !$error ){
				
			}
			
			return false;
		}
		
		function get_required_rooms( $roomid, $adult_stay, $children_stay, $checkindate, $checkoutdate ){
			$return = array(
				'adult'						=>	false,
				'children'					=>	false,
				'adult_room_required'		=>	0,
				'children_room_required'	=>	0,
				'total_room_required'		=>	0,
			);
			
			
			$dates = $this->get_date_range( $checkindate, $checkoutdate );
			$robj = new UTALII_Room_Search( $roomid );
			
			$max_rooms_available = $robj->get_min_available_rooms( $dates );
			$max_adult_occupancy = (int) get_post_meta( $roomid, UTALII_MKP . 'max_adult_occupancy', true );
			$max_children_occupancy = (int) get_post_meta( $roomid, UTALII_MKP . 'max_children_occupancy', true );
			
			$adult_occupancy_available = $max_rooms_available * $max_adult_occupancy;
			$children_occupancy_available = $max_rooms_available * $max_children_occupancy;
			
			if( ( $max_adult_occupancy < 1 ) && ( $max_children_occupancy < 1 ) ){
				
				/* no room available, zero occupancy */
				
			} else if( ( $adult_occupancy_available < $adult_stay ) && ( $children_occupancy_available < $children_stay ) ){
				
				/* no room available, occupancy is below then required */
				
			} else if( ( $adult_occupancy_available < $adult_stay ) || ( $children_occupancy_available < $children_stay ) ){
				if( ( $adult_occupancy_available < $adult_stay ) ){
					$return['children'] = true;
				} else if( $children_occupancy_available < $children_stay ){
					$return['adult'] = true;
				}
			} else if( $adult_stay == 0 && $children_stay == 0 ){
				$return['adult'] = true;
				$return['children'] = true;
			} else {
				
				if( $adult_stay == 0){
					$return['adult'] = true;
					$return['adult_room_required'] = 0;
				} else if( $max_adult_occupancy > 0 ){
					if( $adult_stay == 1){
						$return['adult'] = true;
						$return['adult_room_required'] = 1;
					} else {
						$return['adult'] = true;
						$return['adult_room_required'] = ceil( ( $adult_stay / $max_adult_occupancy ) );
					}
				}
				
				if( $children_stay == 0){
					$return['children'] = true;
					$return['children_room_required'] = 0;
				} else if( $max_children_occupancy > 0 ){
					if( $children_stay == 1){
						$return['children'] = true;
						$return['children_room_required'] = 1;
					} else {
						$return['children'] = true;
						$return['children_room_required'] = ceil( ( $children_stay / $max_children_occupancy ) );
					}
				}
			}
			
			$return['total_room_required'] = $return['adult_room_required'];
			if( $return['adult_room_required'] < $return['children_room_required'] ){
				$return['total_room_required'] = $return['children_room_required'];
			}
			
			return $return;
		}
		
		
		function book_rooms_on( $rooms, $checkindate, $checkoutdate ){
			$dates = $this->get_date_range( $checkindate, $checkoutdate );
			$date_count = count( $dates );
			
			foreach( $rooms as $room_id => $rdata ){
				$rooms_required = $rdata['rooms_required'];
				if( $date_count == 1 ){
					$this->book_room_on( $room_id, $dates[0], $rooms_required );
				} else {
					for( $i=0; $i<($date_count - 1); $i++ ){
						$this->book_room_on( $room_id, $dates[$i], $rooms_required );
					}
				}
			}
		}
		
		function book_room_on( $room_id , $date, $required_room ){
			$current_bookings = (int ) get_post_meta( $room_id, UTALII_MKP . 'bookings_on_' . $date , true );
			$updated_bookings = ( $current_bookings + $required_room );
			update_post_meta( $room_id, UTALII_MKP . 'bookings_on_' . $date , $updated_bookings );
		}
	} /* end of class */
}