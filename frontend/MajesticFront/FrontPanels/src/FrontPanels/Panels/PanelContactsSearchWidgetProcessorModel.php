<?php
namespace FrontPanels\Panels;

use FrontPanels\Entities\FrontPanelsPanelEntity;
use FrontPanels\Interfaces\InterfacePanelsProcessor;

class PanelContactsSearchWidgetProcessorModel extends AbstractPanelProcessorModel implements InterfacePanelsProcessor
{
	/**
	 * (non-PHPdoc)
	 * @see \FrontPanels\Interfaces\InterfacePanelsProcessor::processPanel()
	 */
	public function processPanel(FrontPanelsPanelEntity $objPanel)
	{
		$html = '
			<div id="advsearch">
				<div class="well clearfix">
					<form method="post" id="search-form" action="/front/contacts">
						<div class="form-group">
							<label for="keyword">Keyword:</label>
							<input type="text" class="form-control" name="keyword" id="keyword" placeholder="e.g. First Name / Email" value=""/>
						</div>

							<div class="col-md-6">
								<div class="form-group">
									<label for="regtbl_date_created_start">From date:</label>
									<input type="text" class="form-control" data-provide="datepicker" placeholder="from date" name="regtbl_date_created_start" readonly="readonly" value=""/>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label for="regtbl_date_created_end">To date:</label>
									<input type="text" class="form-control" data-provide="datepicker" placeholder="to date" name="regtbl_date_created_end" readonly="readonly" value=""/>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label for="regtbl_source">Source:</label>
									<select name="regtbl_source" id="regtbl_source" class="ajax-dropdown form-control">
										<option value="">--select--</option>
										<option value="_load_data_">Load Data</option>
									</select>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label for="regtbl_status">Contact status:</label>
									<select name="regtbl_status" id="regtbl_status" class="ajax-dropdown form-control">
										<option value="">--select--</option>
										<option value="_load_data_">Load Data</option>
									</select>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label for="regtbl_user">User:</label>
									<select name="regtbl_user" id="regtbl_user" class="ajax-dropdown form-control">
										<option value="">--select--</option>
										<option value="_load_data_">Load Data</option>
									</select>
								</div>
							</div>


						<input type="submit" value="Search" class="btn btn-primary pull-right"/>

					</form>
				</div>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery(".ajax-dropdown").change(function () {
						if (jQuery(this).val() == "_load_data_")
						{
							loadSearchFormData(jQuery(this).attr("id"), jQuery(this));
						}//end if
					});
		
					//monitor quick search button
					jQuery(".quick-search-button").click(function (e) {
						e.preventDefault();
		
						//populate values into search form
						jQuery("#search-form").find("#keyword").val(jQuery("#quick-search-keyword").val());
		
						//submit the form
						jQuery("#search-form").submit();
					});
				});
		
				function loadSearchFormData(param, element)
				{
					//set element data
					element.empty();
					element.append(jQuery("<option></option>").text("--loading--"));
		
					var param_url = "/front/contacts/ajax-search-values?param=#param";
		
					//request data
					jQuery.ajax({
						url: param_url.replace("#param", param),
					})
					.done(function (data) {
						var objData = jQuery.parseJSON(data);
		
						if (objData.error == 1)
						{
							//reset element
							element.append(jQuery("<option></option>").text("--select--"));
							element.append(jQuery("<option></option>").val("_load_data_").text("--select--"));
							alert("An error occured: " + objData.response);
							return false;
						}//end if
		
						element.empty();
						element.append(jQuery("<option></option>").val("").text("--select--"));
						jQuery.each(objData.response, function (i, obj) {
							element.append(jQuery("<option></option>").val(obj.id).text(obj.val));
						});
					})
					.fail(function () {
						alert("Data could not be loaded. An unkown error has occured");
						return false;
					});
				}//end function
			</script>';
		
		$html_id = str_replace(".", "", microtime(TRUE));
		
		$objPanel->set("html", $html);
		return $objPanel;
	}//end function
}//end class