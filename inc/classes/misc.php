<?php
if( !class_exists( 'UTALII_Misc' ) ){
	class UTALII_Misc{
		
		public static function get_hotel( $id ){
			$args = array(
				'p'					=>	$id,
				'post_type'			=>	array( 'utalii_hotels' ),
				'post_status'		=>	array( 'publish' ),
				'pagination'		=>	false,
				'posts_per_page'	=>	'-1',
				'order'				=>	'ASC',
				'orderby'			=>	'title',
				'cache_results'		=>	false,
			);
			
			$post = get_posts( $args );
			if( !empty( $post ) && ( 1 == count($post) ) ){
				$p = $post[0];
				$post = array( 'id' => $p->ID, 'slug' => $p->post_name, 'title' => $p->post_title );
				return $post;
			}
			return false;
		}
		
		public static function get_hotels(){
			$args = array(
				'post_type'			=>	array( 'utalii_hotels' ),
				'post_status'		=>	array( 'publish' ),
				'pagination'		=>	false,
				'posts_per_page'	=>	'-1',
				'order'				=>	'ASC',
				'orderby'			=>	'title',
				'cache_results'		=>	false,
			);
			
			
			$posts = get_posts( $args );
			$locations = array();
			if( is_array( $posts ) && count( $posts) ){
				foreach( $posts as $p ){
					$id = $p->ID;
					$slug = $p->post_name;
					$title = $p->post_title;
					$locations[$id] = array( 'id' => $id, 'slug' => $slug, 'title' => $title );
				}
				
				if( is_array( $locations ) && count( $locations) ){
					return $locations;
				}
			}
			return false;
		}
		
		public static function get_room( $id ){
			$args = array(
				'p'					=>	$id,
				'post_type'			=>	array( 'utalii_rooms' ),
				'post_status'		=>	array( 'publish' ),
				'pagination'		=>	false,
				'posts_per_page'	=>	'-1',
				'order'				=>	'ASC',
				'orderby'			=>	'title',
				'cache_results'		=>	false,
			);
			
			$post = get_posts( $args );
			if( !empty( $post ) && ( 1 == count($post) ) ){
				$p = $post[0];
				$post = array( 'id' => $p->ID, 'slug' => $p->post_name, 'title' => $p->post_title );
				return $post;
			}
			return false;
		}
		
		public static function get_rooms(){
			$args = array (
				'post_type'			=> array( 'utalii_rooms' ),
				'post_status'		=>	array( 'publish' ),
				'pagination'		=>	false,
				'posts_per_page'	=>	'-1',
				'order'				=>	'ASC',
				'orderby'			=>	'title',
				'cache_results'		=>	false,
			);
			
			$posts = get_posts( $args );
			$locations = array();
			if( is_array( $posts ) && count( $posts) ){
				foreach( $posts as $p ){
					$id = $p->ID;
					$slug = $p->post_name;
					$title = $p->post_title;
					$locations[$id] = array( 'id' => $id, 'slug' => $slug, 'title' => $title );
				}
				
				if( is_array( $locations ) && count( $locations) ){
					return $locations;
				}
			}
			
			return false;
		}
		
		public static function get_real_locations($params = array() ){
			$all_rooms = self::get_rooms();
			
			$cities = array();
			$states = array();
			$countries = array();
			$locations = array();
			$include_country = true;
			$include_state = true;
			$include_city = true;
			if( !empty( $params ) ){
				if( isset( $params['country'] ) ){
					if( !$params['country'] ){
						$include_country = false;
					}
				}
				if( isset( $params['state'] ) ){
					if( !$params['state'] ){
						$include_state = false;
					}
				}
				if( isset( $params['city'] ) ){
					if( !$params['city'] ){
						$include_city = false;
					}
				}
			}
			
			$rooms_having_hotels = array();
			if( $all_rooms ){
				foreach( $all_rooms as $all_room ){
					$ar = $all_room['id'];
					
					$hotel_id = get_post_meta( $ar, UTALII_MKP . 'hotel_id', true );
					$hotel_id = trim( $hotel_id );
					if( !empty( $hotel_id ) && is_numeric( $hotel_id ) ){
						if( self::is_published( $hotel_id, 'utalii_hotels' ) ){
							$city_id = get_post_meta( $hotel_id, UTALII_MKP . 'city_id', true );
							$state_id = get_post_meta( $hotel_id, UTALII_MKP . 'state_id', true );
							$country_id = get_post_meta( $hotel_id, UTALII_MKP . 'country_id', true );
							
							$city_id = trim($city_id);
							$state_id = trim($state_id);
							$country_id = trim($country_id);
							
							if( !empty( $city_id ) && self::is_published( $city_id, 'utalii_city' ) ){
								$p = get_post( $city_id );
								$id = $p->ID;
								$slug = $p->post_name;
								$title = $p->post_title;
								if( !array_key_exists( $city_id, $cities ) ){
									$cities[$city_id] = array( 'id' => $id, 'slug' => $slug, 'title' => $title );
								}
							}
							if( !empty( $state_id ) && self::is_published( $state_id, 'utalii_state' ) ){
								$p = get_post( $state_id );
								$id = $p->ID;
								$slug = $p->post_name;
								$title = $p->post_title;
								
								if( !array_key_exists( $state_id, $states ) ){
									$states[$state_id] = array( 'id' => $id, 'slug' => $slug, 'title' => $title );
								}
							}
							if( !empty( $country_id ) && self::is_published( $country_id, 'utalii_country' ) ){
								$p = get_post( $country_id );
								$id = $p->ID;
								$slug = $p->post_name;
								$title = $p->post_title;
								
								if( !array_key_exists( $country_id, $countries ) ){
									$countries[$country_id] = array( 'id' => $id, 'slug' => $slug, 'title' => $title );
								}
							}
							
						}
					}
				}
			}
			
			if( $include_city ){
				if( !empty( $cities ) ){
					usort($cities, array(__CLASS__, 'sort_locations' ) );
					$locations[] = $cities;
				}
			}
			
			if( $include_state ){
				if( !empty( $states ) ){
					usort($states, array(__CLASS__, 'sort_locations' ) );
					$locations[] = $states;
				}
			}
			
			if( $include_country ){
				if( !empty( $countries ) ){
					usort($countries, array(__CLASS__, 'sort_locations' ) );
					$locations[] = $countries;
				}
			}
			
			if( !empty( $locations ) ){
				return $locations;
			}
			
			return false;
		}
		
		public static function sort_locations( $a, $b ){
			 return strcasecmp($a['title'], $b['title']);
		}
		
		public static function get_country( $id ){
			$args = array(
				'p'					=>	$id,
				'post_type'			=>	array( 'utalii_country' ),
				'post_status'		=>	array( 'publish' ),
				'pagination'		=>	false,
				'posts_per_page'	=>	'-1',
				'order'				=>	'ASC',
				'orderby'			=>	'title',
				'cache_results'		=>	false,
			);
			
			if( !empty( $post ) && ( 1 == count($post) ) ){
				$p = $post[0];
				$post = array( 'id' => $p->ID, 'slug' => $p->post_name, 'title' => $p->post_title );
				return $post;
			}
			return false;
		}
		
		public static function current_url(){
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
			return $protocol . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}
		
		public static function print_r( $var, $return = false ){
			$html	=	'';
			$html	.=	'<div style="border: 2px solid #00f; width: 450px; overflow-x: scroll; margin: 5px; padding: 5px;">';
			$html	.=		'<pre>';
			$html	.=		print_r( $var, true );
			$html	.=		'</pre>';
			$html	.=	'</div>';
			
			if( $return ){
				return $html;
			}
			
			echo $html;
		}
		
		public static function is_published( $id, $type = null ){
			$return = false;
			$p = get_post( $id );
			
			if( $p ){
				if( $p->post_status == 'publish' ){
					$return = true;
					if( !is_null( $type ) ){
						if( $p->post_type != $type ){
							$return = false;
						}
					}
				}
			}
			
			return $return;
		}
		
		public static function hotel_search_page_id(){
			$post_id	=	get_option( 'utalii_hotel_search_page_id', false );
			if( $post_id ){
				if( self::is_published( $post_id ) ){
					return $post_id;
				}
			}
			
			return false;
		}
		
		public static function hotel_search_page_url(){
			$post_id	=	get_option( 'utalii_hotel_search_page_id', false );
			if( $post_id ){
				if( self::is_published( $post_id ) ){
					return get_permalink( $post_id );
				}
			}
			
			return false;
		}
		
		public static function view_hotels_page_id(){
			$post_id	=	get_option( 'utalii_view_hotels_page_id', false );
			if( $post_id ){
				if( self::is_published( $post_id ) ){
					return $post_id;
				}
			}
			
			return false;
		}
		
		public static function view_hotels_page_url(){
			$post_id	=	get_option( 'utalii_view_hotels_page_id', false );
			if( $post_id ){
				if( self::is_published( $post_id ) ){
					return get_permalink( $post_id );
				}
			}
			
			return false;
		}
		
		public static function view_rooms_page_id(){
			$post_id	=	get_option( 'utalii_view_rooms_page_id', false );
			if( $post_id ){
				if( self::is_published( $post_id ) ){
					return $post_id;
				}
			}
			
			return false;
		}
		
		public static function view_rooms_page_url(){
			$post_id	=	get_option( 'utalii_view_rooms_page_id', false );
			if( $post_id ){
				if( self::is_published( $post_id ) ){
					return get_permalink( $post_id );
				}
			}
			
			return false;
		}
		
		public static function room_cart_page_id(){
			$post_id	=	get_option( 'utalii_room_cart_page_id', false );
			if( $post_id ){
				if( self::is_published( $post_id ) ){
					return $post_id;
				}
			}
			
			return false;
		}
		
		public static function room_cart_page_url(){
			$post_id	=	get_option( 'utalii_room_cart_page_id', false );
			if( $post_id ){
				if( self::is_published( $post_id ) ){
					return get_permalink( $post_id );
				}
			}
			
			return false;
		}
		
		public static function is_date( $date ) {
			$d = DateTime::createFromFormat('Y-m-d', $date);
			return $d && $d->format('Y-m-d') == $date;
		}
		
	} /* end of class */
}