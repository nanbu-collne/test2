jQuery(function($) {
	$("input").keypress(function(ev) {
		if ((ev.which && ev.which === 13) || (ev.keyCode && ev.keyCode === 13)) {
			return false;
		} else {
			return true;
		}
	});
});
