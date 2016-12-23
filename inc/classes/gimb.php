<?php
if( !class_exists('GIMBGallery') ){
	class GIMBGallery {
		protected static $_instance = null;
		private $post_types = null;
		private $js_url = null;
		private $css_url = null;
		private $css_h = 'gimb-css-h';
		private $js_h = 'gimb-js-h';
		
		public function __construct( $post_types, $css_url, $js_url ){
			
			if( is_array( $post_types ) && !empty( $post_types ) ){
				$this->post_types = $post_types;
				$this->css_url = $css_url;
				$this->js_url = $js_url;
			}
			
			if( !is_null( $this->post_types ) && !is_null( $this->css_url ) && !is_null( $this->js_url ) ){
				$this->add_css();
				$this->add_js();
				
				$this->add_action_meta_box();
			}
			
		}
		
		public function add_action_meta_box(){
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box_func' ) );
			add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
		}
		
		public function add_meta_box_func(){
			$post_types = $this->post_types;
			foreach( $post_types as $post_type ){
				add_meta_box(
					'gimb_top_container',
					__( 'Gallery', 'textdomain' ),
					array( $this, 'render_meta_box' ),
					$post_type,
					'side',
					'default'
				);
			}
		}
		
		public function render_meta_box(){
			global $post;
			$post_id = $post->ID;
			
			$gimb_gallery_images = trim( get_post_meta( $post_id, '_gimb_gallery_images', true ) );
			$gimb_gallery_images_arr = array_filter( explode( ',', $gimb_gallery_images ) );
			?>
			<div class="gimb_container" id="gimb_container">
				<div class="gallery_container">
					<ul class="images">
						<?php if ( count( $gimb_gallery_images_arr ) ) { ?>
						<?php foreach ( $gimb_gallery_images_arr as $gimb_gallery_image_id ) { ?>
						
						<li class="image" data-attachment_id="<?php echo esc_attr( $gimb_gallery_image_id ); ?>" title="<?php echo __( "Drag to reorder", "textdomain" ); ?>">
							<?php echo wp_get_attachment_image( $gimb_gallery_image_id, 'thumbnail' ); ?>
							<ul class="controls">
								<li class="delete">
									<span class="dashicons dashicons-no-alt delete_image_icon" title="<?php echo __( "Remove image", "textdomain" ); ?>"></span>
								</li>
							</ul>
						</li>
						
						<?php } } ?>
					</ul>
				</div>
				<div class="clear"></div>
				
				<div class="other_controls_container">
					<div class="add_image hide-if-no-js" id="gimb_add_image">
						<a href="javascript:void(0);" title="<?php echo __( "Add images(s) from media library", "textdomain" ); ?>">
							<?php _e( 'Add image(s)', 'textdomain' ); ?>
						</a>
					</div>
					
					<div class="hidden_controls">
						<input type="hidden" id="gimb_gallery_images" name="gimb_gallery_images" value="<?php echo esc_attr( $gimb_gallery_images ); ?>" />
						<?php if(false){ ?>
						<input type="hidden" name="prevent_delete_meta_movetotrash" id="prevent_delete_meta_movetotrash" value="<?php echo wp_create_nonce('gimb_gallery_images_'.$post_id ); ?>" />
						<?php } ?>
						<?php wp_nonce_field( 'gimb_gallery_images_save_meta_box_data', 'gimb_gallery_images_meta_box_nonce' ); ?>
					</div>
				</div>
				
			</div> <!-- gimb_container end -->
			<?php
		}
		
		function save_meta_box_data( $post_id ) {
			
			/* validation checks */
			if ( ! isset( $_POST['gimb_gallery_images_meta_box_nonce'] ) ) { return; }
			if ( ! wp_verify_nonce( $_POST['gimb_gallery_images_meta_box_nonce'], 'gimb_gallery_images_save_meta_box_data' ) ) { return; }
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
			
			/* data save */
			if ( ! isset( $_POST['gimb_gallery_images'] ) ) { return; }
			
			$gimb_gallery_images = sanitize_text_field( $_POST['gimb_gallery_images'] );
			
			/* prevent deleting meta data on trash the post */
			/* if ( !wp_verify_nonce($_POST['prevent_delete_meta_movetotrash'], 'gimb_gallery_images_'.$post_id ) ) { return $post_id; }*/
			
			/* update meta */
			update_post_meta( $post_id, '_gimb_gallery_images', $gimb_gallery_images );
		}
		
		public function add_css(){
			add_action( 'admin_enqueue_scripts', array( $this, 'register_css' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_css' ) );
		}
		
		public function add_js(){
			add_action( 'admin_enqueue_scripts', array( $this, 'register_js' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_js' ) );
		}
		
		public function register_css(){
			if ( !wp_script_is( $this->css_h, 'registered' ) ){
				wp_enqueue_style( $this->css_h, $this->css_url );
			}
		}
		
		public function enqueue_css(){
			if ( !wp_script_is( $this->css_h, 'enqueued' ) ){
				wp_enqueue_style( $this->css_h );
			}
		}
		
		public function register_js(){
			if ( !wp_script_is( $this->js_h, 'registered' ) ){
				$deps = array(
					'jquery',
					'jquery-ui-core',
					'jquery-ui-widget',
					'jquery-ui-mouse',
					'jquery-ui-draggable',
					'jquery-ui-droppable',
					'jquery-ui-sortable'
				);
				
				wp_register_script( $this->js_h, $this->js_url, $deps, false, false );
			}
		}
		
		public function enqueue_js(){
			if ( !wp_script_is( $this->js_h, 'enqueued' ) ){
				wp_enqueue_script( $this->js_h );
			}
		}
		
	} /* GIMBGallery class end */
}