<?php
if( !class_exists( 'UTALII_Booking_Email' ) ){
	class UTALII_Booking_Email{
		
		public static function send_mail( $booking ){
			$to = self::get_to_email();
			
			$site_title = get_bloginfo( 'name' );
			$subject = $site_title . ' - Your hotel booking details';
			
			$body = self::get_body( $booking );
			
			add_filter( 'wp_mail_from', array( __CLASS__, 'from_email' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'from_name' ) );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'set_content_type' ) );
			
			wp_mail( $to, $subject, $body );
			
			remove_filter( 'wp_mail_from', array( __CLASS__, 'from_email' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'set_content_type' ) );
			
		}
		
		public static function set_content_type( $content_type ) {
			return 'text/html';
		}
		
		public static function from_name( $from_name ) {
			$site_title = get_bloginfo( 'name' );
			if( !empty( $site_title ) ){
				return $site_title;
			}
			return $from_name;
		}
		
		public static function from_email( $from_email ) {
			$admin_email = get_option('admin_email');
			if( is_email( $admin_email ) ){
				return $admin_email;
			}
			return $admin_email;
		}
		
		public static function get_to_email(){
			$user_id = get_current_user_id();
			$uobj = get_user_by( 'id', $user_id );
			$user_email = $uobj->user_email;
			return $user_email;
		}
		
		public static function get_body( $booking ){
			ob_start();
			?>
			<h2>Thank you for booking with us.</h2>
			<h2><u>Your Booking Details</u></h2>
			<b>Booking id: </b>#<?php echo $booking['booking_id']; ?>
			<br />
			<b>Hotel : </b>
			<a href="<?php echo get_permalink( $booking['hotel_id'] ); ?>">
				<?php echo get_the_title( $booking['hotel_id'] ); ?>
			</a>
			<br />
			<b>Check-In: </b> <?php echo date( 'F j, Y', strtotime( $booking['checkindate'] ) ); ?>
			<br />
			<b>Check-Out: </b> <?php echo date( 'F j, Y', strtotime( $booking['checkoutdate'] ) ); ?>
			<br />
			<b>Total Night(s): </b> <?php echo $booking['total_nights']; ?>
			<br />
			<b>Booking Time: </b><?php echo date('F j, Y H:i', $booking['booking_time'] ); ?>
			<br />
			<b>Payment mode: </b><?php echo $booking['payment_via']; ?>
			<br />
			<b>Paypal Transaction id: </b><?php echo $booking['payment_tx_id']; ?>
			<br />
			<b>Additional Request: </b><?php echo $booking['additional_info']; ?>
			<br /><b>Room Details: </b><br />
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
			<br />
			<br />
			<br />
			<b>Regards <?php echo get_bloginfo( 'site_title' ); ?></b>
			<?php
			$output = ob_get_clean();
			return $output;
		}
	} /* end of class */
}