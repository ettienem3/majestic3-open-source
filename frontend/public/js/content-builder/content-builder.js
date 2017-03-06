	jQuery(document).ready(function () {
		//load editor block capabilities
		jQuery("#main-editor .editor").each(function (i, obj) {
			jQuery(obj).editorBlock();
			jQuery(obj).attr("id", "editor_id_" + i);
			//add ids to each block for easier mapping later on
		});

		jQuery("#clone-preview .editor").each(function (i, obj) {
			//add ids to each block for easier mapping later on
			jQuery(obj).attr("id", "editor_clone_id_" + i);
		});
		
		//monitor ceditor block buttons
		jQuery(".button_clear").click(function() {
			buttonClear(jQuery(this).parent().parent());
		});

		jQuery(".button_edit").click(function() {
			buttonEdit(jQuery(this).parent().parent());
		});

		jQuery(".button_push").click(function() {
			buttonPush(jQuery(this).parent().parent());
		});

		jQuery("#export-template-html").click(function () {
			//trigger all the push button click actions to update template
			jQuery("button.button_push").click();
			
			var objHtml = jQuery("#clone-preview").clone();

			//remove editor div sections
//			jQuery.each(jQuery(objHtml).find("td"), function (i, obj) {
// 				var html = "";
//			
// 				html = jQuery(obj).find(".editor").html();
// 				
// 				if (typeof html === "undefined")
// 				{
// 					return;
// 				}//end if
//			
// 				jQuery(obj).find(".editor").html("").replaceWith(html);
//			});

			//remove editor div sections
			jQuery.each(jQuery(objHtml).find(".editor"), function (i, obj) {
				jQuery(obj).replaceWith(jQuery(obj).html());
			});
			
			//write content to file
			jQuery.ajax({
				url: html_write_doc_url,
				type: "POST",
				async: false,
				data: {
					html: jQuery(objHtml).html()
				}
			})
			.done(function (result) {
				var objResponse = jQuery.parseJSON(result);
				
				if (objResponse.error == 1)
				{
					alert("An error occured. File could not be created");
					return false;
				}//end if

				window.open(objResponse.data.url);
			})
			.fail(function () {
				alert("An unknown error occurred generating the content file. Once you click ok, another window will appear with the html for you to copy");
				alert(jQuery(objHtml).html());
			});
		});
		
		//enable draggable elements
		jQuery(".draggable").draggable();
		//enable resizable elements
		jQuery(".resizable").resizable();

		//enable content drag and drop
		//enable draggable elements
		jQuery(".draggable-content").draggable({ cursor: "move", helper: "clone", start: function (e, ui) {
			jQuery(ui.helper).css("padding", "5");
			jQuery(ui.helper).addClass("ui-state-active");
			jQuery(ui.helper).addClass("shadow ui-corner-all");
		}});
		
		//enable tinymce content block
		jQuery("textarea.tinymce").tinymce({
				theme: "advanced"
				,plugins : "advlink,advimage,contextmenu,table,searchreplace,paste,-replace_fields,fullscreen"
				,theme_advanced_buttons1 : "bold,italic,underline,strikethrough,forecolor,backcolor,|,bullist,numlist,|,link,unlink,anchor,|,undo,redo,cleanup,removeformat,|,pastetext,pasteword,|,visualaid,code,help,|,fullscreen"
				,theme_advanced_buttons2 : "fontselect,fontsizeselect,|,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,replace,charmap,|,outdent,indent,|,image"
				,theme_advanced_buttons3 : "tablecontrols,'|',replace_fields_box"
				,theme_advanced_resizing : true
				,theme_advanced_toolbar_location : "top"
				,theme_advanced_statusbar_location : "bottom"
				,theme_advanced_toolbar_align : "left"
				,paste_auto_cleanup_on_paste : true
				,paste_strip_class_attributes : "all"
				,paste_remove_spans : "all"
				,paste_remove_styles : true
				,content_css : "http://styles.majesticinteractive.co.za/tinymce.css"
				,theme_advanced_font_sizes : "8pt=1,10pt=2,12pt=3,14pt=4,18pt=5,24pt=6,36pt=7"
				,font_size_style_values : "8pt,10pt,12pt,14pt,18pt,24pt,36pt"
				,relative_urls : false
				,remove_script_host : false
				,forced_root_block : false
		});

		//enable droppable elements
		jQuery(".droppable-content").droppable({
			over : function (event, ui) {
				jQuery(ui.helper).removeClass("shadow");
			}, //end over
			
			out : function (event, ui) {
				jQuery(ui.helper).addClass("shadow");
			}, //end out
			
			drop : function (event, ui) {	
				if (jQuery(this).hasClass("link-to-tinymce"))
				{
					if (ui.draggable.text() == "")
					{
						//use html
						var value = ui.draggable.html();
					} else {
						var value = ui.draggable.text();
					}//end if
					
					if (jQuery(ui.draggable).hasClass("draggable-value"))
					{
						value = jQuery(ui.draggable).attr("value");
					}//end if
					
					jQuery(this).parent().prev().find("textarea").tinymce().execCommand('mceInsertContent', false, value);
					return false;
				}//end if
				
				if (jQuery(ui.draggable).hasClass("draggable-value"))
				{
					jQuery(this).append(" " + jQuery(ui.draggable).attr("value"));
				} else {
					//if this is an image block, replace all html
					if (jQuery(this).parent().parent().find("div").hasClass("editor-image"))
					{
						var element = jQuery(this);
						element.html(ui.draggable.html());
						var image = element.find("img");
								
						//set form properties
						jQuery("#dialog-image-settings").find(".image-height").attr("value", image.css("height"));
						jQuery("#dialog-image-settings").find(".image-width").attr("value", image.css("width"));
						jQuery("#dialog-image-settings").find(".image-alt").attr("value", "");

						//update parent element dimensions
						element.css("height", image.css("height")).css("width", image.css("width"));
						
						//set image properties
						jQuery("#dialog-image-settings").css("visibility", "visible").dialog({
							modal: true,
							title: "Image Properties",
							buttons: {
								"Save" : function () {
									image.css("height", jQuery("#dialog-image-settings").find(".image-height").val());
									image.css("width", jQuery("#dialog-image-settings").find(".image-width").val());
									image.attr("alt", jQuery("#dialog-image-settings").find(".image-alt").val());

									if (jQuery("#dialog-image-settings").find(".image-make-link").is(":checked"))
									{
										element.html('<a href="' + jQuery("#image-url").val() + '">' + element.html() + '</a>');
									}//end if
									
									//update parent element dimensions
									element.css("height", image.css("height")).css("width", image.css("width"));
									jQuery(this).dialog("close");
								}, //end save button

								"Cancel" : function () {
									jQuery(this).dialog("close");
								} //end cancel button
							}//end buttons
						});
						return;
					}//end if

					if (ui.draggable.text() == "")
					{
						//use html
						jQuery(this).append(" " + ui.draggable.html());
					} else {
						jQuery(this).append(" " + ui.draggable.text());
					}//end if
				}//end if
			} // end drop
		});
		
		//enable toolbox tabs
		jQuery("#main-editor-toolbar").tabs();

		//monitor ajax links
		jQuery("button.ajax").click(function () {


			var button_id = jQuery(this).attr("id");
			var element = jQuery(this).parent().find(".data");
			
			//set loading image
			element.html("<div style=\"text-align: center;\"><h2>Loading...</h2><img src=\"/img/images/animations/please_wait.gif\"/></div>");
			
			//update button text
			jQuery(this).html("Refresh");
			var ajax_load_data_url = objUrls[button_id];
			if (button_id == "images")
			{
				if (jQuery("#mailer_images_folder").val() != "" && jQuery("#mailer_images_folder").val() != "undefinded")
				{
					ajax_load_data_url = ajax_load_data_url + "?folder=" + jQuery("#mailer_images_folder").val();
				}//end if
			}//end if
			
			jQuery.ajax({
				url : ajax_load_data_url,
				type: "GET",
				async: false
			})
			.done(function (response) {
				objData = jQuery.parseJSON(response);
				if (objData.error == 1)
				{
					alert("An error occured. " + objData.response);
					return false;
				}//end if

				//clear current data
				element.html("")


				var li = "";
				jQuery.each(objData.data, function (i, obj) {
					if (isNaN(i))
					{
						//set header
						li = li + "<li><br/><h2>" + i.replace("_", " ").toUpperCase() + "</h2><br/></li>";
						
						//extract section data
						jQuery.each(obj, function (ii, objField) {
							li = li + "<li class=\"draggable-content draggable-value\" value=\"" + objField.field + "\">" + objField.description + "</li>";
						});//end each
					} else {
						if (obj.field == "image")
						{
							li = li + "<li class=\"draggable-content\">" + obj.description + "</li>";
						} else {
							li = li + "<li class=\"draggable-content draggable-value\" value=\"" + obj.field + "\">" + obj.description + "</li>";
						}//end if
					}//end if
				});
				element.append("<ul class=\"drag-content toolbox-data-list \">" + li + "</ul>");

				jQuery(".draggable-content").draggable({ cursor: "move", helper: "clone", start: function (e, ui) {
					jQuery(ui.helper).css("padding", "5");
					jQuery(ui.helper).addClass("ui-state-active");
					jQuery(ui.helper).addClass("shadow ui-corner-all");
				}});
			})
			.fail(function () {

			});
		});
	});

	function shrinkSize(frameId, smallerWidth, smallerHeight) {
	    var frameOffset = $(frameId).offset(),
	        frameHeight = $(frameId).height()-smallerWidth,
	        frameWidth = $(frameId).width()-smallerHeight;

	    return ([frameOffset.left, frameOffset.top, frameOffset.left+frameWidth, frameOffset.top+frameHeight]);
	}

