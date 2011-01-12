document.addEvent('domready', function() {
	
/*
 Handles the status message passed via GET-back
*/
if( window.location.search.length 
	&& window.location.search.indexOf('message=') != -1 ) {

	var data = new URI(window.location.href).get('data');
	var message = data.message.unencode(), 
		   type = data.type.unencode();
	
	message = message.replace(/[^a-zA-Z0-9\.\!\?\:\-\\\/\*]/g, ' ');
	
	switch(type) {
		case 'success':
		case 'notice':
		case 'error':
			break;
		default:
			type = 'notice';
			break;
	}

	var el = new Element('span',{
		'class': type,
		'text': message
	});
	
	if( $('login') ) {
		el.id = 'loginerror';
		el.inject($$('form h1').pop(), 'after');
	} else {
		setTimeout(function() {
			gryphon.statusMessage.display(message, type);
		}, 1000);
	}
}

$$('.text-replace').each(function(el) {
	if( el.get('type') == 'password' ) {
		el.set('type', 'text');
		el.store('isPass', true);
	}
	
	el.addEvents({
		'focus': function(e) {
			if( this.get('default') == this.get('value') ) {
				this.set('value', '');
			}
			if( this.retrieve('isPass') ) {
				this.set('type', 'password');
			}
		},
		'blur': function(e) {
			if( !this.get('value') ) {
				this.set('value', this.get('default'));

				if( this.retrieve('isPass') ) {
					this.set('type', 'text');
				}

			}
		}
	});
});



}); // end domready