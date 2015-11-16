function generateCommObject(options)
{
	var comm = new joint.shapes.basic.Rect({
	    position: { x: options.x, y: options.y },
	    size: { width: options.width, height: options.height },
	    attrs: { rect: { fill: options.fill },
		    		text: { text: options.text_label, fill: options.text_fill }
				}
	});
	
	return comm;
}//end function