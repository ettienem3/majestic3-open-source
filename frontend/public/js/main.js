jQuery(document).ready(function() {
	
	// toolbar
	var mj3toolkit = 0;
	jQuery('.mj3-js-toolkit').click(function(e) {
		e.preventDefault();
		if (mj3toolkit == 0) {
			jQuery('.mj3-toolkit').show();
			mj3toolkit = 1;
		} else {
			jQuery('.mj3-toolkit').hide();
			mj3toolkit = 0;
		}
	});
	
	//user toolbar
	var mj3usertoolkit = 0;
	jQuery('.mj3-js-user-toolkit').click(function(e) {
		e.preventDefault();
		if (mj3usertoolkit == 0) {
			jQuery('.mj3-user-toolkit').show();
			mj3usertoolkit = 1;
		} else {
			jQuery('.mj3-user-toolkit').hide();
			mj3usertoolkit = 0;
		}
	});

	//deal with links clicked in toolkit iframes which should load in parent page, should one day become one page type thing or something
	jQuery(".toolkit_iframe_parent_link").click(function (e) {
		e.preventDefault();
		window.parent.location.href = jQuery(this).attr("href");
	});
	
	//listen to menu clear cache link
	jQuery('.global_profile_cache_clear').click(function (e) {
		e.preventDefault();
		
		//display popup
		
		jQuery.ajax({
			'url': jQuery(this).attr('href'),
		})
		.done(function (data) {
			if (data.error == 0)
			{
				location.reload();
				return true;
			}//end if
			
			alert(data.response);
		})
		.fail(function () {
			alert('A problem has occured and cache could not be cleared. Please try again');
		});
	});
});
