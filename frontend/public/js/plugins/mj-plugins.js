/**
* Majestic Interactive Javascript plugins
* author ettiene
*/

/**
 *	Custom Accordion
 */
(function (jQuery) {
	jQuery.fn.mj_accordion = function (params) {
		//set default options
		params = params || {
								disable_expand_all : false,
								disable_collapse_all : false,
								disable_form_display_section_tooltip_simulate : false,
							};
							
		//set some options
		var html_prepend_text = "";
		if (params.disable_expand_all != true)
		{
			html_prepend_text += "<span class=\"mj-accordion-expand-all btn btn-primary glyphicon glyphicon-menu-down\" title=\"Expand All\" data-toggle=\"tooltip\" style=\"margin-bottom: 10px;\"></span>&nbsp;";
		}//end if
		
		if (params.disable_collapse_all != true)
		{
			html_prepend_text += "<span class=\"mj-accordion-collapse-all btn btn-default glyphicon glyphicon-menu-up\" title=\"Collapse All\" data-toggle=\"tooltip\" style=\"margin-bottom: 10px;\"></span>";
		}//end if 
		
		if (html_prepend_text != "")
		{
			//add expand and collapse options
			jQuery(this).prepend(html_prepend_text);
		}//end if
		
		//set expand all functon
		jQuery(".mj-accordion-expand-all").click(function () {
			jQuery(this).parent().find(".m3-panel-subsection").removeClass("panel-default").addClass("panel-primary");
			jQuery(this).parent().find(".m3-panel-subsection-icon").removeClass("glyphicon-resize-full").addClass("glyphicon-resize-small");
			jQuery(this).parent().find(".m3-panel-subsection").next().show('fast');
		});
		
		//set collapse all function
		jQuery(".mj-accordion-collapse-all").click(function () {
			jQuery(this).parent().find(".m3-panel-subsection").removeClass("panel-primary").addClass("panel-default");
			jQuery(this).parent().find(".m3-panel-subsection-icon").removeClass("glyphicon-resize-small").addClass("glyphicon-resize-full");
			jQuery(this).parent().find(".m3-panel-subsection").next().hide('fast');
		});
		
	  jQuery(this).find(".m3-panel-subsection").click(function() {		  
		  	jQuery(this).toggleClass("panel-default");
		  	jQuery(this).toggleClass("panel-primary");
		  	
		  	if (jQuery(this).hasClass("panel-default"))
		  	{
		  		jQuery(this).find(".m3-panel-subsection-icon").removeClass("glyphicon-resize-small").addClass("glyphicon-resize-full");
		  	} else {
		  		jQuery(this).find(".m3-panel-subsection-icon").removeClass("glyphicon-resize-full").addClass("glyphicon-resize-small");
		  	}//end if
		  	
	      	jQuery(this).next().toggle('fast');
	      	return false;
		  }).next().hide();
	  
		//where forms are submitted, check for errors and expand required section
	  	jQuery(".help-inline").parent().parent().parent().prev().toggleClass("panel-default panel-danger").click();
	}; //end function
}(jQuery));

/**
 * Contact Toolkit Section
 * Generate Contact Toolkit Sections
 */
(function (jQuery) {
	jQuery.fn.mj_toolkit_section = function (url, arr_params) {
		//display toolkit section
		var container = jQuery(".mj3-view-toolkit-container");

		//display toolkit container
		container.css("display", "block");

		if (url != "")
		{
			//load content
			container.find(".mj3-toolkit").find("#mj3-toolkit-iframe").attr("src", url);
		}//end if
		
		//return container as object for later use
		return container;
	};
})(jQuery);

/**
 * Contact Toolkit Section
 * Generate content for specified contact toolkit section
 */
