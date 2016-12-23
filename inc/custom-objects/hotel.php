<?php
if( !class_exists('UTALII_Hotel') ){
	class UTALII_Hotel{
		private static $_instance = null;
		private $post_type = null;
		private $post_rewrite_slug = null;
		
		private function __construct(){
			$this->post_type = UTALII_PREFIX . 'hotels';
			$this->post_rewrite_slug = 'hotels';
			
			add_action( 'init', array( $this, 'register_custom_post_type' ), 0 );
			
			$this->add_assets();
			$this->add_meta_boxes();
			$this->save_meta_boxes();
			
			/* custom column, start */
			add_filter('manage_'.$this->post_type.'_posts_columns' , array( $this, 'add_custom_columns' ) );
			add_action( 'manage_'.$this->post_type.'_posts_custom_column' , array( $this, 'custom_column_content' ), 10, 2 );
			add_filter( 'manage_edit-'.$this->post_type.'_sortable_columns', array( $this, 'custom_column_sortable' ) );
			add_action( 'load-edit.php', array( $this, 'edit_custom_post_load' ) );
			/* custom column, start */
		}
		
		public static function get_instance(){
			if( is_null( self::$_instance ) ){
				self::$_instance = new self();
			}
			
			return self::$_instance;
		}
		
		/* register custom post type function, start */
		function register_custom_post_type() {
			$labels = array(
				'name'					=>	_x( 'Hotels', 'Post Type General Name', 'utalii' ),
				'singular_name'			=>	_x( 'Hotel', 'Post Type Singular Name', 'utalii' ),
				'menu_name'				=>	__( 'Hotels', 'utalii' ),
				'name_admin_bar'		=>	__( 'Hotels', 'utalii' ),
				'parent_item_colon'		=>	__( 'Parent Hotel:', 'utalii' ),
				'all_items'				=>	__( 'Hotels', 'utalii' ),
				'add_new_item'			=>	__( 'Add New Hotel', 'utalii' ),
				'add_new'				=>	__( 'Add New Hotel', 'utalii' ),
				'new_item'				=>	__( 'New Hotel', 'utalii' ),
				'edit_item'				=>	__( 'Edit Hotel', 'utalii' ),
				'update_item'			=>	__( 'Update Hotel', 'utalii' ),
				'view_item'				=>	__( 'View Hotel', 'utalii' ),
				'search_items'			=>	__( 'Search Hotel', 'utalii' ),
				'not_found'				=>	__( 'Hotel Not found', 'utalii' ),
				'not_found_in_trash'	=>	__( 'Hotel Not found in Trash', 'utalii' ),
			);
			
			$rewrite = array(
				'slug'			=>	$this->post_rewrite_slug,
				'with_front'	=>	true,
				'pages'			=>	true,
				'feeds'			=>	true,
			);
			
			$args = array(
				'label'					=>	__( 'Hotels', 'utalii' ),
				'description'			=>	__( 'A Hotel', 'utalii' ),
				'labels'				=>	$labels,
				'supports'				=>	array( 'title', 'thumbnail', 'comments' ),
				'taxonomies'			=>	array(),
				'hierarchical'			=>	false,
				'public'				=>	true,
				'show_ui'				=>	true,
				'show_in_menu'			=>	'utalii',
				'menu_position'			=>	5,
				'show_in_admin_bar'		=>	true,
				'show_in_nav_menus'		=>	true,
				'can_export'			=>	true,
				'has_archive'			=>	'hotels',
				'exclude_from_search'	=>	false,
				'publicly_queryable'	=>	true,
				'rewrite'				=>	$rewrite,
				'capability_type'		=>	'post',
			);
			
			register_post_type( $this->post_type, $args );
		}
		/* register custom post type function, end */
		
		/* adding meta boxes */
		function add_meta_boxes(){
			add_action( 'add_meta_boxes_' . $this->post_type, array( $this, 'register_details' ) );
			$this->add_gallery_meta_box();
		}
		
		/* saving meta boxes */
		function save_meta_boxes(){
			add_action( 'save_post_' . $this->post_type, array( $this, 'save_details' ) );
		}
		
		/* hotel details, start */
		function register_details( $post_type ) {
			add_meta_box(
				UTALII_PREFIX .'hotel_details',
				__( 'Hotel Details', 'utalii' ),
				array( $this, 'render_details' ),
				$this->post_type,
				'advanced',
				'high'
			);
		}
		
		function save_details( $post_id ){
			
			/* validation checks, start */
			if( !wp_verify_nonce( $_POST['prevent_delete_meta_movetotrash'], UTALII_MKP . $post_id ) ) { return; }
			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return;}
			if( defined('DOING_AJAX') && DOING_AJAX ) { return; }
			if( !isset( $_POST[ UTALII_MKP . 'meta_box_nonce' ] ) ) { return; }
			if( !wp_verify_nonce( $_POST[ UTALII_MKP . 'meta_box_nonce' ], UTALII_MKP . 'save_meta_data' ) ) { return; }
			if( !isset( $_POST['post_type'] ) ) { return; }
			if( !( $this->post_type == $_POST['post_type'] ) ) { return; }
			if( !current_user_can( 'edit_post', $post_id ) ) { return; }
			
			/* if required fields is not set or empty, stop tempered front end code, start */
			if( !isset( $_POST[ UTALII_MKP . 'address' ] ) ){ return; }
			if( !isset( $_POST[ UTALII_MKP . 'city' ] ) ){ return; }
			if( !isset( $_POST[ UTALII_MKP . 'country' ] ) ){ return; }
			
			if( isset( $_POST[ UTALII_MKP . 'address' ] ) ){
				$address = trim( $_POST[ UTALII_MKP . 'address' ] );
				if( empty( $address ) ){
					return;
				}
			}
			if( isset( $_POST[ UTALII_MKP . 'city' ] ) ){
				$city = trim( $_POST[ UTALII_MKP . 'city' ] );
				if( empty( $city ) ){
					return;
				}
			}
			if( isset( $_POST[ UTALII_MKP . 'country' ] ) ){
				$country = trim( $_POST[ UTALII_MKP . 'country' ] );
				if( empty( $country ) ){
					return;
				}
			}
			/* if required fields is not set or empty, stop tempered front end code, end */
			/* validation checks, start */
			
			$data = array(
				'address'				=>	'',
				'city_id'				=>	'',
				'city'					=>	'',
				'state_id'				=>	'',
				'state'					=>	'',
				'country_id'			=>	'',
				'country'				=>	'',
				'postal_code'			=>	'',
				'short_description'		=>	'',
				'long_description'		=>	'',
			);
			
			$data['address']			=	get_post_meta( $post_id, UTALII_MKP . 'address', true );
			$data['city_id']			=	get_post_meta( $post_id, UTALII_MKP . 'city_id', true );
			$data['city']				=	get_post_meta( $post_id, UTALII_MKP . 'city', true );
			$data['state_id']			=	get_post_meta( $post_id, UTALII_MKP . 'state_id', true );
			$data['state']				=	get_post_meta( $post_id, UTALII_MKP . 'state', true );
			$data['country_id']			=	get_post_meta( $post_id, UTALII_MKP . 'country_id', true );
			$data['country']			=	get_post_meta( $post_id, UTALII_MKP . 'country', true );
			$data['postal_code']		=	get_post_meta( $post_id, UTALII_MKP . 'postal_code', true );
			
			
			if ( isset( $_POST[ UTALII_MKP . 'address' ] ) ) {
				$address = sanitize_text_field( $_POST[ UTALII_MKP . 'address' ] );
				$data['address'] = $address;
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'city' ] ) ) {
				$city_id = sanitize_text_field( $_POST[ UTALII_MKP . 'city' ] );
				
				if( !empty( $city_id ) ){
					$data['city_id'] = $city_id;
					
					$data['city'] = UTALII_Location::get_city( $city_id );
					$data['city'] = $data['city']['title'];
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'state' ] ) ) {
				$state_id = sanitize_text_field( $_POST[ UTALII_MKP . 'state' ] );
				
				/* state is n/a to some regions
				 * so if it is empty then we will assume user intentionally make it blank
				 * and we will set it to empty value
				 * otherwise update new value
				 */
				if( !empty( $state_id ) ){
					$data['state_id'] = $state_id;
					
					$data['state'] = UTALII_Location::get_state( $state_id );
					$data['state'] = $data['state']['title'];
				} else {
					$data['state_id'] = '';
					$data['state'] = '';
				}
			}
			if ( isset( $_POST[ UTALII_MKP . 'country' ] ) ) {
				$country_id = sanitize_text_field( $_POST[ UTALII_MKP . 'country' ] );
				
				if( !empty( $country_id ) ){
					$data['country_id'] = $country_id;
					
					$data['country'] = UTALII_Location::get_country( $country_id );
					$data['country'] = $data['country']['title'];
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'postal_code' ] ) ) {
				$postal_code = sanitize_text_field( $_POST[ UTALII_MKP . 'postal_code' ] );
				$data['postal_code'] = $postal_code;
			}
			
			
			update_post_meta( $post_id, UTALII_MKP . 'address', $data['address'] );
			update_post_meta( $post_id, UTALII_MKP . 'city_id', $data['city_id'] );
			update_post_meta( $post_id, UTALII_MKP . 'city', $data['city'] );
			update_post_meta( $post_id, UTALII_MKP . 'state_id', $data['state_id'] );
			update_post_meta( $post_id, UTALII_MKP . 'state', $data['state'] );
			update_post_meta( $post_id, UTALII_MKP . 'country_id', $data['country_id'] );
			update_post_meta( $post_id, UTALII_MKP . 'country', $data['country'] );
			update_post_meta( $post_id, UTALII_MKP . 'postal_code', $data['postal_code'] );
			
			/* neither we need to get nor to update the short_description and long description
			 * because they are post excerpt and post content respectively
			 * and are automatically saved by WordPress in their corresponding fields
			 */
		}
		
		function render_details(){
			global $post;
			$post_id = $post->ID;
			
			/* core content */
			$data = array(
				'address'				=>	'',
				'city_id'				=>	'',
				'city'					=>	'',
				'state_id'				=>	'',
				'state'					=>	'',
				'country_id'			=>	'',
				'country'				=>	'',
				'postal_code'				=>	'',
				'short_description'		=>	'',
				'long_description'		=>	'',
			);
			
			$data['address']			=	get_post_meta( $post_id, UTALII_MKP . 'address', true );
			$data['city_id']			=	get_post_meta( $post_id, UTALII_MKP . 'city_id', true );
			$data['city']				=	get_post_meta( $post_id, UTALII_MKP . 'city', true );
			$data['state_id']			=	get_post_meta( $post_id, UTALII_MKP . 'state_id', true );
			$data['state']				=	get_post_meta( $post_id, UTALII_MKP . 'state', true );
			$data['country_id']			=	get_post_meta( $post_id, UTALII_MKP . 'country_id', true );
			$data['country']			=	get_post_meta( $post_id, UTALII_MKP . 'country', true );
			$data['postal_code']		=	get_post_meta( $post_id, UTALII_MKP . 'postal_code', true );
			
			$long_description			=	$post->post_content;
			$short_description			=	$post->post_excerpt;
			$data['short_description']	=	htmlspecialchars_decode( $short_description );
			$data['long_description']	=	htmlspecialchars_decode( $long_description );
			?>
			
			<div class="utalii_info">
				<span class="dashicons dashicons-info"></span>
				<?php echo __( 'Fields marked with * are required.', 'utalii' ); ?>
			</div>
			
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'address'; ?>">*<?php echo __( 'Address', 'utalii' ); ?>: </label>
						</th>
						<td>
							<input type="text" id="<?php echo UTALII_MKP . 'address'; ?>" name="<?php echo UTALII_MKP . 'address'; ?>" value="<?php echo esc_attr( $data['address'] ); ?>" required="required" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'city'; ?>">*<?php echo __( 'City', 'utalii' ); ?>: </label>
						</th>
						<td>
							<select id="<?php echo UTALII_MKP . 'city'; ?>" name="<?php echo UTALII_MKP . 'city'; ?>"  required="required">
								<option value="">— <?php _e( 'Select City', 'utalii' ); ?> —</option>
								<?php if( $locs = UTALII_Location::get_cities() ) { ?>
								<?php foreach( $locs as $key => $val ) { ?>
								<option value="<?php echo $key; ?>" <?php selected( $key, $data['city_id'] ); ?>><?php echo $val['title']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
							<br />
							<a target="_blank" href="<?php echo admin_url( 'post-new.php?post_type=' . UTALII_PREFIX . 'city' ); ?>"><?php _e( 'Add new city', 'utalii' ); ?></a>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'state'; ?>"><?php echo __( 'County', 'utalii' ); ?>: </label>
						</th>
						<td>
							<select id="<?php echo UTALII_MKP . 'state'; ?>" name="<?php echo UTALII_MKP . 'state'; ?>">
								<option value="">— <?php _e( 'Select County or N/A', 'utalii' ); ?> —</option>
								<?php if( $locs = UTALII_Location::get_states() ) { ?>
								<?php foreach( $locs as $key => $val ) { ?>
								<option value="<?php echo $key; ?>" <?php selected( $key, $data['state_id'] ); ?>><?php echo $val['title']; ?></option>
								<?php } ?>
								<?php } ?>
							</select><span class="spinner"></span>
							<br />
							<a target="_blank" href="<?php echo admin_url( 'post-new.php?post_type=' . UTALII_PREFIX . 'state' ); ?>"><?php _e( 'Add new county', 'utalii' ); ?></a>
							<p class="description"><?php _e( 'If County is not applicable in a region, one can leave this option untouched. County is optional.', 'utalii' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'country'; ?>">*<?php echo __( 'Country', 'utalii' ); ?>: </label>
						</th>
						<td>
							<select id="<?php echo UTALII_MKP . 'country'; ?>" name="<?php echo UTALII_MKP . 'country'; ?>"  required="required">
								<option value="">— <?php _e( 'Select Country', 'utalii' ); ?> —</option>
								<?php if( $locs = UTALII_Location::get_countries() ) { ?>
								<?php foreach( $locs as $key => $val ) { ?>
								<option value="<?php echo $key; ?>" <?php selected( $key, $data['country_id'] ); ?>><?php echo $val['title']; ?></option>
								<?php } ?>
								<?php } ?>
							</select><span class="spinner"></span>
							<br />
							<a target="_blank" href="<?php echo admin_url( 'post-new.php?post_type=' . UTALII_PREFIX . 'country' ); ?>"><?php _e( 'Add new country', 'utalii' ); ?></a>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'postal_code'; ?>"><?php echo __( 'Postal code', 'utalii' ); ?>: </label>
						</th>
						<td>
							<input type="text" id="<?php echo UTALII_MKP . 'postal_code'; ?>" name="<?php echo UTALII_MKP . 'postal_code'; ?>" value="<?php echo $data['postal_code']; ?>"  />
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<label for=""><b><?php _e( 'Short description', 'utalii' ); ?>:</b></label>
							<?php
								$wpe_shortdesc_id = 'excerpt'; /* $_POST[] name attribute name also */
								
								$wpe_shortdesc_settings = array(
									'textarea_name'		=>	'excerpt',
									'drag_drop_upload'	=>	true,
									'editor_css'		=>	'<style> #wp-excerpt-editor-container .wp-editor-area{height:150px; width:100%;} </style>',
								);
								
								wp_editor( $data['short_description'], 'excerpt', $wpe_shortdesc_settings );
							?>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<label for=""><b><?php _e( 'Long description', 'utalii' ); ?>:</b></label>
							<?php
							$wpe_longdesc_settings = array(
								'textarea_name'		=>	'content',
								'drag_drop_upload'	=>	false,
								'editor_css'		=>	'<style>#wp-content-editor-container .wp-editor-area{height:300px; width:100%;}</style>'
							);
							
							wp_editor( $data['long_description'], 'content', $wpe_longdesc_settings );
							?>
						</td>
					</tr>
				</tbody>
			</table>
			
			<?php /* nonce fields */ ?>
			<input type="hidden" name="prevent_delete_meta_movetotrash" id="prevent_delete_meta_movetotrash" value="<?php echo wp_create_nonce( UTALII_MKP . $post_id ); ?>" />
			<?php
			wp_nonce_field( UTALII_MKP . 'save_meta_data', UTALII_MKP . 'meta_box_nonce' );
		}
		
		function add_gallery_meta_box(){
			$css = UTALII_PLUGIN_URL .'/assets/css/gallery-meta-box.css';
			$js = UTALII_PLUGIN_URL .'/assets/js/gallery-meta-box.js';
			new GIMBGallery( array( $this->post_type ), $css, $js );
		}
		/* hotel details, end */
		
		function add_assets(){
			/* add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );*/
		}
		
		function register_assets(){
			global $pagenow, $typenow;
			
			if( $typenow == $this->post_type ){ /* include scripts only on this post type page in wp-admin */
			
				/* hotel-js, start */
				$handle		=	'hotel_js';
				$src		=	UTALII_PLUGIN_URL . '/inc/custom-posts/hotel/assets/js/hotel-post.js';
				$deps		=	array( 'jquery' );
				$ver		=	false;
				$in_footer	=	false;
				
				wp_register_script( $handle, $src, $deps, $ver, $in_footer );
				
				$obj	=	UTALII_PREFIX . $handle . '_obj';
				$props	=	array(
								'admin_ajax_url'	=>	admin_url( 'admin-ajax.php' ),
								'mkp'				=>	UTALII_MKP,
							);
				
				wp_localize_script( $handle, $obj, $props );
				
				wp_enqueue_script( $handle );
				/* hotel-js, end */
				
			}
		}
		
		/* adding custom columns, start */
		function add_custom_columns( $columns ) {
			unset( $columns['comments'] );
			unset( $columns['date'] );
			
			$columns['title'] = __( 'Hotel', 'utalii' );
			
			$new_columns = array(
				'address'		=>	__( 'Address', 'utalii' ),
				'city'			=>	__( 'City', 'utalii' ),
				'state'			=>	__( 'State', 'utalii' ),
				'country'		=>	__( 'Country', 'utalii' ),
				'postal_code'	=>	__( 'Postal Code', 'utalii' ),
			);
			
			$updated_columns = array_merge( $columns, $new_columns );
			
			return $updated_columns;
		}
		
		function custom_column_content( $column, $post_id ) {
			switch ( $column ) {
				case 'address' :
					$data = get_post_meta( $post_id , UTALII_MKP . 'address', true );
					$data = trim( $data );
					if( !empty( $data ) ){
						echo $data;
					} else {
						echo '';
					}
					
					break;
				
				case 'city' :
					$data = get_post_meta( $post_id , UTALII_MKP . 'city', true );
					$city_id = get_post_meta( $post_id , UTALII_MKP . 'city_id', true );
					$data = trim( $data );
					if( !empty( $data ) ){
						echo '<a href="'.admin_url( 'post.php?post='.$city_id.'&action=edit').'">'.$data.'</a>';
					} else {
						echo '';
					}
					
					break;
				
				
				case 'state' :
					$data = get_post_meta( $post_id , UTALII_MKP . 'state', true );
					$state_id = get_post_meta( $post_id , UTALII_MKP . 'state_id', true );
					$data = trim( $data );
					if( !empty( $data ) ){
						echo '<a href="'.admin_url( 'post.php?post='.$state_id.'&action=edit').'">'.$data.'</a>';
					} else {
						echo '';
					}
					
					break;
				
				case 'country' :
					$data = get_post_meta( $post_id , UTALII_MKP . 'country', true );
					$country_id = get_post_meta( $post_id , UTALII_MKP . 'country_id', true );
					$data = trim( $data );
					if( !empty( $data ) ){
						echo '<a href="'.admin_url( 'post.php?post='.$country_id.'&action=edit').'">'.$data.'</a>';
					} else {
						echo '';
					}
					
					break;
				
				case 'postal_code' :
					$data = get_post_meta( $post_id , UTALII_MKP . 'postal_code', true );
					$data = trim( $data );
					if( !empty( $data ) ){
						echo $data;
					} else {
						echo '';
					}
					
					break;
			}
		}
		
		function custom_column_sortable( $columns ) {
			$columns['address'] = 'address';
			$columns['city'] = 'city';
			$columns['state'] = 'state';
			$columns['country'] = 'country';
			$columns['postal_code'] = 'postal_code';
			
			return $columns;
		}
		
		function edit_custom_post_load() {
			add_filter( 'request', array( $this, 'sort_column_address' ) );
			add_filter( 'request', array( $this, 'sort_column_city' ) );
			add_filter( 'request', array( $this, 'sort_column_state' ) );
			add_filter( 'request', array( $this, 'sort_column_country' ) );
			add_filter( 'request', array( $this, 'sort_column_postal_code' ) );
		}
		
		function sort_column_address( $vars ) {
			if ( isset( $vars['post_type'] ) && $this->post_type == $vars['post_type'] ) {
				if ( isset( $vars['orderby'] ) && 'address' == $vars['orderby'] ) {
					$vars = array_merge(
						$vars,
						array(
							'meta_key'	=>	UTALII_MKP . 'address',
							'orderby'	=>	'meta_value',
						)
					);
				}
			}
			return $vars;
		}
		
		function sort_column_city( $vars ) {
			if ( isset( $vars['post_type'] ) && $this->post_type == $vars['post_type'] ) {
				if ( isset( $vars['orderby'] ) && 'city' == $vars['orderby'] ) {
					$vars = array_merge(
						$vars,
						array(
							'meta_key'	=>	UTALII_MKP . 'city',
							'orderby'	=>	'meta_value',
						)
					);
				}
			}
			return $vars;
		}
		
		function sort_column_state( $vars ) {
			if ( isset( $vars['post_type'] ) && $this->post_type == $vars['post_type'] ) {
				if ( isset( $vars['orderby'] ) && 'state' == $vars['orderby'] ) {
					$vars = array_merge(
						$vars,
						array(
							'meta_key'	=>	UTALII_MKP . 'state',
							'orderby'	=>	'meta_value',
						)
					);
				}
			}
			return $vars;
		}
		
		function sort_column_country( $vars ) {
			if ( isset( $vars['post_type'] ) && $this->post_type == $vars['post_type'] ) {
				if ( isset( $vars['orderby'] ) && 'country' == $vars['orderby'] ) {
					$vars = array_merge(
						$vars,
						array(
							'meta_key'	=>	UTALII_MKP . 'country',
							'orderby'	=>	'meta_value',
						)
					);
				}
			}
			return $vars;
		}
		
		function sort_column_postal_code( $vars ) {
			if ( isset( $vars['post_type'] ) && $this->post_type == $vars['post_type'] ) {
				if ( isset( $vars['orderby'] ) && 'postal_code' == $vars['orderby'] ) {
					$vars = array_merge(
						$vars,
						array(
							'meta_key'	=>	UTALII_MKP . 'postal_code',
							'orderby'	=>	'meta_value_num',
						)
					);
				}
			}
			return $vars;
		}
		/* adding custom columns, end */
	}
}
UTALII_Hotel::get_instance();