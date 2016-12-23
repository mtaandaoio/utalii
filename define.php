<?php
//if( function_exists( 'add_action' ) ){
	if(true){
	
	define(	'UTALII_PLUGIN_NAME',					'Utalii Hotel Reservation System'														);
	define(	'UTALII_PLUGIN_URI',					'http://mtaandao.co.ke/'														);
	define(	'UTALII_PLUGIN_DESCRIPTION',			'Simple Hotel and Room reservation system'											);
	define(	'UTALII_PLUGIN_VERSION',				'16.12.0'																			);
	define(	'UTALII_PLUGIN_AUTHOR',				'Mtaandao'																	);
	define(	'UTALII_PLUGIN_AUTHOR_URI',			'http://mtaandao.co.ke/'														);
	define(	'UTALII_TEXT_DOMAIN',					'utalii'																			);
    
	define(	'UTALII_PREFIX',						'utalii_'																			);
	define(	'UTALII_MKP',							'_utalii_'																		); /* meta key prefix */
	define(	'UTALII_PLUGIN_PATH',					untrailingslashit( str_replace('\\', '/', plugin_dir_path( __FILE__ ) ) )		);
	define(	'UTALII_PLUGIN_URL',					untrailingslashit( str_replace('\\', '/', plugin_dir_url( __FILE__ ) ) )		);
	define(	'UTALII_CURRENT_THEME_PATH',			untrailingslashit( str_replace('\\', '/', get_template_directory() ) )			);
	define(	'UTALII_CURRENT_THEME_URL',			untrailingslashit( str_replace('\\', '/', get_stylesheet_directory_uri() ) )	);
	define(	'UTALII_CURRENT_CHILD_THEME_PATH',	untrailingslashit( str_replace('\\', '/', get_stylesheet_directory() ) )		);
	define(	'UTALII_CURRENT_CHILD_THEME_URL',		untrailingslashit( str_replace('\\', '/', get_stylesheet_directory_uri() ) )	);
	define(	'UTALII_TEMPLATEPATH',				untrailingslashit( str_replace('\\', '/', UTALII_PLUGIN_PATH . '/templates' ) ) . '/'	);
	define(	'UTALII_OVERRIDE_TEMPLATEPATH',		'utalii-tpl/' );
	
	/* wordpress plugins dir path */
	if( '' != UTALII_PLUGIN_PATH ){
		$del = 'wp-content/plugins';
		$str = explode( $del, UTALII_PLUGIN_PATH );
		$str = $str[0] . $del;
		define( 'UTALII_PLUGINS_PATH', $str );
	} else {
		define( 'UTALII_PLUGINS_PATH', '' );
	}
	
	/* wordpress plugins dir url */
	if( '' != UTALII_PLUGIN_URL ){
		$del = 'wp-content/plugins';
		$str = explode( $del, UTALII_PLUGIN_URL );
		$str = $str[0] . $del;
		define( 'UTALII_PLUGINS_URL', $str );
	} else {
		define( 'UTALII_PLUGINS_URL', '' );
	}
	
	/* wordpress themes dir path */
	if( '' != UTALII_CURRENT_THEME_PATH ){
		$del = 'wp-content/themes';
		$str = explode( $del, UTALII_CURRENT_THEME_PATH );
		$str = $str[0] . $del;
		define( 'UTALII_CURRENT_THEMES_PATH', $str );
	} else {
		define( 'UTALII_CURRENT_THEMES_PATH', '' );
	}
	
	/* wordpress themes dir url */
	if( '' != UTALII_CURRENT_THEME_URL ){
		$del = 'wp-content/themes';
		$str = explode( $del, UTALII_CURRENT_THEME_URL );
		$str = $str[0] . $del;
		define( 'UTALII_CURRENT_THEMES_URL', $str );
	} else {
		define( 'UTALII_CURRENT_THEMES_URL', '' );
	}
}
