<?php
/*
  Plugin Name: Utalii Hotel Reservation System
  Plugin URI: http://mtaandao.co.ke/
  Description: An Online Hotel Room Search and Reservation System
  Version: 16.12.0
  Author: Mtaandao
  Author URI: http://mtaandao.co.ke/
  Text Domain: utalii
 */

if(!defined('ABSPATH')) {exit;}

require_once 'define.php';

if( !class_exists( 'UTALII_Init' ) ){
	class UTALII_Init{
		private static $_instance = null;
		
		public static function get_instance(){
			if( is_null( self::$_instance ) ){
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		
		public function __construct(){
			if(!session_id()) {
				session_start();
			}
			register_activation_hook( __FILE__, array( get_class($this), 'on_plugin_activate' ) );
			register_deactivation_hook( __FILE__, array( get_class($this), 'on_plugin_deactivate' ) );
			
			add_action('admin_menu', array( $this, 'main_menu_reg' ) );
			require_once 'inc/autoloader.php';
		}
		
		function on_plugin_activate() {
			do_action( 'utalii_plugin_activate' );
		}
		
		function on_plugin_deactivate() {
			//update_option( 'utalii_plugin_activated', 'no' );
		}
		
		function main_menu_reg(){
			$page_title		=	'UTALII Admin Area';
			$menu_title		=	'UTALII';
			$capability		=	'manage_options';
			$plugin_slug	=	'utalii';
			$function		=	array( $this, 'main_menu' );
			$icon_url		=	'dashicons-store';
			//$position		=	'';
			 
			add_menu_page(
				$page_title,
				$menu_title,
				$capability,
				$plugin_slug,
				$function,
				$icon_url
			);
		}
		
		function main_menu(){}
		
		
	}/* end of class */
}

if( !function_exists( 'utalii_init_start') ){
	function utalii_init_start(){
		return UTALII_Init::get_instance();
	}
}
utalii_init_start();