(function (jQuery) {
	jQuery.fn.mj_contact_toolkit_section_load = function (url, div_class) {
		//check if element should be loaded or closed
		if (!this.hasClass("section-loaded"))
		{
			this.addClass("section-loaded");
			
			//display wait message
			this.html("<img src=\"/img/images/animations/please_wait.gif\" />");
			//load the conatct
			this.load(url);
		}//end if
	};
})(jQuery);


/**
 * A very simple table style applyer which uses the configured ui theme
 */
(function (jQuery) {
	jQuery.fn.mj_table_simple_format = function ()
	{
		//apply styles to headers
		jQuery(this).find("th").each( function () {
			jQuery(this).addClass("ui-state-default");
		});
		
		//format each row col
		jQuery(this).find("td").each(function () {
			jQuery(this).addClass("ui-widget-content");
		});
		
		//format table row where on hover
		 jQuery(this).find("tr").hover(
			//on hover
			  function() {
			      jQuery(this).children("td").addClass("ui-state-hover");
			  },//end function
			  
			  //hover exists
			  function() {
			      jQuery(this).children("td").removeClass("ui-state-hover");
			  });
		 
		 //highlight row that are clicked
		 jQuery(this).find("tr").click(function() {
			   jQuery(this).children("td").toggleClass("ui-state-highlight");
		 });
	}; //end function
})(jQuery);

/**
 * Enable drag and drop elements
 */
(function (jQuery) {
	jQuery.fn.mj_drag_and_drop = function () {
		//enable draggable elements
		jQuery(".draggable").draggable({ cursor: "move", helper: "clone", start: function (e, ui) {
			jQuery(ui.helper).css("padding", "5");
			jQuery(ui.helper).addClass("ui-state-active");
			jQuery(ui.helper).addClass("shadow ui-corner-all");
		}});
		
		//enable droppable elements
		jQuery(".droppable").droppable({
			over : function (event, ui) {
				jQuery(ui.helper).removeClass("shadow");
			}, //end over
			
			out : function (event, ui) {
				jQuery(ui.helper).addClass("shadow");
			}, //end out
			
			drop : function (event, ui) {
				if (jQuery(ui.draggable).attr("data-drag-and-drop-value") != "")
				{
					jQuery(this).val(jQuery(this).val() + jQuery(ui.draggable).attr("data-drag-and-drop-value"));
					return;
				}//end if

				jQuery(this).val(jQuery(this).val() + ui.draggable.text());
			} // end drop
		});

		//enable text editor elements
		jQuery("#content_ifr, .text-editor-droppable").droppable({
			over : function (event, ui) {
				jQuery(ui.helper).removeClass("shadow");
			}, //end over
			
			out : function (event, ui) {
				jQuery(ui.helper).addClass("shadow");
			}, //end out
			
			drop : function (event, ui) {
				 //Dynamically add content
				if (jQuery(ui.draggable).attr("data-drag-and-drop-value") != "")
				{
					var text = jQuery(ui.draggable).attr("data-drag-and-drop-value");
				} else {
					var text = ui.draggable.text();
				}//end if
		
		        tinyMCE.activeEditor.execCommand('mceInsertContent', false, text);
			} //end drop
		});
	};//end function
})(jQuery);

/**
 * Plugin catering configuration of behaviours
 */
(function (jQuery) {
	jQuery.fn.mj_behaviours_view = function () {
		//monitor add link click
		jQuery(this).click(function () {
			//get parent section for later use
			var parent_element = jQuery(this).parent();

			//check if manage section is specified
			//@TODO
				
			//load content for div
			var source_html = "<div id=\"iframe_behaviours_configure\"><iframe width=\"568\" height=\"600\" src=\"" + jQuery(this).attr("href") + "\" seamless style=\"border: none;\"</iframe></div>";
			jQuery("#manage-behaviours-section").html('<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">Behaviours</h4></div><div class="modal-body"> ' + source_html + '</div><div class="clearboth"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div></div><!-- /.modal-content --></div><!-- /.modal-dialog -->');
			jQuery("#manage-behaviours-section").modal('show');
			
			//prevent browser loading link
			return false;
		});
	};
}) (jQuery);

