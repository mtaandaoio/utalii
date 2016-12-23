<?php
if( !class_exists('UTALII_Room_Type') ){
	class UTALII_Room_Type{
		private static $_instance = null;
		private $post_type = null;
		private $post_rewrite_slug = null;
		
		private function __construct(){
			$this->post_type = UTALII_PREFIX . 'rooms';
			$this->post_rewrite_slug = 'rooms';
			add_action( 'init', array( $this, 'register_custom_post_type' ), 0 );
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
				'name'					=>	_x( 'Rooms', 'Post Type General Name', 'utalii' ),
				'singular_name'			=>	_x( 'Room', 'Post Type Singular Name', 'utalii' ),
				'menu_name'				=>	__( 'Rooms', 'utalii' ),
				'name_admin_bar'		=>	__( 'Rooms', 'utalii' ),
				'parent_item_colon'		=>	__( 'Parent Room:', 'utalii' ),
				'all_items'				=>	__( 'Rooms', 'utalii' ),
				'add_new_item'			=>	__( 'Add New Room', 'utalii' ),
				'add_new'				=>	__( 'Add New Room', 'utalii' ),
				'new_item'				=>	__( 'New Room', 'utalii' ),
				'edit_item'				=>	__( 'Edit Room', 'utalii' ),
				'update_item'			=>	__( 'Update Room', 'utalii' ),
				'view_item'				=>	__( 'View Room', 'utalii' ),
				'search_items'			=>	__( 'Search Room', 'utalii' ),
				'not_found'				=>	__( 'Rooms Not found', 'utalii' ),
				'not_found_in_trash'	=>	__( 'Rooms Not found in Trash', 'utalii' ),
			);
			
			$rewrite = array(
				'slug'			=>	$this->post_rewrite_slug,
				'with_front'	=>	true,
				'pages'			=>	true,
				'feeds'			=>	true,
			);
			
			$args = array(
				'label'					=>	__( 'Rooms', 'utalii' ),
				'description'			=>	__( 'A Room Type in a Hotel, like double, single, luxury etc.', 'utalii' ),
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
				'has_archive'			=>	'rooms',
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
		
		/* room details, start */
		function register_details( $post_type ) {
			add_meta_box(
				UTALII_PREFIX .'room_details',
				__( 'Room Details', 'utalii' ),
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
			
			/* validation checks, start */
			
			$data = array(
				'hotel_id'						=>	'',
				'hotel'						=>	'',
				'charge_per_night'			=>	'',
				'max_adult_occupancy'		=>	'',
				'max_children_occupancy'	=>	'',
				'total_rooms'				=>	'',
				'short_description'			=>	'',
				'long_description'			=>	'',
			);
			
			$data['hotel_id']				=	get_post_meta( $post_id, UTALII_MKP . 'hotel_id', true );
			$data['hotel']					=	get_post_meta( $post_id, UTALII_MKP . 'hotel', true );
			$data['charge_per_night']		=	get_post_meta( $post_id, UTALII_MKP . 'charge_per_night', true );
			$data['max_adult_occupancy']	=	get_post_meta( $post_id, UTALII_MKP . 'max_adult_occupancy', true );
			$data['max_children_occupancy']	=	get_post_meta( $post_id, UTALII_MKP . 'max_children_occupancy', true );
			$data['total_rooms']			=	get_post_meta( $post_id, UTALII_MKP . 'total_rooms', true );
			
			if ( isset( $_POST[ UTALII_MKP . 'hotel' ] ) ) {
				$hotel_id = sanitize_text_field( $_POST[ UTALII_MKP . 'hotel' ] );
				
				if( !empty( $hotel_id ) ){
					$data['hotel_id'] = $hotel_id;
					$data['hotel'] = UTALII_Misc::get_hotel( $hotel_id );
					$data['hotel'] = $data['hotel']['title'];
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'charge_per_night' ] ) ) {
				$charge_per_night = (float) sanitize_text_field( $_POST[ UTALII_MKP . 'charge_per_night' ] );
				
				if( 0 <= $charge_per_night ){
					$data['charge_per_night'] = $charge_per_night;
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'max_adult_occupancy' ] ) ) {
				$max_adult_occupancy = (int) sanitize_text_field( $_POST[ UTALII_MKP . 'max_adult_occupancy' ] );
				
				if( 0 <= $max_adult_occupancy ){
					$data['max_adult_occupancy'] = $max_adult_occupancy;
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'max_children_occupancy' ] ) ) {
				$max_children_occupancy = (int) sanitize_text_field( $_POST[ UTALII_MKP . 'max_children_occupancy' ] );
				
				if( 0 <= $max_children_occupancy ){
					$data['max_children_occupancy'] = $max_children_occupancy;
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'total_rooms' ] ) ) {
				$total_rooms = (int) sanitize_text_field( $_POST[ UTALII_MKP . 'total_rooms' ] );
				
				if( 0 <= $total_rooms ){
					$data['total_rooms'] = $total_rooms;
				}
			}
			
			
			update_post_meta( $post_id, UTALII_MKP . 'hotel_id', $data['hotel_id'] );
			update_post_meta( $post_id, UTALII_MKP . 'hotel', $data['hotel'] );
			update_post_meta( $post_id, UTALII_MKP . 'charge_per_night', $data['charge_per_night'] );
			update_post_meta( $post_id, UTALII_MKP . 'max_adult_occupancy', $data['max_adult_occupancy'] );
			update_post_meta( $post_id, UTALII_MKP . 'max_children_occupancy', $data['max_children_occupancy'] );
			update_post_meta( $post_id, UTALII_MKP . 'total_rooms', $data['total_rooms'] );
			
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
				'hotel_id'					=>	'',
				'hotel'						=>	'',
				'charge_per_night'			=>	0,
				'max_adult_occupancy'		=>	0,
				'max_children_occupancy'	=>	0,
				'total_rooms'				=>	0,
				'short_description'			=>	'',
				'long_description'			=>	'',
			);
			
			$data['hotel_id']				=	get_post_meta( $post_id, UTALII_MKP . 'hotel_id', true );
			$data['hotel']					=	get_post_meta( $post_id, UTALII_MKP . 'hotel', true );
			
			$charge_per_night				=	(float) get_post_meta( $post_id, UTALII_MKP . 'charge_per_night', true );
			if( 0 <= $charge_per_night ){
				$data['charge_per_night'] = $charge_per_night;
			}
			
			$max_adult_occupancy			=	(int) get_post_meta( $post_id, UTALII_MKP . 'max_adult_occupancy', true );
			if( 0 <=  $max_adult_occupancy ){
				$data['max_adult_occupancy'] = $max_adult_occupancy;
			}
			
			$max_children_occupancy			=	(int) get_post_meta( $post_id, UTALII_MKP . 'max_children_occupancy', true );
			if( 0 <= $max_children_occupancy ){
				$data['max_children_occupancy'] = $max_children_occupancy;
			}
			
			$total_rooms					=	(int)get_post_meta( $post_id, UTALII_MKP . 'total_rooms', true );
			if( 0 <= $total_rooms ){
				$data['total_rooms'] = $total_rooms;
			}
			
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
							<label for="<?php echo UTALII_MKP . 'hotel'; ?>">*<?php echo __( 'Hotel', 'utalii' ); ?>: </label>
						</th>
						<td>
							<select id="<?php echo UTALII_MKP . 'hotel'; ?>" name="<?php echo UTALII_MKP . 'hotel'; ?>"  required="required">
								<option value="">— <?php _e( 'Select Hotel', 'utalii' ); ?> —</option>
								<?php if( $hotels = UTALII_Misc::get_hotels() ) { ?>
								<?php foreach( $hotels as $key => $val ) { ?>
								<option value="<?php echo $key; ?>" <?php selected( $key, $data['hotel_id'] ); ?>><?php echo $val['title']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
							<br />
							<a target="_blank" href="<?php echo admin_url( 'post-new.php?post_type=' . UTALII_PREFIX . 'hotels' ); ?>">Add new hotel</a>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'charge_per_night'; ?>"><?php echo __( 'Charge per night', 'utalii' ); ?> (1USD = KSh 100) : </label>
						</th>
						<td>
							KSh<input type="number" id="<?php echo UTALII_MKP . 'charge_per_night'; ?>" name="<?php echo UTALII_MKP . 'charge_per_night'; ?>" required="required" value="<?php echo $data['charge_per_night']; ?>" min="0" step="any" />
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'max_adult_occupancy'; ?>"><?php echo __( 'Max. adult occupancy', 'utalii' ); ?>: </label>
						</th>
						<td>
							<input type="number" id="<?php echo UTALII_MKP . 'max_adult_occupancy'; ?>" name="<?php echo UTALII_MKP . 'max_adult_occupancy'; ?>" required="required" value="<?php echo $data['max_adult_occupancy']; ?>" min="0" />
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'max_children_occupancy'; ?>"><?php echo __( 'Max. children occupancy', 'utalii' ); ?>: </label>
						</th>
						<td>
							<input type="number" id="<?php echo UTALII_MKP . 'max_children_occupancy'; ?>" name="<?php echo UTALII_MKP . 'max_children_occupancy'; ?>" required="required" value="<?php echo $data['max_children_occupancy']; ?>" min="0" />
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'total_rooms'; ?>"><?php echo __( 'Total rooms of this type', 'utalii' ); ?>: </label>
						</th>
						<td>
							<input type="number" id="<?php echo UTALII_MKP . 'total_rooms'; ?>" name="<?php echo UTALII_MKP . 'total_rooms'; ?>" required="required" value="<?php echo $data['total_rooms']; ?>" min="0" />
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
		/* room details, end */
		
		
		/* adding custom columns, start */
		function add_custom_columns( $columns ) {
			unset( $columns['comments'] );
			unset( $columns['date'] );
			
			$columns['title'] = __( 'Room', 'utalii' );
			
			$new_columns = array(
				'hotel'		=>	__( 'Hotel', 'utalii' ),
				/* 'address'	=>	__( 'Address', 'utalii' ),
				'city'		=>	__( 'City', 'utalii' ),
				'state'		=>	__( 'State', 'utalii' ),
				'country'	=>	__( 'Country', 'utalii' ),*/
			);
			
			$updated_columns = array_merge( $columns, $new_columns );
			
			return $updated_columns;
		}
		
		function custom_column_content( $column, $post_id ) {
			switch ( $column ) {
				case 'hotel' :
					$data = get_post_meta( $post_id , UTALII_MKP . 'hotel', true );
					$hotel_id = get_post_meta( $post_id , UTALII_MKP . 'hotel_id', true );
					$data = trim( $data );
					if( !empty( $data ) ){
						echo '<a href="'.admin_url( 'post.php?post='.$hotel_id.'&action=edit').'">'.$data.'</a>';
					} else {
						echo '';
					}
					
					break;
				
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
					$data = trim( $data );
					if( !empty( $data ) ){
						echo $data;
					} else {
						echo '';
					}
					
					break;
				
				
				case 'state' :
					$data = get_post_meta( $post_id , UTALII_MKP . 'state', true );
					$data = trim( $data );
					if( !empty( $data ) ){
						echo $data;
					} else {
						echo '';
					}
					
					break;
				
				case 'country' :
					$data = get_post_meta( $post_id , UTALII_MKP . 'country', true );
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
			$columns['hotel']	=	'hotel';
			$columns['address']	=	'address';
			$columns['city']	=	'city';
			$columns['state']	=	'state';
			$columns['country']	=	'country';
			
			return $columns;
		}
		
		function edit_custom_post_load() {
			add_filter( 'request', array( $this, 'sort_column_hotel' ) );
			add_filter( 'request', array( $this, 'sort_column_address' ) );
			add_filter( 'request', array( $this, 'sort_column_city' ) );
			add_filter( 'request', array( $this, 'sort_column_state' ) );
			add_filter( 'request', array( $this, 'sort_column_country' ) );
		}
		
		function sort_column_hotel( $vars ) {
			if ( isset( $vars['post_type'] ) && $this->post_type == $vars['post_type'] ) {
				if ( isset( $vars['orderby'] ) && 'address' == $vars['orderby'] ) {
					$vars = array_merge(
						$vars,
						array(
							'meta_key'	=>	UTALII_MKP . 'hotel',
							'orderby'	=>	'meta_value',
						)
					);
				}
			}
			return $vars;
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
		/* adding custom columns, end */
	}
}
UTALII_Room_Type::get_instance();