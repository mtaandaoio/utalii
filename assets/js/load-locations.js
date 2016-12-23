jQuery( function ( $ ) {
	$(document).ready(function(){
		/* utalii_load_loations_js_obj , prefix 'utalii_' can be change */
		/* utalii_load_loations_js_obj.admin_ajax_url */
		var mkp = utalii_load_loations_js_obj.mkp;
		var city = $( "#\\"+ mkp + "city" );
		var state = $( "#\\"+ mkp + "state" );
		var country = $( "#\\"+ mkp + "country" );
		
		if( city.length ){
			city.change(function(){
				curr_city = $(this).val();
				load_state( curr_city ); /* this will also trigger country to change */
			});
		}
		
		if( state.length ){
			state.change(function(){
				curr_state = $(this).val();
				load_country( curr_state );
			});
		}
		
		
		
		/* functions, start */
		
		function load_state( city_id ){
			if( state.length ){
				spinner1 = state.next(".spinner");
				
				if( country.length ){
					spinner2 = country.next(".spinner");
				}
				
				if( spinner1.length ){
					spinner1.css('visibility', 'visible');
				}
				if( spinner2.length ){
					spinner2.css('visibility', 'visible');
				}
				
				
				$.ajax({
					url: utalii_load_loations_js_obj.admin_ajax_url,
					method: 'POST',
					dataType: 'json',
					data: {
						'action': 'utaliiajax_load_state_by_city_id',
						'city_id': city_id
					}
				}).done(function(r){
					if( r.success ){
						if( r.data.state_id ){
							state.val( r.data.state_id );
						} else {
							state.val( '' );
						}
						
						if( r.data.country_id ){
							country.val( r.data.country_id );
						}
					}
					
					if( spinner1.length ){
						spinner1.css('visibility', 'hidden');
					}
					if( spinner2.length ){
						spinner2.css('visibility', 'hidden');
					}
					
				}).fail(function( jqXHR, textStatus ){
					//console.log(jqXHR);
					//console.log(textStatus);
					if( spinner1.length ){
						spinner1.css('visibility', 'hidden');
					}
					if( spinner2.length ){
						spinner2.css('visibility', 'hidden');
					}
				});
			}
		}
		
		function load_country( state_id ){
			if( country.length ){
				spinner1 = country.next(".spinner");
				if( spinner1.length ){
					spinner1.css('visibility', 'visible');
				}
				$.ajax({
					url: utalii_load_loations_js_obj.admin_ajax_url,
					method: 'POST',
					dataType: 'json',
					data: {
						'action': 'utaliiajax_load_country_by_state_id',
						'state_id': state_id
					}
				}).done(function(r){
					if( r.success ){
						if( r.data.country_id ){
							country.val( r.data.country_id );
						}
					}
					
					if( spinner1.length ){
						spinner1.css('visibility', 'hidden');
					}
					
				}).fail(function( jqXHR, textStatus ){
					//console.log(jqXHR);
					//console.log(textStatus);
					if( spinner1.length ){
						spinner1.css('visibility', 'hidden');
					}
				});
			}
		}
		
		/* functions, end */
	});
});