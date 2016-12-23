<?php
if(!defined('ABSPATH')) {exit;}
if( !class_exists('UTALII_Hotel_Search_SC') ){
	class UTALII_Hotel_Search_SC{
		private static $_instance = null;
		
		public static function get_instance(){
			if( is_null( self::$_instance ) ){
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		
		public function __construct(){
			add_shortcode( 'utalii_hotel_search', array( $this, 'hotel_search_sc' ) );
		}
		
		public function hotel_search_sc( $atts ){
			$atts	=	shortcode_atts(
							array(
							),
							$atts, 'utalii_hotel_search'
						);
			/* start */
			
			$time_stamp = time();
			$default_checkin = date('F j, Y', $time_stamp);
			$default_checkout = date('F j, Y', strtotime("+2 day", $time_stamp) );
			$locations = UTALII_Misc::get_real_locations( array('country' => false, 'state' => false) );
			$response_msg = "";
			$my_bookings_page_id = get_option( 'utalii_my_bookings_page_id', false );
			
			/* output buffer, start */
			ob_start();
			?>
			<div class="utalii_hotel_search_wrap">
			<?php if(isset($_GET) && $_GET['paypal_return'] == "1") { ?>
				<?php if( isset( $_GET['trace_id'] ) ){
					$trace_id = trim( $_GET['trace_id'] );
					$args = array(
						'post_type'		=>	UTALII_PREFIX . 'bookings',
						'post_status'	=>	'publish',
						'meta_query'	=>	array(
												array(
													'key'	=>	UTALII_MKP . "trace_id",
													'value'	=>	$trace_id,
												),
											),
						'fields'		=>	'ids',
					);
					$posts = get_posts( $args );
					if( !is_null( $posts ) || ( count( $posts ) == 1 ) ){
						$booking_id = $posts[0];
						$bid = $booking_id;
						$booking_time		=	(int) get_post_meta( $bid, UTALII_MKP . "booking_time", true );
						
						$hotel_id			=	get_post_meta( $bid, UTALII_MKP . "hotel_id", true );
						$checkindate		=	get_post_meta( $bid, UTALII_MKP . "checkindate", true );
						$checkoutdate		=	get_post_meta( $bid, UTALII_MKP . "checkoutdate", true );
						$total_nights		=	get_post_meta( $bid, UTALII_MKP . "total_nights", true );
						$rooms				=	get_post_meta( $bid, UTALII_MKP . "rooms", true );
						$tax				=	get_post_meta( $bid, UTALII_MKP . "tax", true );
						$subtotal			=	get_post_meta( $bid, UTALII_MKP . "subtotal", true );
						$grand_total		=	get_post_meta( $bid, UTALII_MKP . "grand_total", true );
						$tax_percentage		=	get_post_meta( $bid, UTALII_MKP . "tax_percentage", true );
						$tax_html			=	get_post_meta( $bid, UTALII_MKP . "tax_html", true );
						$subtotal_html		=	get_post_meta( $bid, UTALII_MKP . "subtotal_html", true );
						$grand_total_html	=	get_post_meta( $bid, UTALII_MKP . "grand_total_html", true );
						$additional_info	=	get_post_meta( $bid, UTALII_MKP . "additional_info", true );
						$payment_via		=	get_post_meta( $bid, UTALII_MKP . "payment_via", true );
						$payment_tx_id		=	get_post_meta( $bid, UTALII_MKP . "payment_tx_id", true );
						
						$bookings = array(
							'booking_id'		=>	$bid,
							'booking_time'		=>	$booking_time,
							'hotel_id'			=>	$hotel_id,
							'checkindate'		=>	$checkindate,
							'checkoutdate'		=>	$checkoutdate,
							'total_nights'		=>	$total_nights,
							'rooms'				=>	$rooms,
							'tax'				=>	$tax,
							'subtotal'			=>	$subtotal,
							'grand_total'		=>	$grand_total,
							'tax_percentage'	=>	$tax_percentage,
							'tax_html'			=>	$tax_html,
							'subtotal_html'		=>	$subtotal_html,
							'grand_total_html'	=>	$grand_total_html,
							'additional_info'	=>	$additional_info,
							'payment_via'		=>	$payment_via,
							'payment_tx_id'		=>	$payment_tx_id,
						);
						UTALII_Booking_Email::send_mail($bookings);
					}
					
				} ?>
			<h2>Your booking has been placed successfully. <a href="<?php echo get_permalink( $my_bookings_page_id ); ?>">Click here to view your bookings</a></h2>
			<p>We have sent an email to your registered email id having booking details also.</p>
			<?php unset( $_SESSION['utalii_booking'] ); ?>
			<?php } else if(isset($_POST['utalii_checkout_final_submit']))  { ?>
			<?php
					if($_POST['utalii_checkout_r_payment_type'] == "paypal")
					{
						$bookings	=	$_SESSION['utalii_booking'];
						$bookings['user_id'] = get_current_user_id();
						$total_payable_amount = $bookings['grand_total'];
						global $current_user;
						get_currentuserinfo();
						$user_id = get_current_user_id();
						$utalii_checkout_additional_request = str_replace("|", ",", $_POST['utalii_checkout_additional_request']);
						$additional_info = $_POST['utalii_checkout_additional_request'];
						update_user_meta($user_id, "temp_order_data", $bookings);
						
						$paypal_mode = '.sandbox';
						$set_paypal_mode = get_option( 'utalii_pyapal_mode', "sandbox" );
						if( "live" == $set_paypal_mode ){
							$paypal_mode = "";
						}
						$paypal_email = get_option( 'utalii_paypal_email', "" );
						
						?>
						<form action="https://www<?php echo $paypal_mode; ?>.paypal.com/cgi-bin/webscr" method="post" id="utalii_checkout_payment_form">
							<input type="hidden" name="cmd" value="_xclick">
							<input type="hidden" name="business" value="<?php echo $paypal_email; ?>">
							<input type="hidden" name="item_name" value="Room Booking">
							<input type="hidden" name="item_number" value="Room Booking">
							<input type="hidden" name="amount" value="<?php echo $total_payable_amount; ?>">
							<input type="hidden" name="currency_code" value="KSh">
							<input type="hidden" name="first_name" value="<?php echo $current_user->user_firstname; ?>">
							<input type="hidden" name="last_name" value="<?php echo $current_user->user_lastname; ?>">
							<input type="hidden" name="address1" value="<?php echo get_user_meta($user_id, "utalii_checkout_r_address", true); ?>">
							<input type="hidden" name="city" value="<?php echo get_user_meta($user_id, "utalii_checkout_r_city", true); ?>">
							<input type="hidden" name="state" value="<?php echo get_user_meta($user_id, "utalii_checkout_r_state", true); ?>">
							<input type="hidden" name="zip" value="<?php echo get_user_meta($user_id, "utalii_checkout_r_pcode", true); ?>">
							<input type="hidden" name="email" value="<?php echo $current_user->user_email; ?>">
							
							<?php
								$trace_id = uniqid();
								$return_url = add_query_arg( array( 'paypal_return' => '1', 'trace_id' => $trace_id ), get_the_permalink() );
								
								$cancel_return_url = add_query_arg( array( 'paypal_cancel_return' => '1' ), get_the_permalink() );
								
								$notify_url = add_query_arg( array( 'paypal_notify_return' => '1', 'trace_id' => $trace_id ), get_the_permalink() );
								
							?>
							
							<input type="hidden" name="return" value="<?php echo $return_url; ?>">
							<input type="hidden" name="cancel_return" value="<?php echo $cancel_return_url; ?>">
							<input type="hidden" name="notify_url" value="<?php echo $notify_url; ?>">
							
                            <input type="hidden" name="custom" value="<?php echo $user_id; ?>|<?php echo $utalii_checkout_additional_request; ?>">
						</form>
                        <script type="text/javascript">
							jQuery("#utalii_checkout_payment_form").submit();
						</script>
						<?php
						die();
					}
					if($_POST['utalii_checkout_r_payment_type'] == "manual")
					{
						$additional_info = $_POST['utalii_checkout_additional_request'];
						$bookings = $_SESSION['utalii_booking'];
						$bookings['booking_time'] = time();
						$booking_post = array(
									  'post_title'    	=> 	'New Booking',
									  'post_content'  	=> 	'-',
									  'post_status'   	=>	'publish',
									  'post_type'		=>	UTALII_PREFIX . 'bookings'
									);
						$booking_id = wp_insert_post( $booking_post );
						if($booking_id)
						{
							update_post_meta($booking_id, UTALII_MKP . "booking_id", $booking_id);
							update_post_meta($booking_id, UTALII_MKP . "hotel_id", $bookings['hotel_id']);
							update_post_meta($booking_id, UTALII_MKP . "booking_time", $bookings['booking_time']);
							update_post_meta($booking_id, UTALII_MKP . "user_id", get_current_user_id());
							update_post_meta($booking_id, UTALII_MKP . "checkindate", $bookings['checkindate']);
							update_post_meta($booking_id, UTALII_MKP . "checkoutdate", $bookings['checkoutdate']);
							update_post_meta($booking_id, UTALII_MKP . "total_nights", $bookings['total_nights']);
							update_post_meta($booking_id, UTALII_MKP . "rooms", $bookings['rooms']);
							update_post_meta($booking_id, UTALII_MKP . "tax", $bookings['tax']);
							update_post_meta($booking_id, UTALII_MKP . "subtotal", $bookings['subtotal']);
							update_post_meta($booking_id, UTALII_MKP . "grand_total", $bookings['grand_total']);
							update_post_meta($booking_id, UTALII_MKP . "tax_percentage", $bookings['tax_percentage']);
							update_post_meta($booking_id, UTALII_MKP . "tax_html", $bookings['tax_html']);
							update_post_meta($booking_id, UTALII_MKP . "subtotal_html", $bookings['subtotal_html']);
							update_post_meta($booking_id, UTALII_MKP . "grand_total_html", $bookings['grand_total_html']);
							update_post_meta($booking_id, UTALII_MKP . "additional_info", $additional_info);
							update_post_meta($booking_id, UTALII_MKP . "payment_via", "Payment on arrival");
							update_post_meta($booking_id, UTALII_MKP . "payment_tx_id", '');
							$update_booking_post = array(
										  'ID'           => $booking_id,
										  'post_title'   => $booking_id
									  );
							wp_update_post( $update_booking_post );
							$response_msg = "Order placed successfully.";
							$hso = new UTALII_Hotel_Search();
							$hso->book_rooms_on( $bookings['rooms'], $bookings['checkindate'], $bookings['checkoutdate'] );
							$bookings['payment_via'] = "Payment on arrival";
							$bookings['payment_tx_id'] = "N/A";
							$bookings['additional_info'] = $additional_info;
							UTALII_Booking_Email::send_mail($bookings);
							?>
							<h2>Your booking has been placed successfully. <a href="<?php echo get_permalink( $my_bookings_page_id ); ?>">Click here to view your bookings</a></h2>
							<p>We have sent an email to your registered email id having booking details also.</p>
							<?php
							unset( $_SESSION['utalii_booking'] );
						}
					}
					?>
				
			<?php } else if( $_POST['utalii_make_user_login'] && isset( $_SESSION['utalii_booking'] ) ) { ?>
				<?php $booking = $_SESSION['utalii_booking']; ?>
				
					<h2><u>Review Booking Summary</u></h2>
					
						<b>Hotel :</b>
						<a href="<?php echo get_permalink( $booking['booking_id'] ); ?>">
							<?php echo get_the_title( $booking['booking_id'] ); ?>
						</a>
						<br />
						<b>Check-In:</b> <?php echo date( 'F j, Y', strtotime( $booking['checkindate'] ) ); ?>
						<br />
						<b>Check-Out:</b> <?php echo date( 'F j, Y', strtotime( $booking['checkoutdate'] ) ); ?>
						<br />
						<b>Total Night(s):</b> <?php echo $booking['total_nights']; ?>
						<br /><b>Room Details:</b><br />
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
						<table class="utalii_booking_summary sdas">
							<tr>
								<th>#</th>
								<th>Type</th>
								<th>Adults</th>
								<th>Children</th>
								<th>Room(s)</th>
								<th>Charge/night</th>
								<th>Gross Total (KSh)</th>
							</tr>
							<?php $i=1; foreach( $booking['rooms'] as $rid => $dt ) {?>
							<tr>
								<td><?php echo $i; ?></td>
								
								<td>
									<a href="<?php echo get_permalink( $rid ); ?>"><?php echo get_the_title( $rid ); ?></a>
								</td>
								
								<td><?php echo $dt['adult_stay']; ?></td>
								
								<td><?php echo (int) $dt['children_stay']; ?></td>
								
								<td><?php echo $dt['rooms_required']; ?> 
								
								<td><?php echo $dt['charge_per_night_html']; ?></td>
								
								<td><?php echo $dt['total_charge_html']; ?></td>
								
							</tr>
							<?php $i++; } ?>
							<tr>
								<td colspan="6">
									<?php _e( 'Subtotal', 'utalii' ); ?>
								</td>
								<td>
									<?php echo $booking['subtotal_html']; ?>
								</td>
							</tr>
							<?php if( !is_null( $booking['tax'] ) ){ ?>
							<tr>
								<td colspan="6">
									<?php _e( 'Tax', 'utalii' ); ?>(<?php echo $booking['tax_percentage']; ?>%)
								</td>
								<td>
									<?php echo $booking['tax_html']; ?>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<td colspan="6">
									<?php _e( 'Grand total', 'utalii' ); ?> 
								</td>
								<td>
									<?php echo $booking['grand_total_html']; ?>
								</td>
							</tr>
						</table>
						<form name="utalii_make_user_login" id="utalii_make_user_login" action="" method="POST">
							<input type="hidden" name="utalii_make_user_login" value="1" />
						</form>
						<?php 
						////////////////////////////
						if(is_user_logged_in()){
							$is_poa_enable = get_option( 'utalii_poa_enable', "no" );
							$is_paypal_enable = get_option( 'utalii_pyapal_enable', "no" );
						?>
						
                        <div class="utalii_checkout_form" id="utalii_checkout_form">
						<?php if( ( "yes" == $is_poa_enable ) || ( "yes" == $is_paypal_enable ) ){ ?>
                        	<form action="<?php the_permalink(); ?>" method="post" id="utalii_checkout_checkout_frm">
								<h3>Choose Payment Method</h3>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_password">Payment by:</label>
                                    <p class="payment-cls-radio_cls">
									
									<?php if( "yes" == $is_paypal_enable ) { ?>
                                    <input type="radio" name="utalii_checkout_r_payment_type" value="paypal" class="required" id="utalii_checkout_r_payment_type_paypal" autocomplete="off" />
									<label for="utalii_checkout_r_payment_type_paypal">PayPal</label>
									<br/>
                                    <?php } ?>
									
									<?php if( "yes" == $is_poa_enable ) { ?>
									<input type="radio" name="utalii_checkout_r_payment_type" value="manual" class="required" id="utalii_checkout_r_payment_type_manual" autocomplete="off" />
									<label for="utalii_checkout_r_payment_type_manual">Manual : Pay on Arrival</label>
                                    <br/>
									<?php } ?>
                                    </p>
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_additional_request">Any additional requests:</label>
                                    <textarea name="utalii_checkout_additional_request" id="utalii_checkout_additional_request"></textarea>
                                </div>
                                <div class="utalii_form_row button_row">
									<input type="submit" name="utalii_checkout_final_submit" id="utalii_checkout_final_submit" value="Checkout" />
                                    <?php
									if(isset($response_msg) && $response_msg != "")
									{
										echo $response_msg;
									}
									?>
								</div>
                            </form>
						<?php } else { ?>
						<h1>Sorry!! No pyament method is available</h1>
						<?php } ?>
                    	</div>
                        <?php
					}
					else
					{
						?>
						<div class="utalii_checkout_login" id="utalii_checkout_login">
							<form action="" method="post" id="utalii_checkout_login_frm">
								<h3>Login</h3>
								<div class="utalii_form_row">
									<label for="utalii_checkout_l_username">Username:</label>
									<input type="text" name="utalii_checkout_login_username" id="utalii_checkout_login_username" required="required" maxlength="100" />
								</div>
								<div class="utalii_form_row">
									<label for="utalii_checkout_login_password">Password:</label>
									<input type="password" name="utalii_checkout_login_password" id="utalii_checkout_login_password" required="required" maxlength="50" />
								</div>
								<div class="utalii_form_row button_row">
									<input type="submit" name="utalii_checkout_login_button" id="utalii_checkout_login_button" value="Login" />
									<img src="<?php echo UTALII_PLUGIN_URL."/assets/img/utalii-ajax-loader.gif"; ?>" id="utalii_checkout_l_loader" style="display:none;"/>
								</div>
								<div class="utalii_form_row" id="Login_error">
								</div>
							</form>
						</div>
						<div class="utalii_checkout_registration" id="utalii_checkout_registration">
                            <form action="" method="post" id="utalii_checkout_registration_frm">
                                <h3>New Registration</h3>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_title">Title:</label>
                                    <select name="utalii_checkout_r_title" id="utalii_checkout_r_title">
                                        <option value="Mr.">Mr.</option>
                                        <option value="Ms.">Ms.</option>
                                        <option value="Mrs.">Mrs.</option>
                                        <option value="Miss.">Miss.</option>
                                        <option value="Dr.">Dr.</option>
                                        <option value="Prof.">Prof.</option>
                                    </select>
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_f_name">First Name:</label>
                                    <input type="text" name="utalii_checkout_r_f_name" id="utalii_checkout_r_f_name" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_l_name">Last Name:</label>
                                    <input type="text" name="utalii_checkout_r_l_name" id="utalii_checkout_r_l_name" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_address">Address:</label>
                                    <input type="text" name="utalii_checkout_r_address" id="utalii_checkout_r_address" required="required" maxlength="200" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_city">City:</label>
                                    <input type="text" name="utalii_checkout_r_city" id="utalii_checkout_r_city" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_state">State:</label>
                                    <input type="text" name="utalii_checkout_r_state" id="utalii_checkout_r_state" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_pcode">Postal Code:</label>
                                    <input type="text" name="utalii_checkout_r_pcode" id="utalii_checkout_r_pcode" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_country">Country:</label>
                                    <input type="text" name="utalii_checkout_r_country" id="utalii_checkout_r_country" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_phone">Phone:</label>
                                    <input type="text" name="utalii_checkout_r_phone" id="utalii_checkout_r_phone" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_id_type">Identity Type:</label>
                                    <input type="text" name="utalii_checkout_r_id_type" id="utalii_checkout_r_id_type" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_id_number">Identity Number:</label>
                                    <input type="text" name="utalii_checkout_r_id_number" id="utalii_checkout_r_id_number" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_id_number">Email:</label>
                                    <input type="email" name="utalii_checkout_r_email" id="utalii_checkout_r_email" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_password">Password:</label>
                                    <input type="password" name="utalii_checkout_r_password" id="utalii_checkout_r_password" required="required" maxlength="20" />
                                </div>
                                <div class="utalii_form_row button_row">
                                    <input type="submit" name="utalii_checkout_register_button" id="utalii_checkout_register_button" value="Register" />
                                    <img src="<?php echo UTALII_PLUGIN_URL."/assets/img/utalii-ajax-loader.gif"; ?>" id="utalii_checkout_r_loader" style="display:none;"/>
                                </div>
                                <div class="utalii_form_row" id="register_error">
                                </div>
                            </form>
						</div>
						<?php
					}
						////////////////////////////
				?>	
				<?php } else if( isset( $_POST['submit_booked_room'] ) ){ ?>
				
				<?php
					$roomids = $_POST['roomid'];
					$data = array(
						'checkindate'	=>	null,
						'checkoutdate'	=>	null,
						'hotel_id'		=>	null,
						'roomdata'		=>	array(),
					);
					if( !empty($roomids) ){
						$hotel_id = array();
						foreach( $roomids as $roomid ) {
							$key = 'rooms_required_input_' . $roomid;
							if( isset( $_POST[$key] ) ){
								if( !empty( $_POST[$key] ) ){
									$rooms_required = $_POST[$key];
									$data['roomdata'][$roomid]['rooms_required'] = $rooms_required;
								}
							}
							
							$key = 'utalii_adult_stay_' . $roomid;
							if( isset( $_POST[$key] ) ){
								if( !empty( $_POST[$key] ) ){
									$adult_stay = $_POST[$key];
									$data['roomdata'][$roomid]['adult_stay'] = $adult_stay;
									$hid = (int) get_post_meta( $roomid, UTALII_MKP . 'hotel_id', true );
									$hotel_id[] = $hid;
								}
							}
							$key = 'utalii_children_stay_' . $roomid;
							if( !empty( $_POST[$key] ) ){
								$children_stay = $_POST[$key];
								$data['roomdata'][$roomid]['children_stay'] = $children_stay;
								$hid = (int)get_post_meta( $roomid, UTALII_MKP . 'hotel_id', true );
								$hotel_id[] = $hid;
							}
						}
					}
					
					if( isset( $_POST['checkindate'] ) ){
						$data['checkindate'] = $_POST['checkindate'];
					}
					if( isset( $_POST['checkoutdate'] ) ){
						$data['checkoutdate'] = $_POST['checkoutdate'];
					}
					
					$h_id = $hotel_id[0];
					$flag = true;
					for( $i=0; $i<count($htoel_id); $i++){
						if( $h_id != $hotel_id[$i] || empty( $hotel_id[$i] ) ){
							$flag = false;
							break;
						}
					}
					if( $flag ){
						$data['hotel_id'] = $hid;
					}
					$flag = true;
					foreach( $data as $dt ){
						if( is_null( $dt ) || empty( $dt ) ){
							$flag = false;
							break;
						}
					}
					/*
					$booking = array(
						'booking_id'	=>	null,
						'hotel_id'		=>	null,
						'checkindate'	=>	null,
						'checkoutdate'	=>	null,
						'total_nights'	=>	null,
						'rooms'			=>	array(
												'$id'	=>	array(
																'adult_stay'	=>	null,
																'children_stay'	=>	null,
																'charge_per_night'	=>	null,
																'charge_per_night_html'	=>	null,
																'total_charge'	=>	null,
																'total_charge_html'	=>	null,
															),
											),
						'subtotal'		=>	null,
						'tax'			=>	null,
						'grand_total'	=>	null,
					);
					*/ 
				?>
				<div class="booking_summary"><?php
					if( $flag ){
						$total_nights = strtotime( $data['checkoutdate'] ) - strtotime( $data['checkindate'] );
						$total_nights = $total_nights / 86400;
						$subtotal = 0;
						
						$booking['booking_id'] = null;
						$booking['user_id'] = null;
						$booking['hotel_id'] = $data['hotel_id'];
						$booking['checkindate'] = $data['checkindate'];
						$booking['checkoutdate'] = $data['checkoutdate'];
						$booking['total_nights'] = $total_nights;
						
						foreach( $data['roomdata'] as $rid => $dt ){
							$rooms_required = (int) $dt['rooms_required'];
							$adult_stay = (int) $dt['adult_stay'];
							$children_stay = (int) $dt['children_stay'];
							$charge_per_night = (float) get_post_meta( $rid, UTALII_MKP . 'charge_per_night', true );
							$total_charge = ( $total_nights * $charge_per_night * $rooms_required );
							$total_charge = round( $total_charge, 2 );
							$subtotal = ( $subtotal + $total_charge );
							$subtotal = round( $subtotal, 2 );
							
							
							$charge_per_night_html = utalii_money_format($charge_per_night );
							$total_charge_html = utalii_money_format($total_charge );
							
							$booking['rooms'][$rid] = array(
								'rooms_required'		=>	$rooms_required,
								'adult_stay'			=>	$adult_stay,
								'children_stay'			=>	$children_stay,
								'charge_per_night'		=>	$charge_per_night,
								'charge_per_night_html'	=>	$charge_per_night_html,
								'total_charge'			=>	$total_charge,
								'total_charge_html'		=>	$total_charge_html,
							);
						}
						
						$booking['tax'] = null;
						$booking['subtotal'] = $subtotal;
						$booking['grand_total'] = $subtotal;
						$is_tax_apply = get_option( 'utalii_tax_apply', "no" );
						if( "yes" == $is_tax_apply ){
							$tax = get_option( 'utalii_tax', '9' );
							if( is_numeric( $tax ) ){
								$booking['tax_percentage'] = $tax;
								$tax = ( $tax / 100) * $subtotal;
								$tax = round( $tax, 2);
								$booking['tax'] = $tax;
								$booking['tax_html'] = utalii_money_format($booking['tax'] );
								$booking['grand_total'] += $tax;
								$booking['grand_total'] =  round( $booking['grand_total'], 2 );
							}
						}
						
						$booking['subtotal_html'] = utalii_money_format($booking['subtotal'] );
						$booking['grand_total_html'] = utalii_money_format($booking['grand_total'] );
						$_SESSION['utalii_booking'] = $booking;
					?>
						<h2>Review Booking Summary ( <a href="<?php global $post; echo get_permalink($post->ID); ?>">Reset Search</a> )</h2>
					
						<b>Hotel :</b>
						<a href="<?php echo get_permalink( $booking['hotel_id'] ); ?>">
							<?php echo get_the_title( $booking['hotel_id'] );?>
						</a>
						<br />
						<b>Check-In:</b> <?php echo date( 'F j, Y', strtotime( $booking['checkindate'] ) ); ?>
						<br />
						<b>Check-Out:</b> <?php echo date( 'F j, Y', strtotime( $booking['checkoutdate'] ) ); ?>
						<br />
						<b>Total Night(s):</b> <?php echo $booking['total_nights']; ?>
						<br /><b>Room Details:</b><br />
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
						<table class="utalii_booking_summary">
							<tr class="title">
								<th class="spa">#</th>
								<th>Type</th>
								<th>Adults</th>
								<th>Children</th>
								<th>Room(s)</th>
								<th>Charge/night</th>
								<th>Gross Total (KSh)</th>
							</tr>
							<?php $i=1; foreach( $booking['rooms'] as $rid => $dt ) {?>
							<tr>
								<td><?php echo $i; ?></td>
								
								<td>
									<a href="<?php echo get_permalink( $rid ); ?>"><?php echo get_the_title( $rid ); ?></a>
								</td>
								
								<td><?php echo $dt['adult_stay']; ?></td>
								
								<td><?php echo (int) $dt['children_stay']; ?></td>
								
								<td><?php echo $dt['rooms_required']; ?> 
								
								<td><?php echo $dt['charge_per_night_html']; ?></td>
								
								<td><?php echo $dt['total_charge_html']; ?></td>
								
							</tr>
							<?php $i++; } ?>
							<tr>
								<td colspan="6">
									<?php _e( 'Subtotal', 'utalii' ); ?>
								</td>
								<td>
									<?php echo $booking['subtotal_html']; ?>
								</td>
							</tr>
							<?php if( !is_null( $booking['tax'] ) ){ ?>
							<tr>
								<td colspan="6">
									<?php _e( 'Tax', 'utalii' ); ?>(<?php echo $booking['tax_percentage']; ?>%)
								</td>
								<td>
									<?php echo $booking['tax_html']; ?>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<td colspan="6">
									<?php _e( 'Grand total', 'utalii' ); ?> 
								</td>
								<td>
									<?php echo $booking['grand_total_html']; ?>
								</td>
							</tr>
						</table>
						<form name="utalii_make_user_login" id="utalii_make_user_login" action="" method="POST">
							<input type="hidden" name="utalii_make_user_login" value="1" />
						</form>
						<?php 
						////////////////////////////
						if(is_user_logged_in()){
							$is_poa_enable = get_option( 'utalii_poa_enable', "no" );
							$is_paypal_enable = get_option( 'utalii_pyapal_enable', "no" );
						?>
                        <div class="utalii_checkout_form" id="utalii_checkout_form">
						<?php if( ( "yes" == $is_poa_enable ) || ( "yes" == $is_paypal_enable ) ){ ?>
                        	<form action="<?php the_permalink(); ?>" method="post" id="utalii_checkout_checkout_frm">
								<h3>Choose Payment Method</h3>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_password">Payment by:</label>
									
									<p class="payment-cls-radio_cls">
									
									<?php if( "yes" == $is_paypal_enable ) { ?>
                                    <input type="radio" name="utalii_checkout_r_payment_type" value="paypal" class="required" id="utalii_checkout_r_payment_type_paypal" autocomplete="off" />
									<label for="utalii_checkout_r_payment_type_paypal">PayPal</label>
									<br/>
                                    <?php } ?>
									
									<?php if( "yes" == $is_poa_enable ) { ?>
									<input type="radio" name="utalii_checkout_r_payment_type" value="manual" class="required" id="utalii_checkout_r_payment_type_manual" autocomplete="off" />
									<label for="utalii_checkout_r_payment_type_manual">Manual : Pay on Arrival</label>
                                    <br/>
									<?php } ?>
                                    </p>
									
                                    </p>
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_additional_request">Any additional requests:</label>
                                    <textarea name="utalii_checkout_additional_request" id="utalii_checkout_additional_request"></textarea>
                                </div>
                                <div class="utalii_form_row button_row">
									<input type="submit" name="utalii_checkout_final_submit" id="utalii_checkout_final_submit" value="Checkout" />
                                    <?php
									if(isset($response_msg) && $response_msg != "")
									{
										echo $response_msg;
									}
									?>
								</div>
                            </form>
							<?php } else { ?>
							<h1>Sorry!! No pyament method is available</h1>
						<?php } ?>
                    	</div>
                        <?php
					}
					else
					{
						?>
						<div class="utalii_checkout_login" id="utalii_checkout_login">
							<form action="" method="post" id="utalii_checkout_login_frm">
								<h3>Login</h3>
								<div class="utalii_form_row">
									<label for="utalii_checkout_l_username">Username:</label>
									<input type="text" name="utalii_checkout_login_username" id="utalii_checkout_login_username" required="required" maxlength="100" />
								</div>
								<div class="utalii_form_row">
									<label for="utalii_checkout_login_password">Password:</label>
									<input type="password" name="utalii_checkout_login_password" id="utalii_checkout_login_password" required="required" maxlength="50" />
								</div>
								<div class="utalii_form_row button_row">
									<input type="submit" name="utalii_checkout_login_button" id="utalii_checkout_login_button" value="Login" />
									<img src="<?php echo UTALII_PLUGIN_URL."/assets/img/utalii-ajax-loader.gif"; ?>" id="utalii_checkout_l_loader" style="display:none;"/>
								</div>
								<div class="utalii_form_row" id="Login_error">
								</div>
							</form>
						</div>
						<div class="utalii_checkout_registration" id="utalii_checkout_registration">
                            <form action="" method="post" id="utalii_checkout_registration_frm">
                                <h3>New Registration</h3>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_title">Title:</label>
                                    <select name="utalii_checkout_r_title" id="utalii_checkout_r_title">
                                        <option value="Mr.">Mr.</option>
                                        <option value="Ms.">Ms.</option>
                                        <option value="Mrs.">Mrs.</option>
                                        <option value="Miss.">Miss.</option>
                                        <option value="Dr.">Dr.</option>
                                        <option value="Prof.">Prof.</option>
                                    </select>
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_f_name">First Name:</label>
                                    <input type="text" name="utalii_checkout_r_f_name" id="utalii_checkout_r_f_name" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_l_name">Last Name:</label>
                                    <input type="text" name="utalii_checkout_r_l_name" id="utalii_checkout_r_l_name" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_address">Address:</label>
                                    <input type="text" name="utalii_checkout_r_address" id="utalii_checkout_r_address" required="required" maxlength="200" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_city">City:</label>
                                    <input type="text" name="utalii_checkout_r_city" id="utalii_checkout_r_city" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_state">State:</label>
                                    <input type="text" name="utalii_checkout_r_state" id="utalii_checkout_r_state" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_pcode">Postal Code:</label>
                                    <input type="text" name="utalii_checkout_r_pcode" id="utalii_checkout_r_pcode" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_country">Country:</label>
                                    <input type="text" name="utalii_checkout_r_country" id="utalii_checkout_r_country" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_phone">Phone:</label>
                                    <input type="text" name="utalii_checkout_r_phone" id="utalii_checkout_r_phone" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_id_type">Identity Type:</label>
                                    <input type="text" name="utalii_checkout_r_id_type" id="utalii_checkout_r_id_type" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_id_number">Identity Number:</label>
                                    <input type="text" name="utalii_checkout_r_id_number" id="utalii_checkout_r_id_number" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_id_number">Email:</label>
                                    <input type="email" name="utalii_checkout_r_email" id="utalii_checkout_r_email" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_password">Password:</label>
                                    <input type="password" name="utalii_checkout_r_password" id="utalii_checkout_r_password" required="required" maxlength="20" />
                                </div>
                                <div class="utalii_form_row button_row">
                                    <input type="submit" name="utalii_checkout_register_button" id="utalii_checkout_register_button" value="Register" />
                                    <img src="<?php echo UTALII_PLUGIN_URL."/assets/img/utalii-ajax-loader.gif"; ?>" id="utalii_checkout_r_loader" style="display:none;"/>
                                </div>
                                <div class="utalii_form_row" id="register_error">
                                </div>
                            </form>
						</div>
						<?php
					}
						////////////////////////////
					
					} else { ?>
						<h2>You have not selected any room</h2><?php
					} ?>
				</div>
				<?php } else if( isset( $_POST['utalii_hotel_search_submit_step1'] ) ){ ?>
				<?php
					$location_id	=	trim( $_POST['location'] );
					$hotel_id		=	trim( $_POST['hotel_id'] );
					$checkindate	=	trim( $_POST['checkindate'] );
					$checkindate	=	date('Y-m-d', strtotime( $checkindate ) );
					$checkoutdate	=	trim( $_POST['checkoutdate'] );
					$checkoutdate	=	date('Y-m-d', strtotime( $checkoutdate ) );
					$hso = new UTALII_Hotel_Search();
					$rooms = $hso->get_rooms_by_hotel(
								array( $hotel_id ),
								array( 'orderby' => 'title', 'order' => 'ASC' )
							);
				?>
				<h2>Choose Rooms ( <a href="<?php global $post; echo get_permalink($post->ID); ?>">Reset Search</a> )</h2>
				<b>Hotel :</b>
				<a href="<?php echo get_permalink( $hotel_id ); ?>">
					<?php echo get_the_title( $hotel_id);?>
				</a>
				<br />
				<b>Check-In:</b> <?php echo date( 'F j, Y', strtotime( $checkindate ) ); ?>
				<br />
				<b>Check-Out:</b> <?php echo date( 'F j, Y', strtotime( $checkoutdate ) ); ?>
				<br />
				<br />
				
				<?php if( $rooms ) { ?>
					<form action="" method="POST" id="utalii_search_hotel_form">
					<input type="hidden" name="checkindate" value="<?php echo $checkindate; ?>" id="utalii_checkindate" />
					<input type="hidden" name="checkoutdate" value="<?php echo $checkoutdate; ?>" id="utalii_checkoutdate" />
					<?php foreach( $rooms as $r ) { $room = utalii_get_room_detail( $r ); ?>
					<input type="hidden" name="rooms_required_input_<?php echo $room['id']; ?>" value="" id="rooms_required_input_<?php echo $room['id']; ?>" />
					<!-- room data start -->
					<div class="utalii_wrap_inner_content">
						<div class="main_image_cls">
							<?php if( !is_null( $room['images'] ) ){ ?>
							<div class="container">
							  <div class="slider">
								<ul>
								<?php foreach( $room['images'] as $img_id => $img ) { ?>
									<li>
										<img src="<?php echo $img['thumbnail']['url']; ?>" class="gallery_image_cls" />
									</li>
								<?php } ?>
								</ul>
								<button class="prev arrow-cls"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/arrow-left.png"; ?>"/></button>
								<button class="next arrow-cls"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/arrow-right.png"; ?>"/></button>
							  </div>
							</div>
							
							
							<div class="full_size_slider" style="cursor:pointer">
								<span class="full_size_symbol"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/view_full_screen.png"; ?>"/></span>
								<div class="container_large" style="display:none">
									<div class="slider_large">
										<ul>
											<?php foreach( $room['images'] as $img_id => $img ){ ?>
													<li><img src="<?= $img['full']['url']; ?>" class="gallery_image_cls" /></li>
											<?php } ?>
										</ul>
										<button class="prev_large arrow-cls"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/arrow-left.png"; ?>"/></button>
								<button class="next_large arrow-cls"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/arrow-right.png"; ?>"/></button>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
						<div class="detail_right_section_cls">
                        	<div id="short_dis_cls_outer1">
                                    <h1 class="page_title_cls">
                                        <a href="<?php echo get_permalink( $room['id'] ); ?>" title="Room Type: <?php echo get_the_title( $room['id'] ); ?>">
                                            <?php echo $room['title']; ?>
                                        </a>
                                    </h1>
                                    <h4 class="address_cls_dtls">
                                        <ul>
                                            <li>
                                                in <a href="<?php echo get_permalink( $room['hotel_id'] ); ?>" title="Hotel Name: <?php echo get_the_title( $room['hotel_id'] ); ?>">
                                                    <?php echo get_the_title( $room['hotel_id'] ); ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </h4>
                                    <div class="short_dis_cls"><?php echo $room['short_description']; ?></div>
                            </div>
                            
							
						</div>
                        <div id="short_dis_cls_outer2">       
                                    <ul class="utalii_availability_box" data-roomid="<?php echo $room['id']; ?>">
                                        <li>
                                            <span class="dv_inline">Max. Adult Occupancy: </span><?php echo $room['max_adult_occupancy']; ?>
                                        </li>
                                        <li>
                                            <span class="dv_inline">Max. Children Occupancy: </span><?php echo $room['max_children_occupancy']; ?>
                                        </li>
                                        <li>
                                            <span class="dv_inline">Adults to stay: </span>
                                            <input type="number" name="utalii_adult_stay_<?php echo $room['id']; ?>" class="utalii_adult_stay" autocomplete="off" min="0" step="1" value="0" data-roomid="<?php echo $room['id']; ?>" id="utalii_adult_stay_<?php echo $room['id']; ?>" />
                                        </li>
                                        <li>
                                            <span class="dv_inline">Children to stay: </span>
                                            <input type="number" name="utalii_children_stay_<?php echo $room['id']; ?>" class="utalii_children_stay" autocomplete="off" min="0" step="1" value="0" data-roomid="<?php echo $room['id']; ?>" <?php if( empty($room['max_children_occupancy']) || ($room['max_children_occupancy'] < 1 ) ){ echo 'readonly="readonly" disabled="disabled"'; } ?> id="utalii_children_stay_<?php echo $room['id']; ?>" />
                                        </li>
                                        <li>
                                            <span class="dv_inline">Rooms required: </span><span class="utalii_rooms_required" id="utalii_rooms_required_<?php echo $room['id']; ?>">0</span>
                                            <img src="<?php echo utalii_ajax_loader_url(); ?>" class="hs_hotel_ajax_loader" style="display:none" id="hs_hotel_ajax_loader_<?php echo $room['id']; ?>" />
                                        </li>
                                        <li>
                                            <span class="dv_inline"><label for="select_booking_<?php echo $room['id']; ?>">Select Booking</label></span>
                                            <input type="checkbox" name="roomid[]" value="<?php echo $room['id']; ?>" id="select_booking_<?php echo $room['id']; ?>" class="select_booking" disabled="disabled" autocomplete="off" />
                                        </li>
                                    </ul>
                            
                            </div>
					</div>
					<!-- room data end -->
					<?php } ?>
				<div>
					<input type="submit" name="submit_booked_room" value="Book Rooms" id="" class="" />
				</div>
				</form>
				<?php } else { ?>
					<h2>Sorry!! No rooms available in the searching criteria</h2>
				<?php } ?>
				
				<?php } else { ?>
				<h2>Search Rooms</h2>
				<?php if( $locations ) { ?>
				<form action="" method="POST" id="utalii_search_hotel_form">
					<input type="hidden" name="action" value="utaliihotelsearchsubmit" />
					<input type="hidden" name="utalii_post_id" value="<?php global $post; echo $post->ID; ?>">
					
					<ul class="hotel-search-form-cls section-1">
						<li class="left-input">
							<label for="utalii_hotel_search_location">Location*:</label>
							<select name="location" required="required" id="utalii_hotel_search_location" autocomplete="off">
								<option value="">-Select Location-</option>
								<?php foreach( $locations as $loc ) { ?>
								<?php foreach( $loc as $l ){?>
								<option value="<?php echo $l['id']; ?>"><?php echo $l['title']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</li>
						<li class="right-input">
							<label for="utalii_hotel_search_hotel">
							Hotel*:
							<img src="<?php echo utalii_ajax_loader_url(); ?>" class="hs_hotel_ajax_loader" />
							</label>
							<select name="hotel_id" disabled="disabled" id="utalii_hotel_search_hotel" autocomplete="off" required="required">
								<option value="">-Choose Hotel-</option>
							</select>
						</li>
						<li class="left-input last">
							<label for="utalii_hotel_search_checkindate">Check-In*:</label>
							<input type="text" id="utalii_hotel_search_checkindate" class="checkindate" name="checkindate" required="required" readonly="readonly" autocomplete="off" value="<?php echo $default_checkin; ?>" autocomplete="off" />
						</li>
						<li class="right-input">
							<label for="utalii_hotel_search_checkoutdate">Check-Out*:</label>
							<input type="text" id="utalii_hotel_search_checkoutdate" class="checkoutdate" name="checkoutdate" required="required" readonly="readonly" autocomplete="off" value="<?php echo $default_checkout; ?>" autocomplete="off" />
						</li>
						<li class="so_cls">
							<input type="submit" name="utalii_hotel_search_submit_step1" id="utalii_hotel_search_submit" class="utalii_hotel_search_submit" value="Go" />
						</li>
					</ul>
				</form>
				<?php } else { ?>
				<h2>Sorry!! Currently not hotel available</h2>
				<?php } ?>
				<?php } ?>
				
                
                
                
                <?php
				
				if(isset($_GET) && $_GET['action'] == "l_r_c")
				{
					if(is_user_logged_in())
					{
						?>
                        <div class="utalii_checkout_form" id="utalii_checkout_form">
                        	<form action="<?php the_permalink(); ?>" method="post" id="utalii_checkout_checkout_frm">
								<h3>Additional info</h3>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_password">Payment by:</label>
                                    <p class="payment-cls-radio_cls">
                                    <input type="radio" name="utalii_checkout_r_payment_type" value="paypal" class="required"> PayPal<br/>
                                    <input type="radio" name="utalii_checkout_r_payment_type" value="manual" class="required"> Manual : Pay on Arrival
                                    <br/>
                                    </p>
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_additional_request">Any additional requests:</label>
                                    <textarea name="utalii_checkout_additional_request" id="utalii_checkout_additional_request"></textarea>
                                </div>
                                <div class="utalii_form_row button_row">
									<input type="submit" name="utalii_checkout_final_submit" id="utalii_checkout_final_submit" value="Checkout" />
                                    <?php
									if(isset($response_msg) && $response_msg != "")
									{
										echo $response_msg;
									}
									?>
								</div>
                            </form>
                    	</div>
                        <?php
					}
					else
					{
						?>
						<div class="utalii_checkout_login" id="utalii_checkout_login">
							<form action="" method="post" id="utalii_checkout_login_frm">
								<h3>Login</h3>
								<div class="utalii_form_row">
									<label for="utalii_checkout_l_username">Username:</label>
									<input type="text" name="utalii_checkout_login_username" id="utalii_checkout_login_username" required="required" maxlength="100" />
								</div>
								<div class="utalii_form_row">
									<label for="utalii_checkout_login_password">Password:</label>
									<input type="password" name="utalii_checkout_login_password" id="utalii_checkout_login_password" required="required" maxlength="50" />
								</div>
								<div class="utalii_form_row button_row">
									<input type="submit" name="utalii_checkout_login_button" id="utalii_checkout_login_button" value="Login" />
									<img src="<?php echo UTALII_PLUGIN_URL."/assets/img/utalii-ajax-loader.gif"; ?>" id="utalii_checkout_l_loader" style="display:none;"/>
								</div>
								<div class="utalii_form_row" id="Login_error">
								</div>
							</form>
						</div>
						<div class="utalii_checkout_registration" id="utalii_checkout_registration">
                            <form action="" method="post" id="utalii_checkout_registration_frm">
                                <h3>New Registration</h3>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_title">Title:</label>
                                    <select name="utalii_checkout_r_title" id="utalii_checkout_r_title">
                                        <option value="Mr.">Mr.</option>
                                        <option value="Ms.">Ms.</option>
                                        <option value="Mrs.">Mrs.</option>
                                        <option value="Miss.">Miss.</option>
                                        <option value="Dr.">Dr.</option>
                                        <option value="Prof.">Prof.</option>
                                    </select>
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_f_name">First Name:</label>
                                    <input type="text" name="utalii_checkout_r_f_name" id="utalii_checkout_r_f_name" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_l_name">Last Name:</label>
                                    <input type="text" name="utalii_checkout_r_l_name" id="utalii_checkout_r_l_name" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_address">Address:</label>
                                    <input type="text" name="utalii_checkout_r_address" id="utalii_checkout_r_address" required="required" maxlength="200" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_city">City:</label>
                                    <input type="text" name="utalii_checkout_r_city" id="utalii_checkout_r_city" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_state">State:</label>
                                    <input type="text" name="utalii_checkout_r_state" id="utalii_checkout_r_state" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_pcode">Postal Code:</label>
                                    <input type="text" name="utalii_checkout_r_pcode" id="utalii_checkout_r_pcode" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_country">Country:</label>
                                    <input type="text" name="utalii_checkout_r_country" id="utalii_checkout_r_country" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_phone">Phone:</label>
                                    <input type="text" name="utalii_checkout_r_phone" id="utalii_checkout_r_phone" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_id_type">Identity Type:</label>
                                    <input type="text" name="utalii_checkout_r_id_type" id="utalii_checkout_r_id_type" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_id_number">Identity Number:</label>
                                    <input type="text" name="utalii_checkout_r_id_number" id="utalii_checkout_r_id_number" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_id_number">Email:</label>
                                    <input type="email" name="utalii_checkout_r_email" id="utalii_checkout_r_email" required="required" maxlength="100" />
                                </div>
                                <div class="utalii_form_row">
                                    <label for="utalii_checkout_r_password">Password:</label>
                                    <input type="password" name="utalii_checkout_r_password" id="utalii_checkout_r_password" required="required" maxlength="20" />
                                </div>
                                <div class="utalii_form_row button_row">
                                    <input type="submit" name="utalii_checkout_register_button" id="utalii_checkout_register_button" value="Register" />
                                    <img src="<?php echo UTALII_PLUGIN_URL."/assets/img/utalii-ajax-loader.gif"; ?>" id="utalii_checkout_r_loader" style="display:none;"/>
                                </div>
                                <div class="utalii_form_row" id="register_error">
                                </div>
                            </form>
						</div>
						<?php
					}
				}
				
				if(isset($_GET) && $_GET['paypal_cancel_return'] == "1")
				{
					?>
                    <h1>Payment Failed! Please try again.</h1>
                    <?php
				}
				?>
                
                
                
                
			</div>
			<?php //UTALII_Misc::print_r( $_SESSION ); ?>
			<!-- form wrapper end -->
			<?php
			$output = ob_get_clean();
			/* output buffer, end */
			return $output;
		}
		
		function get_all_location_list(){
			
		}
	}
}
UTALII_Hotel_Search_SC::get_instance();