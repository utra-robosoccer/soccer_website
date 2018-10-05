( function( $ ) {


	var sui = window.Shortcode_UI;
	//var construct = sui.utils.shortcodeViewConstructor;

	sui.views.editAttributeFieldMaxButton = sui.views.editAttributeField.extend( {

	updateButton: function(id)
	{

		this.setValue(id);
		var preview = this.$el.find('.button_preview');

		var query =  [{
				counter: 1,
				post_id: $( '#post_ID' ).val(),
				shortcode: this.shortcode.formatShortcode(),
			}];

		// provisional
		wp.ajax.post( 'bulk_do_shortcode', {
			queries: query,
			nonce: shortcodeUIData.nonces.preview,
		}).done( function( response ) {
			var button = response[1].response;
			preview.html(button);
		})

	},

	render: function ()
	{

		var self = this;
		//var maxMedia = window.maxFoundry.maxMedia;
		this.cake_mm = new window.maxFoundry.maxMedia();

		this.cake_mm.init({callback: $.proxy(this.callback, this), useShortCodeOptions: false});
		//cake_mm.openModal();

		var data = jQuery.extend( {
			id: 'shortcode-ui-' + this.model.get( 'attr' ) + '-' + this.model.cid,
		}, this.model.toJSON() );

		//maxMedia.setCallback( $.proxy(this.updateButton, this) );

		this.$el.html( this.template( data ) );
		//this.triggerCallbacks();

		$(document).on('click', '.maxbutton_media_button', $.proxy(function(e)
		{
			 e.preventDefault();
			 this.cake_mm.openModal();
			 $('.media-modal, .media-modal-backdrop').hide();
		},this));


		$(document).on('modal_close', function ()
		{
				$('.media-modal, .media-modal-backdrop').show();
		});

		this.updateButton( this.model.get( 'value' ) );

		return this;
	},

	callback: function(id)
	{
			this.updateButton(id);
			this.cake_mm.close();
			$('.media-modal, .media-modal-backdrop').show();
	}



	}); // extend

} )( jQuery );