/**
 * 
 * Editor block clear, edit and review capabilities
 */
(function (jQuery) {
	jQuery.fn.editorBlock = function (params) {

		//set default options
		params = params || {
			disable_clear_button : false,
			disable_edit_button : false,
			disable_push_button : false
		}
		
		var button_div = jQuery("<div/>", {
			class: "editor-block-functions ui-corner-all"
		});
		
		//clear button
		if (params.disable_clear_button == false)
		{
			button_div.append("<button class=\"button_clear\">Clear</button>");
		} else {
			
		};
		
		//edit button
		if (params.disable_edit_button == false)
		{
			button_div.append("<button class=\"button_edit\">Edit</button>");
		} else {
			
		};
		
		//push button
		if (params.disable_push_button == false)
		{
			button_div.append("<button class=\"button_push\">Push</button>");
		} else {
			
		};

		//enable tinymce areas
		if (jQuery(this).hasClass("editor-text"))
		{
			jQuery(this).html("");
			jQuery("<div/>", {
				class: "editor-block-content",
				html: "<textarea class=\"droppable-content tinymce\">Type Content Here</textarea>"
			}).appendTo(jQuery(this));
			
			//append text block drop block
			jQuery("<div/>", {
				class: "editor-block-content-drop",
				html: "<div class=\"droppable-content link-to-tinymce\">Drop Content Here</div>"
			}).appendTo(jQuery(this));
		}//end if
		
		if (jQuery(this).hasClass("editor-image")) {
			jQuery("<div/>", {
				class: "editor-block-content droppable-content",
				html: "Drop Image Here",
				style: "height: 30px;"
			}).appendTo(jQuery(this));
		};
		
		//add buttons
		jQuery(this).prepend(button_div);
	}; //end function
}(jQuery))

