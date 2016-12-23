jQuery( function ( $ ) {
	$(document).ready(function(){
		
		/* manage re-ordering of attachment ids on drag n drop, start */
		$(".gimb_container .images").sortable({
			items: '.image',
			cursor: 'move',
			scrollSensitivity: 40,
			//placeholder: 'gimb_container-ui-state-highlight',
			forcePlaceholderSize: true,
			forceHelperSize: true,
			helper: 'clone',
			opacity: 0.65,
			start:function(event,ui){
			},
			stop:function(event,ui){
			},
			update: function(event, ui) {
				
				var attachment_ids = [];
				var attachment_id = '';
				$.each( $('#gimb_top_container .images .image'), function(index,ele) {
					
					var attr = $(ele).attr('data-attachment_id');
					if( ( typeof attr !== typeof undefined ) && ( attr !== false ) ) {
						attachment_id = $.trim(attr);
						attachment_ids.push(attachment_id);
					}
				});
				
				attachment_ids = attachment_ids.join(",");
				$("#gimb_gallery_images").val(attachment_ids);
				
			}
		});
		/* manage re-ordering of attachment ids, start */
		
		
		
		/* manage, on add new attahcment, start */
		$("#gimb_add_image").click(function(){
			
			/* open media gallery */
			var gallery_window = wp.media({
				title: 'Select Gallery Images: [press Ctrl+Click to choose multiple images]',
				library: {type: 'image'},
				multiple: true,
				button: {text: 'Insert in Gallery'}
			});
			
			gallery_window.on('select', function(){
				var user_selection = gallery_window.state().get('selection').toJSON();
				var attachment_ids = $("#gimb_gallery_images").val();
				if( $.trim( attachment_ids ) != "" ){
					attachment_ids = attachment_ids.split(",");
				} else {
					attachment_ids = [];
				}
				
				$.each(user_selection, function(index,ele){
					id = false;
					url = false;
					
					if( ele.sizes.thumbnail ){
						id = ele.id;
						url = ele.sizes.thumbnail.url;
					} else if( ele.sizes.medium ){
						id = ele.id;
						url = ele.sizes.medium.url;
					} else{
						id = ele.id;
						url = ele.sizes.full.url;
					}
					
					li_html	=	'';
					li_html	+=	'<li data-attachment_id="'+id+'" class="image ui-sortable-handle">';
					li_html	+=		'<img src="'+url+'" />';
					li_html	+=		'<ul class="controls">';
					li_html	+=			'<li class="delete">';
					li_html	+=				'<span class="dashicons dashicons-no-alt delete_image_icon" title="Remove Image"></span>'
					li_html	+=			'</li>';
					li_html	+=		'</ul>';
					li_html	+=	'</li>';
					
					$("#gimb_container ul.images").append(li_html);
					
					attachment_ids.push(id);
				});
				attachment_ids = attachment_ids.join(",");
				$("#gimb_gallery_images").val(attachment_ids);
			
			});
			
			gallery_window.open();
			
		});
		/* manage, on add new attahcment, end */
		
		
		
		/* manage, on delete existing attachment, start */
		$('#gimb_container').on( 'click', '.delete_image_icon', function() {
			
			$(this).closest('li.image').remove();
			
			var attachment_ids = [];
			var attachment_id = '';
			$.each( $('#gimb_top_container .images .image'), function(index,ele) {
				
				var attr = $(ele).attr('data-attachment_id');
				if( ( typeof attr !== typeof undefined ) && ( attr !== false ) ) {
					attachment_id = $.trim(attr);
					attachment_ids.push(attachment_id);
				}
			});
			
			attachment_ids = attachment_ids.join(",");
			$("#gimb_gallery_images").val(attachment_ids);
			
			return false;
		});
		/* manage, on delete existing attachment, end */
		
	});
});