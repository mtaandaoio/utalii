<?php
/**
 * The Template for displaying all single hotels
 */
get_header(); ?>
	<div id="primary" class="site-content">
		<div id="content" role="main">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php $hotel = utalii_get_hotel_detail( get_the_ID() ); ?>
				<div class="utalii_wrap">
					<!-- hotel data start -->
                    
               	<!-- hotel detais start -->
                    <div class="inner_content hotel-details">
                    <h1 class="page_title_cls">
                    <?= $hotel['title']; ?>
                    </h1>
                    <h4 class="address_cls_dtls"><ul><li><?= $hotel['full_address']; ?></li></ul></h4>
                    <div class="thumbnail_img single-hotel">
                    	<div class="container">
					  		<div class="slider">
                                <ul>
                                    <?php 
                                    if( !is_null( $hotel['gallery_images'] ) )
                                    {
                                        foreach( $hotel['gallery_images'] as $hotel_gid => $hotel_gi ) 
                                        { 
                                            ?>
                                            <li class="list_imag"><img src="<?= $hotel_gi['full']['url']; ?>" class="gallery_image_cls" /></li>
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
                    <div class="l_discrp_cls">
                    	<p><?= $hotel['long_description']; ?></p>
                    </div>
                    </div>
                  <!-- hotel detais end -->
                  
                  <!-- City detais start -->
                    <div class="details_cls_details">
					<?php if ( !is_null ( $hotel['city_id'] ) ){ ?>
                              <div class="about_city_name">
                                 <h3 class="sub_headin_cls"><strong>About City: </strong> <?= $hotel['city_detail']['title']; ?></h3>
                              </div> 
                            <div class="city_thumb_img_cls">
                              	<ul class="thum_img_cls">
                                	<?php
                                   	 if( !is_null( $hotel['city_detail']['images'] ) ){
                                    	foreach( $hotel['city_detail']['images'] as $city_gid => $city_gi ) { ?>
                                        <li class="list_imag"><img src="<?= $city_gi['thumbnail']['url']; ?>" class="gallery_image_cls"/></li>
                                	<?php } } ?>
                              </ul>
                            </div>
                            <div class="l_discrp_cls">
                            		<p><b>Description</b></p>
                               		<p><?= $hotel['city_detail']['long_description']; ?></p>
                              </div>
                    <?php } ?>
                    </div>
                    <!-- City detais end -->
                    
                    <!-- State detais start -->
                    <div class="details_cls_details">
					<?php if ( !is_null ( $hotel['state_id'] ) ){ ?>
                              <div class="about_city_name">
                                 <h3 class="sub_headin_cls"><strong> About State: </strong><?= $hotel['state_detail']['title']; ?></h3>
                              </div>
                              <div class="city_thumb_img_cls">
                              	<ul class="thum_img_cls">
                                	
                                    <?php
									if( !is_null( $hotel['state_detail']['images'] ) ){
										foreach( $hotel['state_detail']['images'] as $state_gid => $state_gi ) { ?>
										<li class="list_imag"><img src="<?= $state_gi['thumbnail']['url']; ?>" class="gallery_image_cls"/></li>
					<?php } } ?>
                              </ul>
                            </div>
                            
                            <div class="in_cls_discription">
                            <p><b>Description</b></p>
                               		<p><?= $hotel['state_detail']['long_description']; ?></p>
                              </div>
                    <?php } ?>
                    </div>
                    <!-- State detais end -->
                    
                    <!-- country detais start -->
                    <div class="details_cls_details">
					<?php if ( !is_null ( $hotel['country_id'] ) ){ ?>
                              <div class="about_city_name">
                                 <h3 class="sub_headin_cls"><strong> About Country: </strong><?= $hotel['country_detail']['title']; ?></h3>
                              </div> 
                            <div class="city_thumb_img_cls">
                              	<ul class="thum_img_cls">
                                    <?php
					if( !is_null( $hotel['country_detail']['images'] ) ){
						foreach( $hotel['country_detail']['images'] as $country_gid => $country_gi ) { ?>
					<li class="list_imag"><img src="<?= $country_gi['thumbnail']['url']; ?>" class="gallery_image_cls"/></li>
					<?php } } ?>
                              </ul>
                            </div>
                            <div class="in_cls_discription">
                            <p><b>Description</b></p>
                               		<p><?= $hotel['country_detail']['long_description']; ?></p>
                              </div>
                    <?php } ?>
                    </div>
                    <!-- country detais end -->
					<!-- hotel data end -->
					
				</div>
				<?php comments_template( '', true ); ?>
			<?php endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>