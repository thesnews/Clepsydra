window.addEvent('domready', function() { 

	// Initial Graph setup
	
/*	var graph = new Bluff.Line('thegraph', '940x300');
	
	graph.set_theme({
		colors: ['#fff'],
		marker_color: '#515051',
		font_color: '#515051',
		background_colors: '#1f1f1f'
	});
	
	graph.data_from_table('this_pay_period');
  	
	graph.set_margins = 0;
	graph.tooltips = true;
	graph.hide_legend = true;
	graph.hide_title = true;
	graph.marker_font_size = 8;
	graph.dot_radius = 3;
	graph.line_width = 2;
	graph.top_margin = 0;
	
	graph.draw();
	
	// Event Listener for Graph Change
	
	$$('#graph li a').addEvent('click', function(){
		$$('#graph li').removeClass('active');
		this.getParent('li').addClass('active');
		// TODO: Clear graph, get data from graph referenced in anchor's rel attr, redraw graph
	});
*/	

	if( $('container_currentTime') ) {
		var el = $('container_currentTime');

		var uri = el.get('data-url').toURI();
		uri.set('file', uri.get('file')+'.json');
		
		new Request.JSON({
			url: uri,
			method: "GET",
			onComplete: function(data) {
				var time = data[0].currently * 1000;
				var date = new Date(time);
				
				el.set('text', date.format('%k:%M:%S'));
				
				setInterval(function() {
					date.increment('second');
					el.set('text', date.format('%k:%M:%S'));
				}, 1000);
			}
		}).send();
	}


});