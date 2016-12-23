<?php
/**
 * The template for displaying Archive for utalii_hotels post type
 * and for view hotels page
 */

$paged = 1;
$hotels = array();
$hotels_found = false;
$utalii_pagination = false;
$available_hotels = false;
$hotel_search_form = false;

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
		
		if(	isset(
				$_SESSION['hotel_search_form_data']['location'],
				$_SESSION['hotel_search_form_data']['checkindate'],
				$_SESSION['hotel_search_form_data']['checkoutdate']
			)
		){
			$location = trim( $_SESSION['hotel_search_form_data']['location'] );
			$checkindate = trim( $_SESSION['hotel_search_form_data']['checkindate'] );
			$checkoutdate = trim( $_SESSION['hotel_search_form_data']['checkoutdate'] );
			$data	=	array(
							'location'		=>	$location,
							'checkindate'	=>	$checkindate,
							'checkoutdate'	=>	$checkoutdate,
						);
			$search_rooms_obj = new UTALII_Hotel_Search();
			$available_hotels = $search_rooms_obj->get_available_hotels( $data );
			$hotel_search_form = true;
		}
	}
}

$args	=	array(
				'post_type'					=>	array( 'utalii_hotels' ),
				'nopaging'					=>	false,
				'paged'						=>	$paged,
				'posts_per_page'			=>	'5',
				'order'						=>	'ASC',
				'orderby'					=>	'title',
				'cache_results'				=>	false,
				'update_post_meta_cache'	=>	false,
				'update_post_term_cache'	=>	false,
			);

if( $hotel_search_form ){
	$args['post__in'] = array(0);
	
	if( $available_hotels ){
		$args['post__in'] = $available_hotels;
	}
}

$query = new WP_Query( $args );

if ( $query->have_posts() ) {
	$hotels_found = true;
	while ( $query->have_posts() ) {
		$query->the_post();
		$id = get_the_ID();
		$hotels[$id] = utalii_get_hotel_detail( $id );
	}
	$utalii_pagination = utalii_paginate_links( $query, $paged );
	wp_reset_postdata();
}
?>
<?php get_header(); ?>

	<section id="primary" class="site-content">
		<div id="content" role="main">
		<div class="utalii_wrap">
			<?php if( $hotels_found ){ ?>
			
			<?php echo $utalii_pagination; ?>
			<br />
			<br />
			<?php foreach( $hotels as $hotel ) { ?>
			<!-- hotel data start -->
			<div class="utalii_wrap_inner_content">
            	<div class="main_image_cls">
					<?php if( !is_null( $hotel['images'] ) ){ ?>
                    <div class="container">
					  <div class="slider">
                        <ul>
                        <?php foreach( $hotel['images'] as $img_id => $img ) { ?>
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
                    	<span class="full_size_symbol">
                        <img src="<?php echo UTALII_PLUGIN_URL."/assets/img/view_full_screen.png"; ?>"/>
                        </span>
                        <div class="container_large" style="display:none">
                            <div class="slider_large">
                                <ul>
                                    <?php
										foreach( $hotel['images'] as $img_id => $img ) 
										{ 
											?>
                                        	<li><img src="<?= $img['full']['url']; ?>" class="gallery_image_cls" /></li>
                                    		<?php 
										}
									?>
                                </ul>
                                <button class="prev_large arrow-cls"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/arrow-left.png"; ?>"/></button>
						<button class="next_large arrow-cls"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/arrow-right.png"; ?>"/></button>                            </div>
                        </div>
                    </div>
                    
                    
                    <?php } ?>
                </div>
                <div class="detail_right_section_cls">
					<h1 class="page_title_cls">
						<a href="<?php echo get_permalink( $hotel['id'] ); ?>"><?php echo $hotel['title']; ?></a>
					</h1>
					
					<h4 class="address_cls_dtls"><ul><li><?php echo $hotel['full_address']; ?></li></ul></h4>
					<p class="short_dis_cls"><?php echo $hotel['short_description']; ?></p>
                </div>
            </div>
			<!-- hotel data end -->
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