/**
* Plugin to load uploaded files list
*/
(function (jQuery) {
	jQuery.fn.mj_files_list = function (objParams) {
		//add icon to input field
		var element = jQuery(this);
		element.parent().append(jQuery("<div></div>").attr("class", "files_dialog_box").css("display", "none").append(jQuery("<select></select>").append(jQuery("<option></option>").text("--select--")).attr("id", "file_select").on("change", function() {
			if (jQuery(this).val() != "")
			{
				element.val(jQuery(this).val());
			}//end if
		})));
		
		element.parent().append("&nbsp;<span parent=\"" + jQuery(this).attr("id") + "\" id=\"element-load-files-list\"><img height='40px' width='40px' src=\"//cdn-aws.majestic3.com/images/m3frontend/icons/icon_css.png\"/></span>")
			.on("click", function () {
				jQuery.ajax({
					url: objParams.ajax_url
				})
				.done(function (data) {
					var objData = jQuery.parseJSON(data);
					if (objData.error == 1)
					{
						console.log(objData.response);
						//return false;
					}//end if
					
					//poplulate files into dropdown
					jQuery.each(objData.files, function (i, obj) {
						element.parent().find("#file_select").append(jQuery("<option></option>").val(obj.url).text(obj.location));
					});
					
					element.parent().find(".files_dialog_box").css("visibility", "visible").dialog({
						title: "Select File",
						modal: true,
						buttons: {
							"Close": function () {								
								jQuery(this).dialog("close");
							}//end close button
						}//end buttons
					});
				})
				.fail(function () {
					//alert("An unknown error has occured. Files could not be loaded");
					console.log('An unknown error has occured. Files could not be loaded');
				});
			});
		
	};
})(jQuery);

/**
 * Create docking content blocks and anchor to set regions
 * @param jQuery
 */
(function (jQuery) {
	jQuery.mj_docker = function (params) {
		//set default options
		params = params || {
								dock_collapse : true,
								dock_title : "My Docker Panel",
							};
							
		//set default dock region
		if (params.dock_region == undefined)
		{
			params.dock_region = "m3-content-section-east";
		}//end if
		
		//create content block
		var random = Math.random().toString(36).replace(".", "");
		var parent_block 					= jQuery("<div/>").attr("class", "panel panel-default");
		var parent_block_heading 			= jQuery("<div/>").attr("class", "panel-heading");
		var parent_block_body_container 	= jQuery("<div/>").attr("class", "panel-collapse collapse in").attr("id", random);
		var parent_block_body 				= jQuery("<div/>").attr("class", "panel-body").attr("id", "dock_content_" + random);
		
		//set header content
		if (params.dock_title != undefined && params.dock_title != false)
		{
			parent_block.addClass("dock-" + (params.dock_title).replace(" ", "-").toLowerCase());
			if (params.dock_collapse == true)
			{
				var collapse_link = jQuery("<a/>").attr("data-toggle", "collapse").attr("href", "#" + random).html(params.dock_title);
				parent_block_heading.html("<h4 class=\"panel-title\"></h4>").find("h4").append(collapse_link);
			} else {
				parent_block_heading.html("<h4 class=\"panel-title\">" + params.dock_title + "</h4>");
			}//end if
			
			//add header to panel
			parent_block.append(parent_block_heading);
		}//end if
		
		//set body content
		parent_block_body.html(global_wait_image);
		
		//compile the content
		parent_block_body_container.append(parent_block_body);
		parent_block.append(parent_block_body_container);
		
		//append content to docker region
		if (params.dock_position == undefined)
		{
			jQuery("." + params.dock_region).append(parent_block);
		} else {
			if (params.dock_position == 0)
			{
				jQuery("." + params.dock_region).prepend(parent_block);
			} else {
				//check if this many blocks have been docked already
				if (jQuery("." + params.dock_region + " :nth-child(" + params.dock_position + ")").html() == undefined)
				{
					jQuery("." + params.dock_region).find(".panel").last().after(parent_block);
				} else {
					jQuery("." + params.dock_region + " :nth-child(" + params.dock_position + ")").after(parent_block);
				}//end if
			}//end if
		}//end if
		
		var target_element = jQuery("#dock_content_" + random);
	
		//load panel body source
		if (params.dock_content != undefined)
		{
			//predefined content
			target_element.html(params.dock_content);
		} else {
			if (params.dock_ajax_url == undefined)
			{
				target_element.html("Content source not specified");
				return false;
			}//end if
			
			//load panel body content via ajax
			jQuery.ajax({
				url: params.dock_ajax_url
			})
			.done(function (data) {
				target_element.html(data);
				
				if (jQuery.isFunction(params.dock_ajax_complete_callback))
				{
					params.dock_ajax_complete_callback();
				}//end if
			})
			.fail(function () {
				target_element.html("Content could not be loaded");
				
				if (jQuery.isFunction(params.dock_ajax_failed_callback))
				{
					params.dock_ajax_failed_callback();
				}//end if
			});
		}//end if
	};
})(jQuery);



