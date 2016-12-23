<?php
/**
 * The template for displaying Archive for utalii_rooms post type
 * and for view rooms page
 */
 
$paged = 1;
$rooms = array();
$rooms_found = false;
$utalii_pagination = false;
$available_rooms = false;
$is_booking_room = false;
if(
	isset(
		$_SESSION['hotel_search_form_data']['location'],
		$_SESSION['hotel_search_form_data']['checkindate'],
		$_SESSION['hotel_search_form_data']['checkoutdate'],
		$_SESSION['view_rooms_by_hotel_data']['hotel_id']
	)
){
	$s_location		=	trim( $_SESSION['hotel_search_form_data']['location'] );
	$s_checkindate	=	trim( $_SESSION['hotel_search_form_data']['checkindate'] );
	$s_checkoutdate	=	trim( $_SESSION['hotel_search_form_data']['checkoutdate'] );
	$s_hotel_id		=	trim( $_SESSION['view_rooms_by_hotel_data']['hotel_id'] );
	
	if(
		!empty( $s_location ) &&
		!empty( $s_checkindate ) &&
		!empty( $s_checkoutdate ) &&
		!empty( $s_hotel_id )
	){
		$is_booking_room = true;
	}
	
}
if( get_query_var( 'paged' ) ){
	$paged = get_query_var( 'paged' );
} else if( get_query_var( 'page' ) ){
	$paged = get_query_var( 'page' );
}
if( isset( $_SESSION['hotel_search_form_data'] ) ){
	
	if( !empty( $_SESSION['hotel_search_form_data'] ) ){
		$location = false;
		$checkindate = false;
		$checkoutdate = false;
		//$hotel_id = false;
		
		if(	isset(
				$_SESSION['hotel_search_form_data']['location'],
				$_SESSION['hotel_search_form_data']['checkindate'],
				$_SESSION['hotel_search_form_data']['checkoutdate']
				//$_SESSION['view_rooms_by_hotel_data']['hotel_id']
			)
		){
			$location = trim( $_SESSION['hotel_search_form_data']['location'] );
			$checkindate = trim( $_SESSION['hotel_search_form_data']['checkindate'] );
			$checkoutdate = trim( $_SESSION['hotel_search_form_data']['checkoutdate'] );
			//$hotel_id = trim( $_SESSION['view_rooms_by_hotel_data']['hotel_id'] );
			$data	=	array(
							'location'		=>	$location,
							'checkindate'	=>	$checkindate,
							'checkoutdate'	=>	$checkoutdate,
							//'hotel_id'		=>	$hotel_id,
						);
			$search_rooms_obj = new UTALII_Hotel_Search();
			$available_rooms = $search_rooms_obj->get_all_non_booked_rooms( $data['location'], $data['checkindate'], $data['checkoutdate'] );
			var_dump($available_rooms);
		}
	}
}
$args	=	array(
				'post_type'					=>	array( 'utalii_rooms' ),
				'nopaging'					=>	false,
				'paged'						=>	$paged,
				'posts_per_page'			=>	'5',
				'order'						=>	'ASC',
				'orderby'					=>	'title',
				'cache_results'				=>	false,
				'update_post_meta_cache'	=>	false,
				'update_post_term_cache'	=>	false,
			);
if( isset( $_GET['hotel_id'] ) ){
	$hotel_id = $_GET['hotel_id'];
	$args['meta_query'] = array(
		array(
			'key'		=>	UTALII_MKP . 'hotel_id',
			'value'		=>	$hotel_id,
			'compare'	=>	'=',
		),
	);
}
if( $available_rooms ){
	$args['post__in'] = $available_rooms;
}

$query = new WP_Query( $args );
if ( $query->have_posts() ) {
	$rooms_found = true;
	while ( $query->have_posts() ) {
		$query->the_post();
		$id = get_the_ID();
		$rooms[$id] = utalii_get_room_detail( $id );
	}
	$utalii_pagination = utalii_paginate_links( $query, $paged );
	wp_reset_postdata();
}
?>
<?php get_header(); ?>
	<section id="primary" class="site-content">
		<div id="content" role="main">
		
		<div class="utalii_wrap">
			<?php if( $rooms_found ){ ?>
			
			<?php echo $utalii_pagination; ?>
			<br />
			<br />
			<?php foreach( $rooms as $room ) { ?>
			<!-- room data start -->
			<div class="utalii_wrap_inner_content">
            	<div class="main_image_cls">
					<?php if( !is_null( $room['images'] ) ){ ?>
                    <div class="container">
					  <div class="slider">
                        <ul>
                        <?php foreach( $room['images'] as $img_id => $img ) { ?>
                            <li>
                                <img src="<?= $img['thumbnail']['url']; ?>" class="gallery_image_cls" />
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
                                    <?php
										foreach( $room['images'] as $img_id => $img ) 
										{ 
											?>
                                        	<li><img src="<?= $img['full']['url']; ?>" class="gallery_image_cls" /></li>
                                    		<?php 
										}
									?>
                                </ul>
                                
                                <button class="prev_large arrow-cls"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/arrow-left.png"; ?>"/></button>
						<button class="next_large arrow-cls"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/arrow-right.png"; ?>"/></button>
                            </div>
                        </div>
                    </div>
                    
                    
                    
                    <?php } ?>
                </div>
                <div class="detail_right_section_cls">
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
					<p class="short_dis_cls"><?php echo $room['short_description']; ?></p>
                </div>
            </div>
			<!-- room data end -->
			<?php } ?>
			<br />
			<br />
			<?php echo $utalii_pagination; ?>
			
			<?php } else { ?>
			<h2>No hotels found</h2>
			<?php } ?>
		</div>
		
		</div><!-- #content -->
	</section><!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
