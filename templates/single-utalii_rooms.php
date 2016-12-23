<?php
/**
 * The Template for displaying all single rooms
 */
get_header(); ?>
	<div id="primary" class="site-content">
		<div id="content" role="main">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php $room = utalii_get_room_detail( get_the_ID() ); ?>
				<div class="utalii_wrap">
                    <div class="inner_content rooms-details ">
                            <h1 class="page_title_cls"><?= $room['title']; ?></h1>
                            <h4 class="address_cls_dtls">
                                <ul>
                                    <li>in <a href="<?= get_permalink( $room['hotel_id'] ); ?>" ><?= $room['hotel']; ?></a></li>
                                </ul>
                            </h4>
                        <div class="charge-and-occu-cls">
                            <p><b>charge per night: </b>$<?= $room['charge_per_night']; ?></p>
                            <p><b>max adult occupancy: </b><?= $room['max_adult_occupancy']; ?></p>
                            <p><b>max children occupancy: </b><?= $room['max_children_occupancy']; ?></p>
                        </div>
                        <div class="thumbnail_img single-hotel">
                        	<div class="container">
					  			<div class="slider">
                            		<ul>
									<?php
                                    if( !is_null( $room['images'] ) )
                                    {
                                        foreach( $room['images'] as $room_gid => $room_gi ) 
                                        {
                                            ?>
                                            <li class="list_imag"><img src="<?= $room_gi['full']['url']; ?>" class="gallery_image_cls" /></li>
                                            <?php
                                        }
                                    }
                                    ?>
                            		</ul> 
                            		<button class="prev arrow-cls"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/arrow-left.png"; ?>"/></button>
						<button class="next arrow-cls"><img src="<?php echo UTALII_PLUGIN_URL."/assets/img/arrow-right.png"; ?>"/></button>
					  			</div>
							</div>
                        </div>
                        <div class="l_discrp_cls_room">
                            <p><?= $room['long_description']; ?></p>
                        </div>
                    </div>
				</div>
				<?php comments_template( '', true ); ?>
			<?php endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>