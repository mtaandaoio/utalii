<?php

if(!defined('ABSPATH')) {exit;}

if( !class_exists('UTALII_My_Bookings_SC') ){

	class UTALII_My_Bookings_SC{

		private static $_instance = null;

		

		public static function get_instance(){

			if( is_null( self::$_instance ) ){

				self::$_instance = new self();

			}

			return self::$_instance;

		}

		

		public function __construct(){

			add_shortcode( 'utalii_my_bookings', array( $this, 'my_bookings_sc' ) );

		}

		

		public function my_bookings_sc( $atts ){

			$atts	=	shortcode_atts(

							array(

							),

							$atts, 'utalii_my_bookings'

						);

			

			/* start */

			$paged = 1;

			if( get_query_var( 'paged' ) ){

				$paged = get_query_var( 'paged' );

			} else if( get_query_var( 'page' ) ){

				$paged = get_query_var( 'page' );

			}

			

			$booking_found = false;

			$booking_user_id = get_current_user_id();

			$bookings = array();

			

			$args	=	array(

							'post_type'					=>	array( 'utalii_bookings' ),

							'post_status'				=>	array( 'publish' ),

							'nopaging'					=>	false,

							'paged'						=>	$paged,

							'posts_per_page'			=>	'5',

							'order'						=>	'DESC',

							'orderby'					=>	'title',

							'cache_results'				=>	false,

							'update_post_meta_cache'	=>	false,

							'update_post_term_cache'	=>	false,

							'meta_query'				=>	array(

																array(

																	'key'		=>	UTALII_MKP . 'user_id',

																	'value'		=>	$booking_user_id,

																	'compare'	=>	'=',

																),

															),

						);

			

			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {

				$booking_found = true;

				while ( $query->have_posts() ) {

					$query->the_post();

					$bid = get_the_ID();

					$title = get_the_title();

					

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

					

					$bookings[$bid] = array(

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

				}

				

				$big = 999999999;

				$paginate_links = paginate_links(

					array(

						'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),

						'format' => '?paged=%#%',

						'current' => max( 1, $paged ),

						'total' => $query->max_num_pages

					)

				);

			}

			wp_reset_postdata();

			

			ob_start(); ?>

			<!-- form wrapper start -->

			<div class="utalii_wrap">
            
            <div class="my_booking_cls_wrapper">

			<?php if( $booking_found ) { ?>

		<?php echo $paginate_links; ?><br />

			<ol class="my_booking_cls_main"> 

			<?php foreach( $bookings as $bid => $b ) { ?>

				<li>
                
					 <a href="<?php echo get_permalink( $b['hotel_id'] ); ?>" class="booking_hotel_heading">

						<?php echo get_the_title( $b['hotel_id'] ); ?>

					</a>
                    
					<b>Booking id: </b>#<?php echo $bid; ?>

					<br />

					<b>Check-In: </b> <?php echo date( 'F j, Y', strtotime( $b['checkindate'] ) ); ?>

					<br />

					<b>Check-Out: </b> <?php echo date( 'F j, Y', strtotime( $b['checkoutdate'] ) ); ?>

					<br />

					<b>Total Night(s): </b> <?php echo $b['total_nights']; ?>

					<br />

					<b>Booking Time: </b><?php echo date('F j, Y H:i', $b['booking_time'] ); ?>

					<br />

					<b>Payment mode: </b><?php echo $b['payment_via']; ?>

					<br />

					<b>Paypal Transaction id: </b><?php echo $b['payment_tx_id']; ?>

					<br />

					<b>Additional Request: </b><?php echo $b['additional_info']; ?>

					<br />

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

						<tr>

							<th>#</th>

							<th>Type</th>

							<th>Adults</th>

							<th>Children</th>

							<th>Room(s)</th>

							<th>Charge/night</th>

							<th>Gross Total (KSh)</th>

						</tr>

						<?php $i=1; foreach( $b['rooms'] as $rid => $dt ) {?>

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

							<td colspan="6" class="right_align_cls">

								<?php _e( 'Subtotal', 'utalii' ); ?> 

							</td>

							<td>

								<?php echo $b['subtotal_html']; ?>

							</td>

						</tr>

						<?php if( !is_null( $b['tax'] ) ){ ?>

						<tr>

							<td colspan="6" class="right_align_cls">

								<?php _e( 'Tax', 'utalii' ); ?>(<?php echo $b['tax_percentage']; ?>%)

							</td>

							<td>

								<?php echo $b['tax_html']; ?>

							</td>

						</tr>

						<?php } ?>

						<tr>

							<td colspan="6" class="right_align_cls">

								<?php _e( 'Grand total', 'utalii' ); ?> 

							</td>

							<td>

								<?php echo $b['grand_total_html']; ?>

							</td>

						</tr>

					</table>

				</li>

			<?php } ?>

			</ol>

			<br /><?php echo $paginate_links; ?><br />

			<?php } else { ?>
				<?php if( is_user_logged_in() ){ ?>
				<h2>No bookings found in your account.</h2>
				<?php } else { ?>
				<h2>You need to <a href="<?php echo wp_login_url( get_permalink() ); ?>" title="Login">Login</a> to view your bookings.</h2>
				<?php } ?>
			<?php } ?>
</div>
			</div>

			<!-- form wrapper end --><?php

			$output = ob_get_clean();

			/* output buffer, end */

			

			return $output;

		}

	}

}

UTALII_My_Bookings_SC::get_instance();