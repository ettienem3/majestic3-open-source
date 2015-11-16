var global_flowchart_arr_elements = [];
var global_flowchart_arr_element_open_panels = [];

joint.shapes.custom = {};
joint.shapes.custom.ElementLabelLink = joint.shapes.basic.Rect.extend({
    // Note the `<a>` SVG element surrounding the rest of the markup.
    markup: '<g class="rotatable"><g class="scalable"><rect/></g><a><text/></a></g>',
    defaults: joint.util.deepSupplement({
        type: 'custom.ElementLabelLink'
    }, joint.shapes.basic.Rect.prototype.defaults)
});

function generateGraph(options)
{
	var paper = new joint.dia.Paper({
	    el: options.container,
	    width: options.width,
	    height: options.height,
	    model: options.graph,
	    gridSize: options.gridSize
	});
	
	return paper;
}//end function

function generateBasicElementRect()
{
	var rect = new joint.shapes.basic.Rect({
	    position: { x: 100, y: 30 },
	    size: { width: 100, height: 30 },
	    attrs: { rect: { fill: 'blue' }, text: { text: 'my box', fill: 'white' } }
	});
	return rect;
}//end function

//Helpers.
//--------

function buildGraphFromAdjacencyList(adjacencyList) {

 var elements = [];
 var links = [];
 
 _.each(adjacencyList, function(edges, parentElementLabel) {
     elements.push(makeElement(parentElementLabel));

     _.each(edges, function(childElementLabel) {
         links.push(makeLink(parentElementLabel, childElementLabel));
     });
 });

 // Links must be added after all the elements. This is because when the links
 // are added to the graph, link source/target
 // elements must be in the graph already.
 return elements.concat(links);
}

function makeLink(parentElementLabel, childElementLabel) {

 return new joint.dia.Link({
     source: { id: parentElementLabel },
     target: { id: childElementLabel },
     attrs: { '.marker-target': { d: 'M 4 0 L 0 2 L 4 4 z' } },
     smooth: true
 });
}

function makeElement(label) {

 var maxLineLength = _.max(label.split('\n'), function(l) { return l.length; }).length;

 // Compute width/height of the rectangle based on the number
 // of lines in the label and the letter size. 0.6 * letterSize is
 // an approximation of the monospace font letter width.
 var letterSize = 8;
 var width = 2 * (letterSize * (0.6 * maxLineLength + 1));
 var height = 2 * ((label.split('\n').length + 1) * letterSize);

 return new joint.shapes.basic.Rect({
     id: label,
     size: { width: width, height: height },
     attrs: {
         text: { text: label, 'font-size': letterSize, 'font-family': 'monospace' },
         rect: {
             width: width, height: height,
             rx: 5, ry: 5,
             stroke: '#555'
         }
     }
 });
}