/**
 * Button functions
 * @param element
 */
function buttonClear(element)
{
	var content_block = element.find(".editor-block-content");
	
	if (window.confirm("Are you sure you want to clear this block?"))
	{
		if (element.hasClass("editor-image"))
		{
			content_block.html("Drag Image Here...");
		}//end if
		
		if (element.hasClass("editor-text"))
		{
			element.find(".editor-block-content").find(".tinymce").tinymce().setContent("");
		}//end if
	}//end if
}//end function

function buttonEdit(element)
{

	if (jQuery(element).hasClass("editor-text"))
	{
		var html = element.find(".editor-block-content").find(".tinymce").tinymce().getContent();
	} else {
		var html = element.find(".editor-block-content").html();
	}//end if
	
	jQuery("#dialog-textarea .dialog-textarea-content").val(html);
	jQuery("#dialog-textarea").css("visibility", "visible");
	
	jQuery("#dialog-textarea").dialog({
		modal: true,
		width: "70%",
		title: "Edit Block Content",
		buttons : {
			"Update" : function () {
				//extract changes
				var html_updated = jQuery("#dialog-textarea .dialog-textarea-content").val();
				
				if (element.hasClass("editor-text"))
				{
					element.find(".editor-block-content").find(".tinymce").tinymce().setContent(html_updated);
				} else {
					element.find(".editor-block-content").html(html_updated);
				}//end if
				
				//resize element to wrap
				element.css({"height" : "auto", "width" : "auto"});
				
				//close dialog
				jQuery(this).dialog("close");
			}, //end save Button
			
			"Cancel" : function () {
				jQuery(this).dialog("close");
			} //end cancel button
		}
	});
}//end function

function buttonPush(element)
{
	var html = "";
	if (element.hasClass("editor-text"))
	{
		html = element.find(".editor-block-content").find(".tinymce").html();
	} else {
		html = element.find(".editor-block-content").html();
	}//end if
	
	//set clone element
	var clone_id = element.attr("id").replace("editor", "editor_clone");

	jQuery("#" + clone_id).html(html);
}//end function