(function (jQuery) {
	jQuery.mj_text_editor = function (params)
	{
		var mj_plugins = "";	
		
		if (params.enable_replace_fields != undefined && params.enable_replace_fields == true)
		{
			jQuery.mj_text_editor_extend_replace_fields(params);
			mj_plugins = mj_plugins + " mj_replace_fields ";
		}//end if
		
		if (params.enable_uploaded_images != undefined && params.enable_uploaded_images == true)
		{
			jQuery.mj_text_editor_extend_uploaded_images(params);
			mj_plugins = mj_plugins + " mj_uploaded_images ";
		}//end if
		
		if (params.enable_templates != undefined && params.enable_templates == true)
		{
			jQuery.mj_text_editor_extend_templates(params);
			mj_plugins = mj_plugins + " mj_templates";
		}//end if
		
		var element = jQuery(params.element);
		var editor = element.tinymce({
				 menubar: "format view edit"
			    ,plugins: [
					        "autolink paste code link textcolor colorpicker image imagetools visualblocks visualchars nonbreaking contextmenu advlist table fullscreen colorpicker " + mj_plugins
					    ]
			    ,toolbar1: "undo redo | styleselect fontselect fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify table | bullist numlist outdent indent | link image | print preview code | forecolor backcolor "
			    //majestic plugins
			    ,toolbar2: mj_plugins
			    //add the advanced tab in the native image box
			    ,image_advtab: true	
			    ,allow_html_in_named_anchor: true
				,paste_auto_cleanup_on_paste : true
				,paste_data_images: false //prevent images from being dragged into editor
				,paste_strip_class_attributes : "all"
				,paste_remove_spans : "all"
				,paste_remove_styles : true
				,theme_advanced_font_sizes : "8pt=1,10pt=2,12pt=3,14pt=4,18pt=5,24pt=6,36pt=7"
				,font_size_style_values : "8pt,10pt,12pt,14pt,18pt,24pt,36pt"
				,relative_urls : false
				,remove_script_host : false
				,forced_root_block : false
			    //manage the images browse icon click
//			    file_browser_callback: function(field_name, url, type, win) {
//
//			    }
		});
		
		var div = jQuery("<div />", {
		    html: '<style>div .mce-fullscreen {top: 50px;}</style>'
		  }).appendTo("body");   
	};
})(jQuery);

/**
 * Extend editor - images
 * @param jQuery
 */
