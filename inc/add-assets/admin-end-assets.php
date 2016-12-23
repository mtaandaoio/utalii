<?php
add_action( 'admin_enqueue_scripts', 'utalii_load_loations_js' );
function utalii_load_loations_js(){
	$handle		=	UTALII_PREFIX . 'load_loations_js';
	$src		=	UTALII_PLUGIN_URL . '/assets/js/load-locations.js';
	$deps		=	array( 'jquery' );
	$ver		=	false;
	$in_footer	=	false;
	
	wp_register_script( $handle, $src, $deps, $ver, $in_footer );
	
	$obj	=	$handle . '_obj';
	$props	=	array(
					'admin_ajax_url'	=>	admin_url( 'admin-ajax.php' ),
					'mkp'				=>	UTALII_MKP,
				);
	
	wp_localize_script( $handle, $obj, $props );
	
	wp_enqueue_script( $handle );
}

add_action( 'wp_enqueue_scripts', 'utalii_hotel_rooms_gallery_admin_js' );
function utalii_hotel_rooms_gallery_admin_js()
{
	/* localize js, start */
	$admin_ajax_url = admin_url( 'admin-ajax.php' );
	$handle		=	'utalii_hotel_rooms_gallery_js';
	$src		=	UTALII_PLUGIN_URL . '/assets/js/rooms-image-gallery.js';
	$deps		=	array('jquery');
	$ver		=	'll';
	$in_footer	=	false;
	wp_register_script( $handle, $src, $deps, $ver, $in_footer );
	/* localize js, start */
	$utalii_hotel_rooms_gallery_props	=	array(
											'admin_ajax_url'			=>	$admin_ajax_url,
										);
	/* localize js, end */
	wp_localize_script( $handle, 'utalii_hotel_rooms_gallery_obj', $utalii_hotel_rooms_gallery_props );
	wp_enqueue_script( $handle );
}

add_action( 'admin_enqueue_scripts', 'utalii_admin_end_css' );
function utalii_admin_end_css(){
	
	$handle		=	'utalii_admin_end_css';
	$url		=	UTALII_PLUGIN_URL . '/assets/css/admin-end-style.css';
	$deps		=	array();	
	wp_register_style( $handle, $url, $deps );	
	wp_enqueue_style( $handle );
	
	$handle		=	'utalii_admin_rooms_gallery_end_css';
	$url		=	UTALII_PLUGIN_URL . '/assets/css/rooms-image-gallery.css';
	$deps		=	array();	
	wp_register_style( $handle, $url, $deps );
	wp_enqueue_style( $handle );
}