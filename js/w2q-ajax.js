	
var frm = jQuery('#w2q-form');

frm.submit(function (ev) {
	ev.preventDefault();
	
	if (!confirm("Are you sure you've made a backup of your data?"))
		return;
	
	jQuery('#w2q .spinner').show();
	jQuery('#w2q-submit').attr('disabled', 'disabled');
	
	jQuery("#w2q-warnings ul").empty();
	jQuery("#w2q-progress").empty();
	
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
					jQuery("#w2q-warnings ul").append( jQuery('<li class="w2q-error" />' ).text( "Fatal error: " + jsonResult.fatal) );
				} else {
					jQuery.each(jsonResult.warnings, function(i, val) {
						jQuery("#w2q-warnings ul").append( jQuery('<li class="w2q-warning" />' ).text( "Warning: " + val ) );
					});
					
					jQuery("#w2q-progress").text( jsonResult.message );
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
				jQuery('#w2q-warnings ul').append( jQuery('<li class="w2q-error" />' ).text( "Fatal error: " + response ) );
			};
			
			jQuery('#w2q .spinner').hide();			
			jQuery('#w2q-submit').removeAttr('disabled');
		}
	});
}
/*
function ajaxSubmit ($) {
	
		var data =  jQuery(this).serialize();
		//data = {  action: 'w2q_perform', w2q_nonce : w2q_vars.w2q_nonce };
		
		var perform = function() {
		
		$.post(ajaxurl, data, function(response) {
		
			try {
				jsonResult = JSON.parse(response);
				
				if (jsonResult.fatal)
					$('#w2q-progress').html(response.fatal);
				else {
					jQuery.each(jsonResult.warnings, function(i, val) {
					  $("#w2q-warnings").append( $('<li/>', {text: val }));
					});
					
					$('#w2q-progress').html(jsonResult.message);
				}
				
				if (jsonResult.continue)
					perform();
			}
			catch (e) {
				console.log("error: "+e);				
				$('#w2q-test').html("Failure");
			};
			
			$('#w2q .spinner').hide();				
	  
		} );
		
		};
		
		$('#w2q .spinner').show();
		perform();
		return false;

}
*/
