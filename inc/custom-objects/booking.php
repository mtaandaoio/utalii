<?php
if( !class_exists('UTALII_Booking') ){
	class UTALII_Booking{
		private static $_instance = null;
		private $post_type = null;
		private $post_rewrite_slug = null;
		
		private function __construct(){
			$this->post_type = UTALII_PREFIX . 'bookings';
			$this->post_rewrite_slug = 'bookings';
			add_action( 'init', array( $this, 'register_custom_post_type' ), 0 );
			$this->add_meta_boxes();
			$this->save_meta_boxes();
			
			/* custom column, start */
			add_filter('manage_'.$this->post_type.'_posts_columns' , array( $this, 'add_custom_columns' ) );
			add_action( 'manage_'.$this->post_type.'_posts_custom_column' , array( $this, 'custom_column_content' ), 10, 2 );
			/*add_filter( 'manage_edit-'.$this->post_type.'_sortable_columns', array( $this, 'custom_column_sortable' ) );
			add_action( 'load-edit.php', array( $this, 'edit_custom_post_load' ) );*/
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
				'name'					=>	_x( 'Bookings', 'Post Type General Name', 'utalii' ),
				'singular_name'			=>	_x( 'Booking', 'Post Type Singular Name', 'utalii' ),
				'menu_name'				=>	__( 'Bookings', 'utalii' ),
				'name_admin_bar'		=>	__( 'Bookings', 'utalii' ),
				'parent_item_colon'		=>	__( 'Parent Booking:', 'utalii' ),
				'all_items'				=>	__( 'Bookings', 'utalii' ),
				'add_new_item'			=>	__( 'Add New Booking', 'utalii' ),
				'add_new'				=>	__( 'Add New Booking', 'utalii' ),
				'new_item'				=>	__( 'New Booking', 'utalii' ),
				'edit_item'				=>	__( 'Edit Booking', 'utalii' ),
				'update_item'			=>	__( 'Update Booking', 'utalii' ),
				'view_item'				=>	__( 'View Booking', 'utalii' ),
				'search_items'			=>	__( 'Search Booking', 'utalii' ),
				'not_found'				=>	__( 'Booking Not found', 'utalii' ),
				'not_found_in_trash'	=>	__( 'Booking Not found in Trash', 'utalii' ),
			);
			
			$rewrite = array(
				'slug'			=>	$this->post_rewrite_slug,
				'with_front'	=>	true,
				'pages'			=>	true,
				'feeds'			=>	true,
			);
			
			$args = array(
				'label'					=>	__( 'Bookings', 'utalii' ),
				'description'			=>	__( 'Bookings', 'utalii' ),
				'labels'				=>	$labels,
				'supports'				=>	array( '' ),
				'taxonomies'			=>	array(),
				'hierarchical'			=>	false,
				'public'				=>	true,
				'show_ui'				=>	true,
				'show_in_menu'			=>	'utalii',
				'menu_position'			=>	5,
				'show_in_admin_bar'		=>	true,
				'show_in_nav_menus'		=>	true,
				'can_export'			=>	true,
				'has_archive'			=>	'bookings',
				'exclude_from_search'	=>	false,
				'publicly_queryable'	=>	false,
				'rewrite'				=>	$rewrite,
				'capability_type'		=>	'post'
			);
			
			register_post_type( $this->post_type, $args );

		}
		/* register custom post type function, end */
		
		/* adding meta boxes */
		function add_meta_boxes(){
			add_action( 'add_meta_boxes_' . $this->post_type, array( $this, 'register_details' ) );
		}
		
		/* saving meta boxes */
		function save_meta_boxes(){
			add_action( 'save_post_' . $this->post_type, array( $this, 'save_details' ) );
		}
		
		/* room details, start */
		function register_details( $post_type ) {
			add_meta_box(
				UTALII_PREFIX .'booking_details',
				__( 'Booking Details', 'utalii' ),
				array( $this, 'render_details' ),
				$this->post_type,
				'advanced',
				'high'
			);
		}
		
		function save_details( $post_id )
		{
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
			/* update */
			/*$data = array(
				'booking_hotel_id'					=>	'',
				'booking_room_ids'					=>	'',
				'booking_arrival_date_time'			=>	'',
				'booking_departure_date_time'		=>	'',
				'booking_no_of_adults'				=>	0,
				'booking_no_of_child'				=>	0,
				'booking_customer_id'				=>	''
			);
			
			$data['booking_hotel_id']				=	get_post_meta( $post_id, UTALII_MKP . 'booking_hotel_id', true );
			$data['booking_room_ids']				=	get_post_meta( $post_id, UTALII_MKP . 'booking_room_ids', true );
			$data['booking_arrival_date_time']		=	get_post_meta( $post_id, UTALII_MKP . 'booking_arrival_date_time', true );
			$data['booking_departure_date_time']	=	get_post_meta( $post_id, UTALII_MKP . 'booking_departure_date_time', true );
			$data['booking_no_of_adults']			=	get_post_meta( $post_id, UTALII_MKP . 'booking_no_of_adults', true );
			$data['booking_no_of_child']			=	get_post_meta( $post_id, UTALII_MKP . 'booking_no_of_child', true );
			$data['booking_customer_id']			=	get_post_meta( $post_id, UTALII_MKP . 'booking_customer_id', true );
			
			if ( isset( $_POST[ UTALII_MKP . 'booking_hotel_id' ] ) )
			{
				$booking_hotel_id = sanitize_text_field( $_POST[ UTALII_MKP . 'booking_hotel_id' ] );
				if( !empty( $booking_hotel_id ) )
				{
					$data['booking_hotel_id'] = $booking_hotel_id;
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'booking_room_ids' ] ) )
			{
				$booking_room_ids = sanitize_text_field( $_POST[ UTALII_MKP . 'booking_room_ids' ] );
				if( 0 <= $booking_room_ids )
				{
					$data['booking_room_ids'] = $booking_room_ids;
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'booking_arrival_date_time' ] ) )
			{
				$booking_arrival_date_time = sanitize_text_field( $_POST[ UTALII_MKP . 'booking_arrival_date_time' ] );
				if( $booking_arrival_date_time != "")
				{
					$data['booking_arrival_date_time'] = $booking_arrival_date_time;
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'booking_departure_date_time' ] ) )
			{
				$booking_departure_date_time = sanitize_text_field( $_POST[ UTALII_MKP . 'booking_departure_date_time' ] );
				if( $booking_departure_date_time != "")
				{
					$data['booking_departure_date_time'] = $booking_departure_date_time;
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'booking_no_of_adults' ] ) )
			{
				$booking_no_of_adults = (int) sanitize_text_field( $_POST[ UTALII_MKP . 'booking_no_of_adults' ] );
				if( 0 <= $booking_no_of_adults )
				{
					$data['booking_no_of_adults'] = $booking_no_of_adults;
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'booking_no_of_child' ] ) )
			{
				$booking_no_of_child = (int) sanitize_text_field( $_POST[ UTALII_MKP . 'booking_no_of_child' ] );
				if( 0 <= $booking_no_of_child )
				{
					$data['booking_no_of_child'] = $booking_no_of_child;
				}
			}
			
			if ( isset( $_POST[ UTALII_MKP . 'booking_customer_id' ] ) )
			{
				$booking_customer_id =  sanitize_text_field( $_POST[ UTALII_MKP . 'booking_customer_id' ] );
				if( 0 <= $booking_customer_id )
				{
					$data['booking_customer_id'] = $booking_customer_id;
				}
			}
			
			update_post_meta( $post_id, UTALII_MKP . 'booking_hotel_id', $data['booking_hotel_id'] );
			update_post_meta( $post_id, UTALII_MKP . 'booking_room_ids', $data['booking_room_ids'] );
			update_post_meta( $post_id, UTALII_MKP . 'booking_arrival_date_time', strtotime($data['booking_arrival_date_time']) );
			update_post_meta( $post_id, UTALII_MKP . 'booking_departure_date_time', strtotime($data['booking_departure_date_time']));
			update_post_meta( $post_id, UTALII_MKP . 'booking_no_of_adults', $data['booking_no_of_adults'] );
			update_post_meta( $post_id, UTALII_MKP . 'booking_no_of_child', $data['booking_no_of_child'] );
			update_post_meta( $post_id, UTALII_MKP . 'booking_customer_id', $data['booking_customer_id'] );*/
		}
		
		function render_details()
		{
			global $post;
			$post_id = $post->ID;
			/* core content */
			$data = array(
				'hotel_id'			=>	'',
				'rooms'				=>	'',
				'checkindate'		=>	'',
				'checkoutdate'		=>	'',
				'user_id'			=>	'',
				'total_nights'		=>	0,
				'tax'				=>	0,
				'subtotal'			=>	0,
				'additional_info'	=>	'',
				'grand_total'		=>	'',
				'tax_percentage'	=>	'',
				'tax_html'			=>	'',
				'subtotal_html'		=>	'',
				'grand_total_html'	=>	'',
				'payment_via'		=>	'',
				'payment_tx_id'		=>	''
			);
			
			$data['hotel_id']	=	get_post_meta( $post_id, UTALII_MKP . 'hotel_id', true );
			$data['rooms']		=	get_post_meta( $post_id, UTALII_MKP . 'rooms', true );
			$booking_time		=	(int) get_post_meta(  $post_id, UTALII_MKP . 'booking_time', true );
			
			$booking_arrival_date_time		=	get_post_meta( $post_id, UTALII_MKP . 'checkindate', true );
			if(!empty($booking_arrival_date_time))
			{
				$data['checkindate'] = $booking_arrival_date_time;
			}
			
			$booking_departure_date_time	=	get_post_meta( $post_id, UTALII_MKP . 'checkoutdate', true );
			if(!empty($booking_departure_date_time))
			{
				$data['checkoutdate'] = $booking_departure_date_time;
			}
			
			$booking_customer_id			=	(int) get_post_meta( $post_id, UTALII_MKP . 'user_id', true );
			if(!empty($booking_customer_id) && $booking_customer_id > 0)
			{
				$data['user_id'] = $booking_customer_id;
			}
						
			$total_nights			=	(int) get_post_meta( $post_id, UTALII_MKP . 'total_nights', true );
			if(!empty($total_nights) && $total_nights > 0)
			{
				$data['total_nights'] = $total_nights;
			}
			
			$tax			=	(float) get_post_meta( $post_id, UTALII_MKP . 'tax', true );
			if(!empty($tax) && $tax > 0)
			{
				$data['tax'] = $tax;
			}
			
			$subtotal			=	(int) get_post_meta( $post_id, UTALII_MKP . 'subtotal', true );
			if(!empty($subtotal) && $subtotal > 0)
			{
				$data['subtotal'] = $subtotal;
			}
			
			$additional_info			=	get_post_meta( $post_id, UTALII_MKP . 'additional_info', true );
			if(!empty($additional_info) && $additional_info != "")
			{
				$data['additional_info'] = $additional_info;
			}
			
			$grand_total			=	get_post_meta( $post_id, UTALII_MKP . 'grand_total', true );
			if(!empty($grand_total) && $grand_total != "")
			{
				$data['grand_total'] = $grand_total;
			}
			
			$tax_percentage			=	get_post_meta( $post_id, UTALII_MKP . 'tax_percentage', true );
			if(!empty($tax_percentage) && $tax_percentage != "")
			{
				$data['tax_percentage'] = $tax_percentage;
			}
			
			$tax_html			=	get_post_meta( $post_id, UTALII_MKP . 'tax_html', true );
			if(!empty($tax_html) && $tax_html != "")
			{
				$data['tax_html'] = $tax_html;
			}
			
			$subtotal_html			=	get_post_meta( $post_id, UTALII_MKP . 'subtotal_html', true );
			if(!empty($subtotal_html) && $subtotal_html != "")
			{
				$data['subtotal_html'] = $subtotal_html;
			}
			
			$grand_total_html			=	get_post_meta( $post_id, UTALII_MKP . 'grand_total_html', true );
			if(!empty($grand_total_html) && $grand_total_html != "")
			{
				$data['grand_total_html'] = $grand_total_html;
			}
			
			$payment_via			=	get_post_meta( $post_id, UTALII_MKP . 'payment_via', true );
			if(!empty($payment_via) && $payment_via != "")
			{
				$data['payment_via'] = $payment_via;
			}
			
			$payment_tx_id			=	get_post_meta( $post_id, UTALII_MKP . 'payment_tx_id', true );
			if(!empty($payment_tx_id) && $payment_tx_id != "")
			{
				$data['payment_tx_id'] = $payment_tx_id;
			}
			
			?>
            <style>
				.utalii_booking_summary{
					border: 1px solid #000;
					border-collapse: collapse;
				}
				.utalii_booking_summary tr,
				.utalii_booking_summary th,
				.utalii_booking_summary td{
					border: 1px solid #000;
					padding: 0;
					margin: 0;
				}
			</style>
			<table class="form-table">
				<tbody>
                	<tr>
						<th scope="row">
							<label for="booking_booking_id"><?php echo __( 'Booking ID', 'utalii' ); ?>: </label>
						</th>
						<td>
							<?php 
								echo  get_the_ID();
							?>
						</td>
					</tr>
                    
                    <tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'checkindate'; ?>"><?php echo __( 'Check-In', 'utalii' ); ?> </label>
						</th>
						<td>
							<?php echo date( 'F j, Y', strtotime( $data['checkindate'] ) ); ?>
						</td>
					</tr>
                    
                    <tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'checkoutdate'; ?>"><?php echo __( 'Check-Out', 'utalii' ); ?> </label>
						</th>
						<td>
							<?php echo date( 'F j, Y', strtotime( $data['checkoutdate'] ) ); ?>
						</td>
					</tr>
                    
                    <tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'total_nights'; ?>"><?php echo __( 'Total Night(s)', 'utalii' ); ?> </label>
						</th>
						<td>
							<?php echo $data['total_nights']; ?>
						</td>
					</tr>
                    
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'hotel'; ?>"><?php echo __( 'Hotel', 'utalii' ); ?>: </label>
						</th>
						<td>
								<?php 
								if( $hotels = UTALII_Misc::get_hotel($data['hotel_id']) ) 
								{
									echo  "<a href='".site_url()."/wp-admin/post.php?post=".$data['hotel_id']."&action=edit' target='_blank'>".$hotels['title']."</a>";
								} ?>
						</td>
					</tr>
                    <tr>
						<th scope="row" colspan="2">
							<label><?php echo __( 'Rooms', 'utalii' ); ?>: </label>
						</th>
                        
                        </tr>
                        <tr>
						<td colspan="2" class="remove_pading_cls">
                        	<table class="utalii_booking_summary">
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Adults</th>
                                    <th>Children</th>
                                    <th>Room(s)</th>
                                    <th>Charge/night</th>
                                    <th>Gross Total (KSh)</th>
                                </tr>
                                <?php $i=1; foreach( $data['rooms'] as $rid => $dt ) {?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    
                                    <td>
                                        <a href="<?php echo get_edit_post_link( $rid ); ?>" target="_blank"><?php echo get_the_title( $rid ); ?></a>
                                    </td>
                                    
                                    <td><?php echo $dt['adult_stay']; ?></td>
                                    
                                    <td><?php echo (int) $dt['children_stay']; ?></td>
                                    
                                    <td><?php echo $dt['rooms_required']; ?> 
                                    
                                    <td><?php echo $dt['charge_per_night_html']; ?></td>
                                    
                                    <td><?php echo $dt['total_charge_html']; ?></td>
                                    
                                </tr>
                                <?php $i++; } ?>
                                <tr>
                                    <td colspan="6" class="right_align_cls">
                                        <?php _e( 'Subtotal', 'utalii' ); ?>
                                    </td>
                                    <td>
                                        <?php echo $data['subtotal_html']; ?>
                                    </td>
                                </tr>
                                <?php if( !is_null( $data['tax'] ) ){ ?>
                                <tr>
                                    <td colspan="6" class="right_align_cls">
                                        <?php _e( 'Tax', 'utalii' ); ?>(<?php echo $data['tax_percentage']; ?>%)
                                    </td>
                                    <td>
                                        <?php echo $data['tax_html']; ?>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td colspan="6" class="right_align_cls">
                                        <?php _e( 'Grand total', 'utalii' ); ?> 
                                    </td>
                                    <td>
                                        <?php echo $data['grand_total_html']; ?>
                                    </td>
                                </tr>
                            </table>
						</td>
					</tr>
                    <tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'payment_via'; ?>"><?php echo __( 'Payment by', 'utalii' ); ?> </label>
						</th>
						<td>
							<?php echo $data['payment_via']; ?>
						</td>
					</tr>
                    
                    <tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'payment_tx_id'; ?>"><?php echo __( 'Transaction ID', 'utalii' ); ?> </label>
						</th>
						<td>
							<?php echo $data['payment_tx_id']; ?>
						</td>
					</tr>
                    
                    <tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'additional_info'; ?>"><?php echo __( 'Additional Request', 'utalii' ); ?> </label>
						</th>
						<td>
							<?php echo $data['additional_info']; ?>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'booking_time'; ?>"><?php echo __( 'Booking Time', 'utalii' ); ?> </label>
						</th>
						<td>
							<?php echo date('F j, Y H:i', $booking_time ); ?>
						</td> 
					</tr>
                    
                    <tr>
						<th scope="row">
							<label for="<?php echo UTALII_MKP . 'user_id'; ?>"><?php echo __( 'Customer', 'utalii' ); ?>: </label>
						</th>
						<td>
                        	<?php
								$user_id = $data['user_id'];
								$user_edit_url = get_edit_user_link( $user_id );
								$user_info = get_user_by("id", $user_id);
								if(!empty($user_info))
								{
									echo "<strong><a href='".$user_edit_url."'>".get_user_meta($user_id, "utalii_checkout_r_title", true).$user_info->data->display_name."</a></strong><br><br>";
									echo "<strong>Address:</strong> ".get_user_meta($user_id, "utalii_checkout_r_address", true)."<br><br>";
									echo "<strong>City:</strong> ".get_user_meta($user_id, "utalii_checkout_r_city", true)."<br><br>";
									echo "<strong>State:</strong> ".get_user_meta($user_id, "utalii_checkout_r_state", true)."<br><br>";
									echo "<strong>Postal Code:</strong> ".get_user_meta($user_id, "utalii_checkout_r_pcode", true)."<br><br>";
									echo "<strong>Country:</strong> ".get_user_meta($user_id, "utalii_checkout_r_country", true)."<br><br>";
									echo "<strong>Phone:</strong> ".get_user_meta($user_id, "utalii_checkout_r_phone", true)."<br><br>";
									echo "<strong>ID:</strong> ".get_user_meta($user_id, "utalii_checkout_r_id_type", true)."<br><br>";
									echo "<strong>ID Number:</strong> ".get_user_meta($user_id, "utalii_checkout_r_id_number", true)."<br><br>";
								}
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
		
		/* room details, end */
		
		
		/* adding custom columns, start */
		function add_custom_columns( $columns ) 
		{
			unset( $columns['comments'] );
			unset( $columns['date'] );			
			//$columns['title'] = __( 'Booking ID', 'utalii' );
			$new_columns = array(
				'client'		=>	__( 'Client', 'utalii' ),
				'hotel'			=>	__( 'Hotel', 'utalii' ),
				'check_in_out'	=>	__( 'Check-In/Out', 'utalii' ),
				'booked_room'	=>	__( 'Booked Room', 'utalii' ),
			);
			$updated_columns = array_merge( $columns, $new_columns );
			return $updated_columns;
		}
		
		function custom_column_content( $column, $post_id ) {
			switch ( $column ) {
				
				case 'client' :
					$data = (int)get_post_meta( $post_id , UTALII_MKP . 'user_id', true );
					$user_id = $data;
					$uobj = get_user_by('id', $user_id );
					$display_name = '';
					if( $uobj ){
						$display_name = $uobj->data->display_name;
					}
					
					if( !empty( $display_name ) ){
						$user_edit_url = get_edit_user_link( $user_id );
						echo '<a href="'.$user_edit_url.'">'.$display_name.'</a>';
					} else {
						echo '';
					}
					
					break;
					
				case 'hotel' :
					$hotel_id = (int)get_post_meta( $post_id , UTALII_MKP . 'hotel_id', true );
					
					if( !empty( $hotel_id ) ){
						$hotel_name = get_the_title( $hotel_id );
						$hotel_edit_url = get_edit_post_link( $hotel_id );
						
						echo '<a href="'.$hotel_edit_url.'">'.$hotel_name.'</a>';
					} else {
						echo '';
					}
					
					break;
					
				case 'check_in_out':
					$check_in = get_post_meta( $post_id, UTALII_MKP . 'checkindate', true );
					$check_in = trim($check_in);
					if( !empty( $check_in ) ){
						$check_in = date('F j, Y', strtotime( $check_in ) );
						echo $check_in;
					} else {
						echo '';
					}
					echo '<br />';
					$check_out = get_post_meta( $post_id, UTALII_MKP . 'checkoutdate', true );
					$check_out = trim($check_out);
					if( !empty( $check_out ) ){
						$check_out = date('F j, Y', strtotime( $check_out ) );
						echo $check_out;
					} else {
						echo '';
					}
				break;
				
				case 'check_out':
					$check_out = get_post_meta( $post_id, UTALII_MKP . 'checkoutdate', true );
					$check_out = trim($check_out);
					if( !empty( $check_out ) ){
						$check_out = date('F j, Y', strtotime( $check_out ) );
						echo $check_out;
					} else {
						echo '';
					}
				break;
				
				case 'booked_room':
				?>
				
				<?php
				$rooms = get_post_meta( $post_id, UTALII_MKP . 'rooms', true );
				if( !empty( $rooms ) ){
					?><ul><?php
					foreach( $rooms as $rid => $dt ) { ?>
					<li>
					[ <?php echo $dt['rooms_required']; ?> ]
					<a href="<?php echo get_edit_post_link( $rid ); ?>">
					<?php echo get_the_title( $rid ); ?>
					</a>
					<br />
					
					<span class="dashicons dashicons-admin-users"></span>
					<?php echo $dt['adult_stay']; ?>
					, 
					<span class="dashicons dashicons-smiley"></span>
					<?php echo (int) $dt['children_stay']; ?>
					<br /><br />
					</li>
					<?php } ?>
					</ul>
					<?php
				}
				break;
				
			}
		}
		
		function custom_column_sortable( $columns )
		{
			$columns['title']	=	'title';
			$columns['customer']	=	'customer';
			$columns['arrival_date']	=	'arrival_date';
			$columns['departure_date']	=	'departure_date';
			return $columns;
		}
		
		function edit_custom_post_load() 
		{
			add_filter( 'request', array( $this, 'sort_column_title' ) );
			add_filter( 'request', array( $this, 'sort_column_customer' ) );
			add_filter( 'request', array( $this, 'sort_column_arrival_date' ) );
			add_filter( 'request', array( $this, 'sort_column_departure_date' ) );
		}
		
		function sort_column_title( $vars ) 
		{
			if ( isset( $vars['post_type'] ) && $this->post_type == $vars['post_type'] ) 
			{
				if ( isset( $vars['orderby'] ) && 'title' == $vars['orderby'] ) {
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
UTALII_Booking::get_instance();