jQuery( function ( $ ) {
	$(document).ready(function(){
		$("body").prepend('<div class="light_box_container" id="light_box_container" style="display:none;"><div class="close_lightbox">X</div><div id="light_box_content" class="light_box_content"></div></div><div id="fade_div" style="display:none;"></div>');
		/* for small size slider start*/
		var ul = $(".slider");
		ul.each(function(indx) {
			var slide_count = $(this).find('ul').children().length;
			var slide_width_pc = 100.0 / slide_count;
			
			$(this).find('ul').css('width', (slide_count * 100)+"%");
			var starting_li = 0;
			$(this).find('li').each(function(index, element) {
				var left_percent = (slide_width_pc * index) + "%";
				$(this).css({"left":left_percent});
				$(this).css({width:(100 / slide_count) + "%"}); 
				if(starting_li == 0)
				{
					$(this).addClass('current_active');
				}
				$(this).attr('data-li-no', starting_li);
				starting_li++;
            });
		});
	
		// Listen for click of prev button
		$(".slider .prev").click(function() {
			var total_lis = $(this).parent().find('ul').children().length;
			var current_li = $(this).parent().find('ul li.current_active');
			var li_no = parseInt(current_li.attr('data-li-no'));
			if(li_no > 0)
			{
				current_li.prev().addClass('current_active');
				current_li.removeClass('current_active');
				var margin_left_pc = ((li_no - 1) * (-100)) + "%";
				$(this).parent().find('ul').animate({"margin-left": margin_left_pc}, 500);
			}
		});
	
		// Listen for click of next button
		$(".slider .next").click(function(){
			var total_lis = $(this).parent().find('ul').children().length;
			var current_li = $(this).parent().find('ul li.current_active');
			var li_no = parseInt(current_li.attr('data-li-no'));
			if(li_no < (total_lis - 1))
			{
				current_li.next().addClass('current_active');
				current_li.removeClass('current_active');
				var margin_left_pc = ((li_no + 1) * (-100)) + "%";
				$(this).parent().find('ul').animate({"margin-left": margin_left_pc}, 500);
			}
		});
		/* for small size slider end*/
		
		/* for large size slider start*/
		
		var ul_large = "";
		var slide_count_large = "";
		var slide_width_pc_large = "";
		var slide_index_large = 0;
		$(".full_size_slider").click(function(e){
			$(".light_box_content").html($(this).find('.container_large').html());
			ul_large = $(".light_box_container ul");
			slide_count_large = ul_large.children().length;
			slide_width_pc_large = 100.0 / slide_count_large;
			ul_large.css('width', (slide_count_large * 100)+"%");
			ul_large.find("li").each(function(indx){
				var left_percent_large = (slide_width_pc_large * indx) + "%";
				$(this).css({"left":left_percent_large});
				$(this).css({width:(100 / slide_count_large) + "%"});
			});
			$(".light_box_container").show();
			$("#fade_div").show();
		});
	
		// Listen for click of prev button
		$(document).on('click', '.light_box_container .prev_large', function(event) {
			slide_large(slide_index_large - 1);
		});
		// Listen for click of next button
		$(document).on('click', '.light_box_container .next_large', function(event) {
			slide_large(slide_index_large + 1);
		});
		function slide_large(new_slide_index) 
		{
			if(new_slide_index < 0 || new_slide_index >= slide_count_large) return; 
			var margin_left_pc = (new_slide_index * (-100)) + "%";
			ul_large.animate({"margin-left": margin_left_pc}, 300, function(){
				slide_index_large = new_slide_index;
			});
		}
		/* for large size slider end */
		$(document).on('click', '.close_lightbox', function(event) {
			ul_large = "";
			slide_count_large = "";
			slide_width_pc_large = "";
			slide_index_large = 0;
            $(".light_box_container").hide();
			$("#fade_div").hide();
        });
	});
});