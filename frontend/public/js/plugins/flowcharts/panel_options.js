function generateOptionsPanel(element, base_html)
{
	//set options panel id
	var panel_id = element.attr("id") + "_options_panel";

	//check if panel is already loaded
	if(jQuery.inArray(panel_id, global_flowchart_arr_element_open_panels) !== -1)
	{
		return false;
	}//end if

	//keep track of open panels
	global_flowchart_arr_element_open_panels.push(panel_id);
	
	var element_top = element.position().top;
	var element_left = parseFloat(element.position().left + element[0].getBBox().width + 10);
	var new_element = jQuery("<div/>", {id: panel_id})
							.html(base_html)
							.css({
								top: element_top,
								left: element_left,
								position: "absolute",
								'z-index': 100
							})
							.hide()
							.show("fast");
		
	//add close event handler
	new_element.find(".button_close").click(function(e) {
		e.preventDefault();

		//remove panel from open panels array	
		global_flowchart_arr_element_open_panels.splice(jQuery.inArray(panel_id, global_flowchart_arr_element_open_panels),1);
		new_element.hide("fast").remove();
	});		
	
	return new_element;
}//end function