(function (jQuery) {
	jQuery.mj_text_editor_extend_uploaded_images = function(params) {
		tinymce.PluginManager.add('mj_uploaded_images', function(editor, url) {
		    // Add a button that opens a window
			editor.addButton('mj_uploaded_images', {
				text: 'Uploaded Images',
				icon: false,
				onclick: function() {
					jQuery(".comms-modal-image").dblclick(function (e) {
						var title = window.prompt("Please set description", "Image");
						editor.insertContent("<img alt=\"" + title + "\" title=\"" + title + "\" src=\"" + jQuery(this).attr("src") + "\" />");
						jQuery(".uploaded-images-modal").modal("toggle");						
					});
					
					jQuery(".uploaded-images-modal").modal("toggle");
				}
			});
		});
	};
}) (jQuery);

/**
 * Extend editor - replace fields
 * @param jQuery
 */
(function (jQuery) {
	jQuery.mj_text_editor_extend_replace_fields = function(params) {
		tinymce.PluginManager.add('mj_replace_fields', function(editor, url) {
		    // Add a button that opens a window
			editor.addButton('mj_replace_fields', {
				text: 'Replace Fields',
				icon: false,
				onclick: function() {
					var html_section = jQuery(".dock-replace-fields").html();
					
					//inject replace fields html
					jQuery(".replace-fields-modal").find(".modal-dialog").find(".modal-content").find(".modal-body").html(html_section).find(".replace-field-item").attr("title", "Double click to add");
					
					//add click event for fields
					jQuery(".replace-field-item").dblclick(function (e){
						var field = jQuery(this).attr("data-replace-field");
						
						/**
						 * Manipulate some replace fields
						 */
						//comm links
						var comm_link_field = "#comm.link";
						if (field.substr(0, comm_link_field.length) == comm_link_field)
						{
							field = "<a href=\"" + field + "\" class=\"\" style=\"\" target=\"_blank\" title=\"\">here</a>";
						}//end if
						
						//forms - populated
						var form_link_field = "#form.link";
						if (field.substr(0, form_link_field.length) == form_link_field)
						{
							field = "<a href=\"" + field + "\" class=\"\" style=\"\" target=\"_blank\" title=\"\">here</a>";
						}//end if
						
						//forms - blank
						var form_blank_link_field = "#form-unpopulated.link";
						if (field.substr(0, form_blank_link_field.length) == form_blank_link_field)
						{
							field = "<a href=\"" + field + "\" class=\"\" style=\"\" target=\"_blank\" title=\"\">here</a>";
						}//end if
						
						editor.insertContent(field + "&nbsp;");
						
						//close modal
						jQuery(".replace-fields-modal").modal("toggle");
					});
					
					//amend link and section ids for accordion, to aviod conflicts with docker panels
					var random_str = Math.random().toString(36).replace(".", "");
					jQuery.each(jQuery(".replace-fields-modal").find("a"), function (i, obj){
							if(jQuery(obj).attr("data-toggle") == "collapse")
							{
								var href = jQuery(obj).attr("href");
								//find child element and rename
								jQuery(".replace-fields-modal").find(href).attr("id", href.replace("#", "") + random_str);
								//now update link
								jQuery(obj).attr("href", href + random_str);
							}//end if
					});
					
					jQuery(".replace-fields-modal").modal("toggle");
				}
			});
		});
	};
}) (jQuery);

(function (jQuery) {
	jQuery.mj_text_editor_extend_templates = function (params) {
		tinymce.PluginManager.add('mj_templates', function(editor, url) {
		    // Add a button that opens a window
			editor.addButton('mj_templates', {
				text: 'Templates',
				icon: false,
				onclick: function() {					
					jQuery(".templates-modal").modal("toggle");
					
					jQuery("#use-template-loaded").click(function (e) {
						e.preventDefault();

		                var xmlhttp = new XMLHttpRequest();
		                xmlhttp.open("GET", jQuery("#template-name").val(), false);
		                xmlhttp.send();
		                editor.setContent(xmlhttp.responseText);
						
						//close modal
						jQuery(".templates-modal").modal("hide");
					});
				}
			});
		});
	};
})(jQuery);
