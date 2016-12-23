jQuery(document).ready(function($){
	/* utalii_hotel_search_form_obj */
	/* console.log(utalii_hotel_search_form_obj); */
	
	var min_date = utalii_hotel_search_form_obj.min_date;
	var max_date = utalii_hotel_search_form_obj.max_date;
	var checkindate = utalii_hotel_search_form_obj.checkindate;
	var checkoutdate = utalii_hotel_search_form_obj.checkoutdate;
	
	
	$(".utalii_hotel_search_wrap .checkindate").datepicker({
		dateFormat: 'MM dd, yy',
		changeMonth: true,
		changeYear: true,
		minDate: checkindate,
		maxDate: max_date,
		onSelect: function(dateStr) {
			var new_checkout_min = $(this).datepicker('getDate');
			var new_checkout_min_show = $(this).datepicker('getDate');
			
			new_checkout_min.setDate(new_checkout_min.getDate() + 1);
			new_checkout_min_show.setDate(new_checkout_min_show.getDate() + 2);
			
			new_checkout_min = format_date_ymd( new_checkout_min );
			new_checkout_min_show = format_date_ymd( new_checkout_min_show );
			
			$(".utalii_hotel_search_wrap .checkoutdate").datepicker('option', 'minDate', new_checkout_min );
			$(".utalii_hotel_search_wrap .checkoutdate").val(new_checkout_min_show);
		}
	});
	$(".utalii_hotel_search_wrap .checkoutdate").datepicker({
		dateFormat: 'MM dd, yy',
		changeMonth: true,
		changeYear: true,
		minDate: checkoutdate,
		maxDate: max_date
	});
	
	$("#utalii_hotel_search_location").change(function(){
		curr_location = $(this).val();
		curr_location = $.trim( curr_location );
		
		load_hotels( curr_location );
	});
	/* 
	$("#utalii_hotel_search_hotel").change(function(){
		curr_hotel_id = $(this).val();
		curr_hotel_id = $.trim( curr_hotel_id );
		
		load_rooms( curr_hotel_id );
	});
	 */
	$("#utalii_hotel_search_options_button").click(function(){
		$("#utalii_hotel_search_options").slideToggle({
			done: function(){
				if( $("#utalii_hotel_search_options").is(':visible') ){
					$("#utalii_hotel_search_options_button").html('- Hide Options');
				} else {
					$("#utalii_hotel_search_options_button").html('+ Show Options');
				}
			}
		});
		
	});
	
	/* Checkout code started*/
	/* Login on checkout page.*/
	$("#utalii_checkout_login_frm").submit(function(e) {
        e.preventDefault();
		$("#utalii_checkout_l_loader").show();
		var utalii_username = $("#utalii_checkout_login_username").val();
		var utalii_password = $("#utalii_checkout_login_password").val();
		$.ajax({
			url: utalii_hotel_search_form_obj.admin_ajax_url,
			method: 'POST',
			dataType: 'json',
			data: {
				'action': 'utaliiajax_login_user_on_checkout',
				'username': utalii_username,
				'password': utalii_password
			}
		}).done(function(r){
			if( r.success )
			{
				if(r.data.rcode == 1)
				{
					//location.href = location.href;
					$("#utalii_make_user_login").submit();
				}
				else
				{
					$("#Login_error").html(r.data.msg);
				}
			}
			else
			{
				$("#Login_error").html("Something went wrong! Please try again.");
			}
			$("#utalii_checkout_l_loader").hide();
		}).fail(function( jqXHR, textStatus ){
			$("#utalii_checkout_l_loader").hide();
			$("#Login_error").html("Something went wrong! Please try again.");
		});
    });
	
	/* Registration on checkout page.*/
	$("#utalii_checkout_registration_frm").submit(function(e) {
		e.preventDefault();
		$("#utalii_checkout_r_loader").show();
		var utalii_checkout_r_title = $("#utalii_checkout_r_title").val();
		var utalii_checkout_r_f_name = $("#utalii_checkout_r_f_name").val();
		var utalii_checkout_r_l_name = $("#utalii_checkout_r_l_name").val();
		var utalii_checkout_r_address = $("#utalii_checkout_r_address").val();
		var utalii_checkout_r_city = $("#utalii_checkout_r_city").val();
		var utalii_checkout_r_state = $("#utalii_checkout_r_state").val();
		var utalii_checkout_r_pcode = $("#utalii_checkout_r_pcode").val();
		var utalii_checkout_r_country = $("#utalii_checkout_r_country").val();
		var utalii_checkout_r_phone = $("#utalii_checkout_r_phone").val();
		var utalii_checkout_r_id_type = $("#utalii_checkout_r_id_type").val();
		var utalii_checkout_r_id_number = $("#utalii_checkout_r_id_number").val();
		var utalii_checkout_r_email = $("#utalii_checkout_r_email").val();
		var utalii_checkout_r_password = $("#utalii_checkout_r_password").val();
		
		$.ajax({
			url: utalii_hotel_search_form_obj.admin_ajax_url,
			method: 'POST',
			dataType: 'json',
			data: {
				'action': 'utaliiajax_register_user_on_checkout',
				'utalii_checkout_r_title': utalii_checkout_r_title,
				'utalii_checkout_r_f_name': utalii_checkout_r_f_name,
				'utalii_checkout_r_l_name': utalii_checkout_r_l_name,
				'utalii_checkout_r_address': utalii_checkout_r_address,
				'utalii_checkout_r_city': utalii_checkout_r_city,
				'utalii_checkout_r_state': utalii_checkout_r_state,
				'utalii_checkout_r_pcode': utalii_checkout_r_pcode,
				'utalii_checkout_r_country': utalii_checkout_r_country,
				'utalii_checkout_r_phone': utalii_checkout_r_phone,
				'utalii_checkout_r_id_type': utalii_checkout_r_id_type,
				'utalii_checkout_r_id_number': utalii_checkout_r_id_number,
				'utalii_checkout_r_email': utalii_checkout_r_email,
				'utalii_checkout_r_password': utalii_checkout_r_password
			}
		}).done(function(r){
			if( r.success )
			{
				if(r.data.rcode == 1)
				{
					//location.href = location.href;
					$("#utalii_make_user_login").submit();
				}
				else
				{
					$("#register_error").html(r.data.msg);
				}
			}
			else
			{
				$("#register_error").html("Something went wrong! Please try again.");
			}
			$("#utalii_checkout_r_loader").hide();
		}).fail(function( jqXHR, textStatus ){
			$("#utalii_checkout_r_loader").hide();
			$("#register_error").html("Something went wrong! Please try again.");
		});
	});
	
	/* getting available rooms */
	$(".utalii_adult_stay").change(function(){
		var roomid = $(this).attr("data-roomid");
		var adult_stay = $(this).val();
		var children_stay = $("#utalii_children_stay_"+roomid).val();
		var checkindate = $("#utalii_checkindate").val();
		var checkoutdate = $("#utalii_checkoutdate").val();
		
		if( adult_stay > 0 || children_stay > 0 ){
			get_available_rooms(roomid, adult_stay, children_stay, checkindate, checkoutdate);
		} else {
			$("#utalii_rooms_required_"+roomid).html( '0' );
			$("#select_booking_"+roomid).prop("checked",false);
			$("#select_booking_"+roomid).prop("disabled",true);
		}
	});
	
	$(".utalii_children_stay").change(function(){
		var roomid = $(this).attr("data-roomid");
		var adult_stay = $("#utalii_adult_stay_"+roomid).val();
		var children_stay = $(this).val();
		var checkindate = $("#utalii_checkindate").val();
		var checkoutdate = $("#utalii_checkoutdate").val();
		
		if( adult_stay > 0 || children_stay > 0 ){
			get_available_rooms(roomid, adult_stay, children_stay, checkindate, checkoutdate);
		} else {
			$("#utalii_rooms_required_"+roomid).html( '0' );
			$("#select_booking_"+roomid).prop("checked",false);
			$("#select_booking_"+roomid).prop("disabled",true);
		}
	});
	/* getting available rooms, start */
	
	/* Checkout code ends here.*/
	
	/* functions, start */
	function format_date_ymd(date) {
		var d = new Date(date),
			month = '' + (d.getMonth() + 1),
			day = '' + d.getDate(),
			year = d.getFullYear();

		if (month.length < 2) month = '0' + month;
		if (day.length < 2) day = '0' + day;
		
		month = get_month_name( ( parseInt(month) - 1 ) );
		return ( month + ' ' + day + ', ' + year );
	}
	
	function get_month_name(monthnumber){
		var m = new Array();
		m[0] = "January";
		m[1] = "February";
		m[2] = "March";
		m[3] = "April";
		m[4] = "May";
		m[5] = "June";
		m[6] = "July";
		m[7] = "August";
		m[8] = "September";
		m[9] = "October";
		m[10] = "November";
		m[11] = "December";
		return m[monthnumber];
	}
	
	function add_query_url(uri, key, value) {
		var i = uri.indexOf('#');
		var hash = i === -1 ? ''  : uri.substr(i);
		uri = i === -1 ? uri : uri.substr(0, i);
		
		var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
		var separator = uri.indexOf('?') !== -1 ? "&" : "?";
		
		if (uri.match(re)) {
			uri = uri.replace(re, '$1' + key + "=" + value + '$2');
		} else {
			uri = uri + separator + key + "=" + value;
		}
		
		return uri + hash;  // finally append the hash as well
	}
	
	function get_available_rooms(roomid, adult_stay, children_stay, checkindate, checkoutdate){
		var rooms_required = $("#utalii_rooms_required_"+roomid);
		var ajaxloader = $("#hs_hotel_ajax_loader_"+roomid);
		var rooms_required_input = $("#rooms_required_input_"+roomid);
		
		rooms_required.html('');
		ajaxloader.css("display","inline-block");
		
		$.ajax({
			url: utalii_hotel_search_form_obj.admin_ajax_url,
			type: "POST",
			data: {
				action : 'utaliiajax_get_available_rooms',
				roomid : roomid,
				adult_stay : adult_stay,
				children_stay : children_stay,
				checkindate : checkindate,
				checkoutdate : checkoutdate
			},
			dataType: "json"
		}).done(function( r ) {
			if( r.success ){
				var required_rooms = r.data.required_rooms;
				console.log( required_rooms );
				rooms_required.html( required_rooms );
				rooms_required_input.val(required_rooms);
				
				ajaxloader.css("display","none");
				$("#select_booking_"+roomid).prop("disabled",false);
				$("#select_booking_"+roomid).prop("checked",true);
			} else {
				$("#select_booking_"+roomid).prop("checked",false);
				$("#select_booking_"+roomid).prop("disabled",true);
				rooms_required.html( r.data.content );
				rooms_required_input.val('');
				
				ajaxloader.css("display","none");
			}
		}).fail(function( jqXHR, textStatus ) {
			console.log('-- error start --');
			console.log(jqXHR);
			console.log(textStatus);
			console.log('-- error stop --');
			rooms_required.html( textStatus );
			ajaxloader.css("display","none");
		});
	}
	
	function load_hotels( loc ){
		if( curr_location != '' ){
			ajax_loader = $(".hs_hotel_ajax_loader");
			
			ajax_loader.css('display', 'inline-block');
			
			$( "#utalii_hotel_search_hotel" ).prop( "disabled", false );
			
			$.ajax({
				url: utalii_hotel_search_form_obj.admin_ajax_url,
				method: 'POST',
				dataType: 'json',
				data: {
					'action': 'utaliiajax_load_hotels_by_location_id',
					'location_id': loc
				}
			}).done(function(r){
				if( r.success ){
					html	=	'<option value="">-Choose Hotel-</option>';
					$.each(r.data, function(key, value){
						id = value.id;
						title = value.title;
						html	+=	'<option value="' + id + '">' + title + '</option>';
					});
					
					$( "#utalii_hotel_search_hotel" ).html(html);
				}
				
				ajax_loader.css('display', 'none');
				
			}).fail(function( jqXHR, textStatus ){
				//console.log(jqXHR);
				//console.log(textStatus);
				ajax_loader.css('display', 'none');
			});
			
		} else {
			$( "#utalii_hotel_search_hotel" ).prop( "disabled", true );
		}
	}
	
	function load_rooms( hotel_id ){
		if( hotel_id != '' ){
			ajax_loader = $(".hs_room_ajax_loader");
			
			ajax_loader.css('display', 'inline-block');
			
			$( "#utalii_hotel_search_room" ).prop( "disabled", false );
			
			$.ajax({
				url: utalii_hotel_search_form_obj.admin_ajax_url,
				method: 'POST',
				dataType: 'json',
				data: {
					'action': 'utaliiajax_load_rooms_by_hotel_id',
					'hotel_id': hotel_id
				}
			}).done(function(r){
				if( r.success ){
					html	=	'<option value="">-Choose Room-</option>';
					$.each(r.data, function(key, value){
						id = value.id;
						title = value.title;
						html	+=	'<option value="' + id + '">' + title + '</option>';
					});
					
					$( "#utalii_hotel_search_room" ).html(html);
				}
				
				ajax_loader.css('display', 'none');
				
			}).fail(function( jqXHR, textStatus ){
				//console.log(jqXHR);
				//console.log(textStatus);
				ajax_loader.css('display', 'none');
			});
			
		} else {
			$( "#utalii_hotel_search_room" ).prop( "disabled", true );
		}
	}
	
	/* functions, stop */
	
});