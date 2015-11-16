function generateJourneyObject(objJourney)
{
	var journey_block = new joint.shapes.custom.ElementLabelLink({
	    position: { x: 100, y: 30 },
	    size: { width: 150, height: 200 },
	    attrs: {
	        rect: { fill: '#9B59B6', stroke: '#8E44AD', 'stroke-width': 5 },
	        a: { 'xlink:href': '#journey' + objJourney.id, cursor: 'pointer', 'xlink:class':'journey_options_url' },
	        text: { text: objJourney.name, fill: 'white' }
		   }
	});

	return journey_block;
}//end function

function generateJourneyCommsContainer(options)
{
	var journey_comms_container = new joint.shapes.custom.ElementLabelLink({
	    position: { x: options.x, y: options.y },
	    size: { width: options.width, height: options.height },
	    attrs: {
		    	rect: { fill: options.fill },
		    	text: { text: options.text_label,  fill: options.text_fill },
		    }
	});
	
	return journey_comms_container;
}//end function
