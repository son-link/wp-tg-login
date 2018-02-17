function getQueryVariable(variable){
	var query = window.location.search.substring(1);
	var vars = query.split("&");
	for (var i=0;i<vars.length;i++) {
		var pair = vars[i].split("=");
		if(pair[0] == variable){return pair[1];}
	}
	return(false);
}

function tg_login_cb(user, type){
	jQuery('#tg-spinner').show();
	jQuery('#tg-login-msg').hide();
	jQuery('#tg-login-msg > span').empty();
	jQuery('#tg-login-msg').removeClass('error');
	jQuery.ajax({
		url:    tg_login_ajax.ajaxurl,
		method: 'GET',
		data:   { action: 'tg_ajax_login', user: user, type: type }
	}).done(function( response ) {
		jQuery('#tg-spinner').hide();

		data = response.data;
		if ( true === response.success ) {
			if(type != 'register'){
				redirect_to = getQueryVariable('redirect_to');
				if (redirect_to){
					window.location = decodeURIComponent(redirect_to);
				}else{
					window.location = data.base_url
				}
			}else{
				jQuery('#tg-login-msg > span').html(data.msg);
				jQuery('#tg-login-msg').removeClass('error');
				jQuery('#tg-login-msg').show();
			}
		} else {
			jQuery('#tg-login-msg > span').html(data.msg);
			jQuery('#tg-login-msg').addClass('error');
			jQuery('#tg-login-msg').show();
		}
	}).error(function(xhr, status, error){
		console.error(xhr);
		jQuery('#tg-login-msg').show();
		jQuery('#tg-login-msg > span').html('Ocurrio un error al obtener los datos del servidor.')
	});
}


jQuery(document).ready(function($) {
	$('#tg-login-msg > a').on('click', function(e) {
		e.preventDefault();
		$('#tg-login-msg').hide();
		$('#tg-login-msg').removeClass('error');
	});

	$('#unlink_tg').on('click', function(e) {
		e.preventDefault();
		tg_login_cb('', 'unlink')
	});
});
