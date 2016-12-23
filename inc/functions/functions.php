<?php
if( !function_exists( 'get_option_utalii_general' ) ){
	function get_option_utalii_general( $option_name = null ){
		$option_value = '';
		if( ! is_null( $option_name ) ){
			$default_value = '';
			$general_option_value = get_option( 'general_settings', $default_value );
			if( '' != $general_option_value ){
				$option_value = $general_option_value[$option_name];
			}
		}
		
		return $option_value;
	}
}
add_filter('utalii_setting_tabs', 'yss_settings_tab' );
function yss_settings_tab( $tabs ){
	$tabs['yss_tab'] = 'YSS Tab';
	return $tabs;
}
function utalii_paginate_links($query, $paged){
	$big = 999999999;
	$args	=	array(
					'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format' => '?paged=%#%',
					'current' => max( 1, $paged ),
					'total' => $query->max_num_pages
				);
	return paginate_links( $args ); 
}
function utalii_noimage_url(){
	$no_img_url = UTALII_PLUGIN_URL . '/assets/img/no-image.png';
	return $no_img_url;
}
function utalii_noimage_width(){
	$no_img_url = UTALII_PLUGIN_URL . '/assets/img/no-image.png';
	return 766;
}
function utalii_noimage_height(){
	return 768;
}
function utalii_ajax_loader_url(){
	$ajax_loader_url = UTALII_PLUGIN_URL . '/assets/img/utalii-ajax-loader.gif';
	return $ajax_loader_url;
}
function utalii_get_hotel_detail( $hotel_id ){
	
	$data	=	array(
					'id'					=>	null,
					'title'					=>	null,
					'address'				=>	null,
					'full_address'			=>	null,
					'city_id'				=>	null,
					'city'					=>	null,
					'city_detail'			=>	null,
					'state_id'				=>	null,
					'state'					=>	null,
					'state_detail'			=>	null,
					'country_id'			=>	null,
					'country'				=>	null,
					'country_detail'		=>	null,
					'postal_code'			=>	null,
					'short_description'		=>	null,
					'long_description'		=>	null,
					'featured_image_id'		=>	null,
					'featured_image'		=>	array(
													'thumbnail'	=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
													'medium'	=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
													'full'		=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
												),
					'gallery_images'		=>	null, /* nested array will be same as featured_image above has */
					'images'				=>	null,
					'post'					=>	null,
				);
	
	$data['id'] = $hotel_id;
	$data['post'] = get_post( $data['id'] );
	$data['title'] = get_the_title( $data['id'] );
	
	$address = get_post_meta( $data['id'], UTALII_MKP . 'address', true );
	$address = trim( $address );
	
	if( !empty( $address ) ){
		$data['address'] = $address;
	}
	
	$city_id = get_post_meta( $data['id'], UTALII_MKP . 'city_id', true );
	$city_id = trim( $city_id );
	
	if( !empty( $city_id ) ){
		$data['city_id'] = $city_id;
		$data['city_detail'] = utalii_get_city_detail( $city_id );
	}
	
	$city = get_post_meta( $data['id'], UTALII_MKP . 'city', true );
	$city = trim( $city );
	if( !empty( $city ) ){
		$data['city'] = $city;
	}
	
	
	$state_id = get_post_meta( $data['id'], UTALII_MKP . 'state_id', true );
	$state_id = trim( $state_id );
	if( !empty( $state_id ) ){
		$data['state_id'] = $state_id;
		$data['state_detail'] = utalii_get_state_detail( $state_id );
	}
	
	$state = get_post_meta( $data['id'], UTALII_MKP . 'state', true );
	$state_id = trim( $state );
	if( !empty( $state ) ){
		$data['state'] = $state;
	}
	
	$country_id = get_post_meta( $data['id'], UTALII_MKP . 'country_id', true );
	$country_id = trim( $country_id );
	if( !empty( $country_id ) ){
		$data['country_id'] = $country_id;
		$data['country_detail'] = utalii_get_country_detail( $country_id );
	}
	
	$country = get_post_meta( $data['id'], UTALII_MKP . 'country', true );
	$country = trim( $country );
	if( !empty( $country ) ){
		$data['country'] = $country;
	}
	
	$postal_code = get_post_meta( $data['id'], UTALII_MKP . 'postal_code', true );
	$postal_code = trim( $postal_code );
	if( !empty( $postal_code ) ){
		$data['postal_code'] = $postal_code;
	}
	
	/* address string */
	$address = "";
	if( !is_null( $data['address'] ) ){
		$address .= $data['address'];
	}
	
	if( !is_null( $data['city'] ) ){
		$address .= ", " . $data['city'];
	}
	
	if( !is_null( $data['postal_code'] ) ){
		$address .= " - " . $data['postal_code'];
	}
	
	if( !is_null( $data['state'] ) ){
		$address .= ", " . $data['state'];
	}
	
	if( !is_null( $data['country'] ) ){
		$address .= ", " . $data['country'];
	}
	$data['full_address'] = $address;
	
	$short_description	=	$data['post']->post_excerpt;
	if( !empty( $short_description ) ){
		$data['short_description']	=	htmlspecialchars_decode( $short_description );
	}
	
	$long_description	=	$data['post']->post_content;
	if( !empty( $long_description ) ){
		$data['long_description']	=	htmlspecialchars_decode( $long_description );
	}
	
	$featured_image_id = get_post_thumbnail_id( $data['id'] );
	if( !empty( $featured_image_id ) ){
		$data['featured_image_id'] = $featured_image_id;
		
		$featured_image_thumbnail = wp_get_attachment_image_src( $featured_image_id, 'thumbnail' );
		if( $featured_image_thumbnail ){
			$data['featured_image']['thumbnail']['url']			=	$featured_image_thumbnail[0];
			$data['featured_image']['thumbnail']['width']		=	$featured_image_thumbnail[1];
			$data['featured_image']['thumbnail']['height']		=	$featured_image_thumbnail[2];
			$data['featured_image']['thumbnail']['is_resized']	=	$featured_image_thumbnail[3];
		}
		
		$featured_image_medium = wp_get_attachment_image_src( $featured_image_id, 'medium' );
		if( $featured_image_medium ){
			$data['featured_image']['medium']['url']		=	$featured_image_medium[0];
			$data['featured_image']['medium']['width']		=	$featured_image_medium[1];
			$data['featured_image']['medium']['height']		=	$featured_image_medium[2];
			$data['featured_image']['medium']['is_resized']	=	$featured_image_medium[3];
		}
		
		$featured_image_full = wp_get_attachment_image_src( $featured_image_id, 'full' );
		if( $featured_image_full ){
			$data['featured_image']['full']['url']			=	$featured_image_full[0];
			$data['featured_image']['full']['width']		=	$featured_image_full[1];
			$data['featured_image']['full']['height']		=	$featured_image_full[2];
			$data['featured_image']['full']['is_resized']	=	$featured_image_full[3];
		}
		
		$data['images'][$featured_image_id] = $data['featured_image'];
	}
	$gallery_images = get_post_meta( $data['id'], '_gimb_gallery_images', true );
	$gallery_images = trim( $gallery_images );
	if( !empty($gallery_images) ){
		$gallery_images = explode(",", $gallery_images );
		if( is_array($gallery_images) && count($gallery_images) ){
			foreach( $gallery_images as $gi ){
				$gi_arr = array(); /* temp var */
				$gi_thumbnail = wp_get_attachment_image_src( $gi, 'thumbnail' );
				if( $gi_thumbnail ){
					$gi_arr['thumbnail']['url']			=	$gi_thumbnail[0];
					$gi_arr['thumbnail']['width']		=	$gi_thumbnail[1];
					$gi_arr['thumbnail']['height']		=	$gi_thumbnail[2];
					$gi_arr['thumbnail']['is_resized']	=	$gi_thumbnail[3];
				}
				
				$gi_medium = wp_get_attachment_image_src( $gi, 'medium' );
				if( $gi_medium ){
					$gi_arr['medium']['url']		=	$gi_medium[0];
					$gi_arr['medium']['width']		=	$gi_medium[1];
					$gi_arr['medium']['height']		=	$gi_medium[2];
					$gi_arr['medium']['is_resized']	=	$gi_medium[3];
				}
				
				$gi_full = wp_get_attachment_image_src( $gi, 'full' );
				if( $gi_full ){
					$gi_arr['full']['url']			=	$gi_full[0];
					$gi_arr['full']['width']		=	$gi_full[1];
					$gi_arr['full']['height']		=	$gi_full[2];
					$gi_arr['full']['is_resized']	=	$gi_full[3];
				}
				
				$data['gallery_images'][$gi] = $gi_arr;
				$data['images'][$gi] = $gi_arr;
			}
		}
	}
	
	if( empty( $data['images'] ) ){
		$data['images'][0] = $data['featured_image'];
	}
	
	return $data;
}
function utalii_get_room_detail( $room_id ){
	
	$data	=	array(
					'id'					=>	null,
					'title'					=>	null,
					'hotel_id'				=>  null,
					'hotel'					=>  null,
					'charge_per_night'		=>  null,
					'max_adult_occupancy'	=>  null,
					'max_children_occupancy'=>  null,
					'total_rooms'			=>  null,
					'short_description'		=>	null,
					'long_description'		=>	null,
					'featured_image_id'		=>	null,
					'featured_image'		=>	array(
													'thumbnail'	=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
													'medium'	=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
													'full'		=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
												),
					'gallery_images'		=>	null, /* nested array will be same as featured_image above has */
					'images'				=>	null,
					'post'					=>	null,
				);
	
	$data['id'] = $room_id;
	$data['post'] = get_post( $data['id'] );
	$data['title'] = get_the_title( $data['id'] );
	
	$hotel_id = get_post_meta( $data['id'], UTALII_MKP . 'hotel_id', true );
	$hotel_id = trim( $hotel_id );
	if( !empty( $hotel_id ) ){
		$data['hotel_id'] = $hotel_id;
	}
	
	$hotel = get_post_meta( $data['id'], UTALII_MKP . 'hotel', true );
	$hotel = trim( $hotel );
	if( !empty( $hotel ) ){
		$data['hotel'] = $hotel;
	}
	
	$charge_per_night = get_post_meta( $data['id'], UTALII_MKP . 'charge_per_night', true );
	$charge_per_night = trim( $charge_per_night );
	if( is_numeric( $charge_per_night ) ){
		$data['charge_per_night'] = $charge_per_night;
	}
	
	$max_adult_occupancy = get_post_meta( $data['id'], UTALII_MKP . 'max_adult_occupancy', true );
	$max_adult_occupancy = trim( $max_adult_occupancy );
	if( is_numeric( $max_adult_occupancy ) ){
		$data['max_adult_occupancy'] = $max_adult_occupancy;
	}
	
	$max_children_occupancy = get_post_meta( $data['id'], UTALII_MKP . 'max_children_occupancy', true );
	$max_children_occupancy = trim( $max_children_occupancy );
	if( is_numeric( $max_children_occupancy ) ){
		$data['max_children_occupancy'] = $max_children_occupancy;
	}
	
	$total_rooms = get_post_meta( $data['id'], UTALII_MKP . 'total_rooms', true );
	$total_rooms = trim( $total_rooms );
	if( is_numeric( $total_rooms ) ){
		$data['total_rooms'] = $total_rooms;
	}
	
	$short_description	=	$data['post']->post_excerpt;
	if( !empty( $short_description ) ){
		$data['short_description']	=	htmlspecialchars_decode( $short_description );
	}
	
	$long_description	=	$data['post']->post_content;
	if( !empty( $long_description ) ){
		$data['long_description']	=	htmlspecialchars_decode( $long_description );
	}
	
	$featured_image_id = get_post_thumbnail_id( $data['id'] );
	if( !empty( $featured_image_id ) ){
		$data['featured_image_id'] = $featured_image_id;
		
		$featured_image_thumbnail = wp_get_attachment_image_src( $featured_image_id, 'thumbnail' );
		if( $featured_image_thumbnail ){
			$data['featured_image']['thumbnail']['url']			=	$featured_image_thumbnail[0];
			$data['featured_image']['thumbnail']['width']		=	$featured_image_thumbnail[1];
			$data['featured_image']['thumbnail']['height']		=	$featured_image_thumbnail[2];
			$data['featured_image']['thumbnail']['is_resized']	=	$featured_image_thumbnail[3];
		}
		
		$featured_image_medium = wp_get_attachment_image_src( $featured_image_id, 'medium' );
		if( $featured_image_medium ){
			$data['featured_image']['medium']['url']		=	$featured_image_medium[0];
			$data['featured_image']['medium']['width']		=	$featured_image_medium[1];
			$data['featured_image']['medium']['height']		=	$featured_image_medium[2];
			$data['featured_image']['medium']['is_resized']	=	$featured_image_medium[3];
		}
		
		$featured_image_full = wp_get_attachment_image_src( $featured_image_id, 'full' );
		if( $featured_image_full ){
			$data['featured_image']['full']['url']			=	$featured_image_full[0];
			$data['featured_image']['full']['width']		=	$featured_image_full[1];
			$data['featured_image']['full']['height']		=	$featured_image_full[2];
			$data['featured_image']['full']['is_resized']	=	$featured_image_full[3];
		}
		
		$data['images'][$featured_image_id] = $data['featured_image'];
	}
	$gallery_images = get_post_meta( $data['id'], '_gimb_gallery_images', true );
	$gallery_images = trim( $gallery_images );
	if( !empty($gallery_images) ){
		$gallery_images = explode(",", $gallery_images );
		if( is_array($gallery_images) && count($gallery_images) ){
			foreach( $gallery_images as $gi ){
				$gi_arr = array(); /* temp var */
				$gi_thumbnail = wp_get_attachment_image_src( $gi, 'thumbnail' );
				if( $gi_thumbnail ){
					$gi_arr['thumbnail']['url']			=	$gi_thumbnail[0];
					$gi_arr['thumbnail']['width']		=	$gi_thumbnail[1];
					$gi_arr['thumbnail']['height']		=	$gi_thumbnail[2];
					$gi_arr['thumbnail']['is_resized']	=	$gi_thumbnail[3];
				}
				
				$gi_medium = wp_get_attachment_image_src( $gi, 'medium' );
				if( $gi_medium ){
					$gi_arr['medium']['url']		=	$gi_medium[0];
					$gi_arr['medium']['width']		=	$gi_medium[1];
					$gi_arr['medium']['height']		=	$gi_medium[2];
					$gi_arr['medium']['is_resized']	=	$gi_medium[3];
				}
				
				$gi_full = wp_get_attachment_image_src( $gi, 'full' );
				if( $gi_full ){
					$gi_arr['full']['url']			=	$gi_full[0];
					$gi_arr['full']['width']		=	$gi_full[1];
					$gi_arr['full']['height']		=	$gi_full[2];
					$gi_arr['full']['is_resized']	=	$gi_full[3];
				}
				
				$data['gallery_images'][$gi] = $gi_arr;
				$data['images'][$gi] = $gi_arr;
			}
		}
	}
	if( empty( $data['images'] ) ){
		$data['images'][0] = $data['featured_image'];
	}
	
	return $data;
}
function utalii_get_city_detail( $city_id ){
	$data	=	array(
					'id'					=>	null,
					'title'					=>	null,
					'state_id'				=>  null,
					'state'					=>  null,
					'country_id'			=>  null,
					'country'				=>  null,
					'long_description'		=>	null,
					'featured_image_id'		=>	null,
					'featured_image'		=>	array(
													'thumbnail'	=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
													'medium'	=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
													'full'		=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
												),
					'gallery_images'		=>	null, /* nested array will be same as featured_image above has */
					'images'				=>	null,
					'post'					=>	null,
				);
	
	$data['id'] = $city_id;
	$data['post'] = get_post( $data['id'] );
	$data['title'] = get_the_title( $data['id'] );
	
	$state_id = get_post_meta( $data['id'], UTALII_MKP . 'state_id', true );
	$state_id = trim( $state_id );
	if( !empty( $state_id ) ){
		$data['state_id'] = $state_id;
	}
	
	$state = get_post_meta( $data['id'], UTALII_MKP . 'state', true );
	$state = trim( $state );
	if( !empty( $state ) ){
		$data['state'] = $state;
	}
	
	$country_id = get_post_meta( $data['id'], UTALII_MKP . 'country_id', true );
	$country_id = trim( $country_id );
	if( !empty( $country_id ) ){
		$data['country_id'] = $country_id;
	}
	
	$country = get_post_meta( $data['id'], UTALII_MKP . 'country', true );
	$country = trim( $country );
	if( !empty( $country ) ){
		$data['country'] = $country;
	}
	
	$long_description	=	$data['post']->post_content;
	if( !empty( $long_description ) ){
		$data['long_description']	=	htmlspecialchars_decode( $long_description );
	}
	
	$featured_image_id = get_post_thumbnail_id( $data['id'] );
	if( !empty( $featured_image_id ) ){
		$data['featured_image_id'] = $featured_image_id;
		
		$featured_image_thumbnail = wp_get_attachment_image_src( $featured_image_id, 'thumbnail' );
		if( $featured_image_thumbnail ){
			$data['featured_image']['thumbnail']['url']			=	$featured_image_thumbnail[0];
			$data['featured_image']['thumbnail']['width']		=	$featured_image_thumbnail[1];
			$data['featured_image']['thumbnail']['height']		=	$featured_image_thumbnail[2];
			$data['featured_image']['thumbnail']['is_resized']	=	$featured_image_thumbnail[3];
		}
		
		$featured_image_medium = wp_get_attachment_image_src( $featured_image_id, 'medium' );
		if( $featured_image_medium ){
			$data['featured_image']['medium']['url']		=	$featured_image_medium[0];
			$data['featured_image']['medium']['width']		=	$featured_image_medium[1];
			$data['featured_image']['medium']['height']		=	$featured_image_medium[2];
			$data['featured_image']['medium']['is_resized']	=	$featured_image_medium[3];
		}
		
		$featured_image_full = wp_get_attachment_image_src( $featured_image_id, 'full' );
		if( $featured_image_full ){
			$data['featured_image']['full']['url']			=	$featured_image_full[0];
			$data['featured_image']['full']['width']		=	$featured_image_full[1];
			$data['featured_image']['full']['height']		=	$featured_image_full[2];
			$data['featured_image']['full']['is_resized']	=	$featured_image_full[3];
		}
		
		$data['images'][$featured_image_id] = $data['featured_image'];
	}
	
	$gallery_images = get_post_meta( $data['id'], '_gimb_gallery_images', true );
	$gallery_images = trim( $gallery_images );
	if( !empty($gallery_images) ){
		$gallery_images = explode(",", $gallery_images );
		if( is_array($gallery_images) && count($gallery_images) ){
			foreach( $gallery_images as $gi ){
				$gi_arr = array(); /* temp var */
				$gi_thumbnail = wp_get_attachment_image_src( $gi, 'thumbnail' );
				if( $gi_thumbnail ){
					$gi_arr['thumbnail']['url']			=	$gi_thumbnail[0];
					$gi_arr['thumbnail']['width']		=	$gi_thumbnail[1];
					$gi_arr['thumbnail']['height']		=	$gi_thumbnail[2];
					$gi_arr['thumbnail']['is_resized']	=	$gi_thumbnail[3];
				}
				
				$gi_medium = wp_get_attachment_image_src( $gi, 'medium' );
				if( $gi_medium ){
					$gi_arr['medium']['url']		=	$gi_medium[0];
					$gi_arr['medium']['width']		=	$gi_medium[1];
					$gi_arr['medium']['height']		=	$gi_medium[2];
					$gi_arr['medium']['is_resized']	=	$gi_medium[3];
				}
				
				$gi_full = wp_get_attachment_image_src( $gi, 'full' );
				if( $gi_full ){
					$gi_arr['full']['url']			=	$gi_full[0];
					$gi_arr['full']['width']		=	$gi_full[1];
					$gi_arr['full']['height']		=	$gi_full[2];
					$gi_arr['full']['is_resized']	=	$gi_full[3];
				}
				
				$data['gallery_images'][$gi] = $gi_arr;
				$data['images'][$gi] = $gi_arr;
			}
		}
	}
	
	return $data;
}
function utalii_get_state_detail( $state_id ){
	$data	=	array(
					'id'					=>	null,
					'title'					=>	null,
					'country_id'			=>  null,
					'country'				=>  null,
					'long_description'		=>	null,
					'featured_image_id'		=>	null,
					'featured_image'		=>	array(
													'thumbnail'	=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
													'medium'	=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
													'full'		=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
												),
					'gallery_images'		=>	null, /* nested array will be same as featured_image above has */
					'images'				=>	null,
					'post'					=>	null,
				);
	
	$data['id'] = $state_id;
	$data['post'] = get_post( $data['id'] );
	$data['title'] = get_the_title( $data['id'] );
	
	$country_id = get_post_meta( $data['id'], UTALII_MKP . 'country_id', true );
	$country_id = trim( $country_id );
	if( !empty( $country_id ) ){
		$data['country_id'] = $country_id;
	}
	
	$country = get_post_meta( $data['id'], UTALII_MKP . 'country', true );
	$country = trim( $country );
	if( !empty( $country ) ){
		$data['country'] = $country;
	}
	
	$long_description	=	$data['post']->post_content;
	if( !empty( $long_description ) ){
		$data['long_description']	=	htmlspecialchars_decode( $long_description );
	}
	
	$featured_image_id = get_post_thumbnail_id( $data['id'] );
	if( !empty( $featured_image_id ) ){
		$data['featured_image_id'] = $featured_image_id;
		
		$featured_image_thumbnail = wp_get_attachment_image_src( $featured_image_id, 'thumbnail' );
		if( $featured_image_thumbnail ){
			$data['featured_image']['thumbnail']['url']			=	$featured_image_thumbnail[0];
			$data['featured_image']['thumbnail']['width']		=	$featured_image_thumbnail[1];
			$data['featured_image']['thumbnail']['height']		=	$featured_image_thumbnail[2];
			$data['featured_image']['thumbnail']['is_resized']	=	$featured_image_thumbnail[3];
		}
		
		$featured_image_medium = wp_get_attachment_image_src( $featured_image_id, 'medium' );
		if( $featured_image_medium ){
			$data['featured_image']['medium']['url']		=	$featured_image_medium[0];
			$data['featured_image']['medium']['width']		=	$featured_image_medium[1];
			$data['featured_image']['medium']['height']		=	$featured_image_medium[2];
			$data['featured_image']['medium']['is_resized']	=	$featured_image_medium[3];
		}
		
		$featured_image_full = wp_get_attachment_image_src( $featured_image_id, 'full' );
		if( $featured_image_full ){
			$data['featured_image']['full']['url']			=	$featured_image_full[0];
			$data['featured_image']['full']['width']		=	$featured_image_full[1];
			$data['featured_image']['full']['height']		=	$featured_image_full[2];
			$data['featured_image']['full']['is_resized']	=	$featured_image_full[3];
		}
		
		$data['images'][$featured_image_id] = $data['featured_image'];
	}
	
	$gallery_images = get_post_meta( $data['id'], '_gimb_gallery_images', true );
	$gallery_images = trim( $gallery_images );
	if( !empty($gallery_images) ){
		$gallery_images = explode(",", $gallery_images );
		if( is_array($gallery_images) && count($gallery_images) ){
			foreach( $gallery_images as $gi ){
				$gi_arr = array(); /* temp var */
				$gi_thumbnail = wp_get_attachment_image_src( $gi, 'thumbnail' );
				if( $gi_thumbnail ){
					$gi_arr['thumbnail']['url']			=	$gi_thumbnail[0];
					$gi_arr['thumbnail']['width']		=	$gi_thumbnail[1];
					$gi_arr['thumbnail']['height']		=	$gi_thumbnail[2];
					$gi_arr['thumbnail']['is_resized']	=	$gi_thumbnail[3];
				}
				
				$gi_medium = wp_get_attachment_image_src( $gi, 'medium' );
				if( $gi_medium ){
					$gi_arr['medium']['url']		=	$gi_medium[0];
					$gi_arr['medium']['width']		=	$gi_medium[1];
					$gi_arr['medium']['height']		=	$gi_medium[2];
					$gi_arr['medium']['is_resized']	=	$gi_medium[3];
				}
				
				$gi_full = wp_get_attachment_image_src( $gi, 'full' );
				if( $gi_full ){
					$gi_arr['full']['url']			=	$gi_full[0];
					$gi_arr['full']['width']		=	$gi_full[1];
					$gi_arr['full']['height']		=	$gi_full[2];
					$gi_arr['full']['is_resized']	=	$gi_full[3];
				}
				
				$data['gallery_images'][$gi] = $gi_arr;
				$data['images'][$gi] = $gi_arr;
			}
		}
	}
	
	return $data;
}
function utalii_get_country_detail( $country_id ){
	$data	=	array(
					'id'					=>	null,
					'title'					=>	null,
					'long_description'		=>	null,
					'featured_image_id'		=>	null,
					'featured_image'		=>	array(
													'thumbnail'	=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
													'medium'	=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
													'full'		=>	array(
																		'url'			=>	utalii_noimage_url(),
																		'width'			=>	utalii_noimage_width(),
																		'height'		=>	utalii_noimage_height(),
																		'is_resized'	=>	null
																	),
												),
					'gallery_images'		=>	null, /* nested array will be same as featured_image above has */
					'images'				=>	null,
					'post'					=>	null,
				);
	
	$data['id'] = $country_id;
	$data['post'] = get_post( $data['id'] );
	$data['title'] = get_the_title( $data['id'] );
	
	$long_description	=	$data['post']->post_content;
	if( !empty( $long_description ) ){
		$data['long_description']	=	htmlspecialchars_decode( $long_description );
	}
	
	$featured_image_id = get_post_thumbnail_id( $data['id'] );
	if( !empty( $featured_image_id ) ){
		$data['featured_image_id'] = $featured_image_id;
		
		$featured_image_thumbnail = wp_get_attachment_image_src( $featured_image_id, 'thumbnail' );
		if( $featured_image_thumbnail ){
			$data['featured_image']['thumbnail']['url']			=	$featured_image_thumbnail[0];
			$data['featured_image']['thumbnail']['width']		=	$featured_image_thumbnail[1];
			$data['featured_image']['thumbnail']['height']		=	$featured_image_thumbnail[2];
			$data['featured_image']['thumbnail']['is_resized']	=	$featured_image_thumbnail[3];
		}
		
		$featured_image_medium = wp_get_attachment_image_src( $featured_image_id, 'medium' );
		if( $featured_image_medium ){
			$data['featured_image']['medium']['url']		=	$featured_image_medium[0];
			$data['featured_image']['medium']['width']		=	$featured_image_medium[1];
			$data['featured_image']['medium']['height']		=	$featured_image_medium[2];
			$data['featured_image']['medium']['is_resized']	=	$featured_image_medium[3];
		}
		
		$featured_image_full = wp_get_attachment_image_src( $featured_image_id, 'full' );
		if( $featured_image_full ){
			$data['featured_image']['full']['url']			=	$featured_image_full[0];
			$data['featured_image']['full']['width']		=	$featured_image_full[1];
			$data['featured_image']['full']['height']		=	$featured_image_full[2];
			$data['featured_image']['full']['is_resized']	=	$featured_image_full[3];
		}
		
		$data['images'][$featured_image_id] = $data['featured_image'];
	}
	
	$gallery_images = get_post_meta( $data['id'], '_gimb_gallery_images', true );
	$gallery_images = trim( $gallery_images );
	if( !empty($gallery_images) ){
		$gallery_images = explode(",", $gallery_images );
		if( is_array($gallery_images) && count($gallery_images) ){
			foreach( $gallery_images as $gi ){
				$gi_arr = array(); /* temp var */
				$gi_thumbnail = wp_get_attachment_image_src( $gi, 'thumbnail' );
				if( $gi_thumbnail ){
					$gi_arr['thumbnail']['url']			=	$gi_thumbnail[0];
					$gi_arr['thumbnail']['width']		=	$gi_thumbnail[1];
					$gi_arr['thumbnail']['height']		=	$gi_thumbnail[2];
					$gi_arr['thumbnail']['is_resized']	=	$gi_thumbnail[3];
				}
				
				$gi_medium = wp_get_attachment_image_src( $gi, 'medium' );
				if( $gi_medium ){
					$gi_arr['medium']['url']		=	$gi_medium[0];
					$gi_arr['medium']['width']		=	$gi_medium[1];
					$gi_arr['medium']['height']		=	$gi_medium[2];
					$gi_arr['medium']['is_resized']	=	$gi_medium[3];
				}
				
				$gi_full = wp_get_attachment_image_src( $gi, 'full' );
				if( $gi_full ){
					$gi_arr['full']['url']			=	$gi_full[0];
					$gi_arr['full']['width']		=	$gi_full[1];
					$gi_arr['full']['height']		=	$gi_full[2];
					$gi_arr['full']['is_resized']	=	$gi_full[3];
				}
				
				$data['gallery_images'][$gi] = $gi_arr;
				$data['images'][$gi] = $gi_arr;
			}
		}
	}
	
	return $data;
}
function utalii_get_hotel_full_address( $hotel_id ){
	$address = get_post_meta( $hotel_id, UTALII_MKP . 'address', true );
	$address = trim( $address );
	
	if( !empty( $address ) ){
		$data['address'] = $address;
	}
	
	$city_id = get_post_meta( $hotel_id, UTALII_MKP . 'city_id', true );
	$city_id = trim( $city_id );
	
	if( !empty( $city_id ) ){
		$data['city_id'] = $city_id;
		$data['city_detail'] = utalii_get_city_detail( $city_id );
	}
	
	$city = get_post_meta( $hotel_id, UTALII_MKP . 'city', true );
	$city = trim( $city );
	if( !empty( $city ) ){
		$data['city'] = $city;
	}
	
	
	$state_id = get_post_meta( $hotel_id, UTALII_MKP . 'state_id', true );
	$state_id = trim( $state_id );
	if( !empty( $state_id ) ){
		$data['state_id'] = $state_id;
		$data['state_detail'] = utalii_get_state_detail( $state_id );
	}
	
	$state = get_post_meta( $hotel_id, UTALII_MKP . 'state', true );
	$state_id = trim( $state );
	if( !empty( $state ) ){
		$data['state'] = $state;
	}
	
	$country_id = get_post_meta( $hotel_id, UTALII_MKP . 'country_id', true );
	$country_id = trim( $country_id );
	if( !empty( $country_id ) ){
		$data['country_id'] = $country_id;
		$data['country_detail'] = utalii_get_country_detail( $country_id );
	}
	
	$country = get_post_meta( $hotel_id, UTALII_MKP . 'country', true );
	$country = trim( $country );
	if( !empty( $country ) ){
		$data['country'] = $country;
	}
	
	$postal_code = get_post_meta( $hotel_id, UTALII_MKP . 'postal_code', true );
	$postal_code = trim( $postal_code );
	if( !empty( $postal_code ) ){
		$data['postal_code'] = $postal_code;
	}
	
	/* address string */
	$address = "";
	if( !is_null( $data['address'] ) ){
		$address .= $data['address'];
	}
	
	if( !is_null( $data['city'] ) ){
		$address .= ", " . $data['city'];
	}
	
	if( !is_null( $data['postal_code'] ) ){
		$address .= " - " . $data['postal_code'];
	}
	
	if( !is_null( $data['state'] ) ){
		$address .= ", " . $data['state'];
	}
	
	if( !is_null( $data['country'] ) ){
		$address .= ", " . $data['country'];
	}
	
	$address = trim( $address );
	if( !empty( $address ) ){
		return $address;
	}
	
	return false;
}

