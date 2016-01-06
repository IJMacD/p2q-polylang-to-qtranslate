
var frm = jQuery('#p2q-form');

frm.submit(function (ev) {
	ev.preventDefault();

	if (!confirm("Are you sure you've made a backup of your data?"))
		return;

	jQuery('#p2q .spinner').show();
	jQuery('#p2q-submit').attr('disabled', 'disabled');

	jQuery("#p2q-warnings ul").empty();
	jQuery("#p2q-progress").empty();

	var data = {};
	jQuery(this).serializeArray().map(function(item) {
		data[item.name] = item.value;
	});
	submit(data);
});


function submit(data) {
	jQuery.ajax({
		type: 'post',
		url: ajaxurl,
		data: data,
		success: function (response) {

			try {
				jsonResult = jQuery.parseJSON(response);

				if (jsonResult.fatal) {
					console.log("Fatal error: " + jsonResult.fatal);
					jQuery("#p2q-warnings ul").append( jQuery('<li class="p2q-error" />' ).text( "Fatal error: " + jsonResult.fatal) );
				} else {
					jQuery.each(jsonResult.warnings, function(i, val) {
						jQuery("#p2q-warnings ul").append( jQuery('<li class="p2q-warning" />' ).text( "Warning: " + val ) );
					});

					jQuery("#p2q-progress").text( jsonResult.message );
				}

				if (jsonResult.continue) {
					//Remember all the variables
					for (var attrname in jsonResult) { data[attrname] = jsonResult[attrname]; }

					submit(data);
					return;
				}
			}
			catch (e) {
				console.log(e);
				jQuery('#p2q-warnings ul').append( jQuery('<li class="p2q-error" />' ).text( "Fatal error: " + response ) );
			};

			jQuery('#p2q .spinner').hide();
			jQuery('#p2q-submit').removeAttr('disabled');
		}
	});
}
/*
function ajaxSubmit ($) {

		var data =  jQuery(this).serialize();
		//data = {  action: 'p2q_perform', p2q_nonce : p2q_vars.p2q_nonce };

		var perform = function() {

		$.post(ajaxurl, data, function(response) {

			try {
				jsonResult = JSON.parse(response);

				if (jsonResult.fatal)
					$('#p2q-progress').html(response.fatal);
				else {
					jQuery.each(jsonResult.warnings, function(i, val) {
					  $("#p2q-warnings").append( $('<li/>', {text: val }));
					});

					$('#p2q-progress').html(jsonResult.message);
				}

				if (jsonResult.continue)
					perform();
			}
			catch (e) {
				console.log("error: "+e);
				$('#p2q-test').html("Failure");
			};

			$('#p2q .spinner').hide();

		} );

		};

		$('#p2q .spinner').show();
		perform();
		return false;

}
*/