function send_mail_via_wp_mail($to, $subject, $message, $from_mail = "")
{
	if(empty($from_mail))
	{
		$site_url_full = site_url();
		$site_url_full = str_replace("https://www.", "", $site_url_full);
		$site_url_full = str_replace("http://www.", "", $site_url_full);
		$site_url_full = str_replace("https://", "", $site_url_full);
		$site_url_full = str_replace("http://", "", $site_url_full);
		$from_mail = "noreply@".$site_url_full;
	}
	
	add_filter( 'wp_mail_content_type', 'utalii_set_html_content_type_func' );
	$headers = 'From: '.$from_mail.' <'.$from_mail.'>' . "\r\n";
	wp_mail( $to, $subject, $message, $headers );
	remove_filter( 'wp_mail_content_type', 'utalii_set_html_content_type_func' );
}

function utalii_set_html_content_type_func() 
{
	return 'text/html';
}

function utalii_money_format( $price ) {
  return '$' . number_format( $price, 2 );
}

function utalii_get_bookings_by_usersearch( $search_str ){
	
	$search_str = trim( $search_str );
	$search_arr = explode( " ", $search_str );
	$t_search_arr = array();
	foreach( $search_arr as $sa ){
		$t_search_arr[] = trim( $sa );
	}
	
	$search_arr = $t_search_arr;
	
	if( !empty( $search_arr ) ){
			
		global $table_prefix;
		$host = DB_HOST;
		$user = DB_USER;
		$pass = DB_PASSWORD;
		$db = DB_NAME;
		$prefix = $table_prefix;
		$keywords = $search_arr;
		
		$con = mysqli_connect( $host, $user, $pass, $db );
		
		$user_tbl = $prefix . 'users';
		$user_meta_tbl = $prefix . 'usermeta';
		$user_ids = array();
		$bookings = array();
		$query = null;
		$where = array();
		
		if( !empty( $keywords ) ){
			foreach( $keywords as $kw ){
				$kw = mysqli_real_escape_string( $con, $kw );
				$where[] = " `user_login` LIKE '%$kw%' ";
				$where[] = " `user_nicename` LIKE '%$kw%' ";
				$where[] = " `user_email` LIKE '%$kw%' ";
				$where[] = " `display_name` LIKE '%$kw%' ";
			}
		}
		if( !empty( $where ) ){
			$where = implode( " OR ", $where );
			$query = "SELECT `ID` FROM `$user_tbl` WHERE " . $where;
			
			$result = mysqli_query( $con, $query );
			if( $result ){
				while( $row = mysqli_fetch_assoc( $result ) ){
					$user_ids[] = $row['ID'];
				}
			}
		}
		
		$where = array();
		if( !empty( $keywords ) ){
			foreach( $keywords as $kw ){
				$kw = mysqli_real_escape_string( $con, $kw );
				$where[] = " ( `meta_key` LIKE 'first_name' AND `meta_value` LIKE '%$kw%' ) ";
				$where[] = " ( `meta_key` LIKE 'last_name' AND `meta_value` LIKE '%$kw%' ) ";
			}
		}
		if( !empty( $where ) ){
			$where = implode( " OR ", $where );
			$query = "SELECT `user_id` FROM `$user_meta_tbl` WHERE " . $where;
			$result = mysqli_query( $con, $query );
			if( $result ){
				while( $row = mysqli_fetch_assoc( $result ) ){
					$user_ids[] = $row['user_id'];
				}
			}
		}
		
		$user_ids = array_unique( $user_ids );
		if( !empty( $user_ids ) ){
			$args	=	array(
							'post_type'			=>	array( 'utalii_bookings' ),
							'post_status'		=>	array( 'publish' ),
							'posts_per_page'	=>	'-1',
							'fields'			=>	'ids',
							'meta_query'		=>	array(
														array(
															'key'		=>	UTALII_MKP . 'user_id',
															'value'		=>	$user_ids,
															'compare'	=>	'IN',
														),
													),
						);
			
			$posts = get_posts( $args );
			if( !is_null( $posts ) && !empty( $posts ) ){
				return $posts;
			}
		}
		
	}
	
	return false;
}

add_filter( 'posts_where', 'utalii_booking_search_where' );
function utalii_booking_search_where( $where ){
	global $pagenow, $wpdb;
	
	if(
		is_admin() &&
		$pagenow == 'edit.php' &&
		$_GET['post_type'] == 'utalii_bookings' &&
		$_GET['s'] != ''
	)
	{
		$pids = utalii_get_bookings_by_usersearch( $_GET['s'] );
		if( $pids ){
			$pids = implode(",", $pids );
			$where .= " OR ID IN ($pids)";
		}
    }
    return $where;
}
