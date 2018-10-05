// media buttons v2
jQuery(document).ready(function(jq) {
	$ = jq;

 maxMedia.prototype = {
	  parent: 'body',
		is_active: false,
		maxmodal: null,
		maxajax: null,
		callback: null,
		useShortCodeOptions: true,
		shortcodeData: null,
		getPage: 1,
		ajaxSuccessHandler: null,


 };

 function maxMedia() {
	 if (typeof window.maxFoundry.maxmodal == 'undefined')
	 {
		 window.maxFoundry.maxmodal = new maxModal();
		 window.maxFoundry.maxmodal.init();
	 }
	 if (typeof window.maxFoundry.maxAjax == 'undefined')
	 {
		 window.maxFoundry.maxAjax = new maxAjax();
		 window.maxFoundry.maxAjax.init();
	 }

	 this.maxmodal = window.maxFoundry.maxmodal;
	 this.maxajax = window.maxFoundry.maxAjax;
 }

 // here the vars go.
 maxMedia.prototype.init = function(options)
 {
	 if (typeof options !== 'undefined')
	 {
	  if (typeof options.callback !== 'undefined')
	 		this.callback = options.callback || null;
//    this.bar = options.requiredArg;
		if (typeof options.useShortCodeOptions !== 'undefined')
			this.useShortCodeOptions = options.useShortCodeOptions;
		if (typeof options.parent !== 'undefined')
		{
			this.parent = options.parent;
		}

	 }
	 this.ajaxSuccessHandler = $.proxy(this.putContent, this);
	 	 $(document).off('click','.pagination span, .pagination-links a', $.proxy(this.doPagination, this));
	 	 $(document).on('click','.pagination span, .pagination-links a', $.proxy(this.doPagination, this));  // should be more specific

	 	 $(document).off('media_button_content_buttons_load',  $.proxy(this.hookButtonAction, this));
	 	 $(document).on('media_button_content_buttons_load', $.proxy(this.hookButtonAction, this));

	 	 $(document).off('media_button_content_shortcode_options', $.proxy(this.hookShortCodeAction, this));
	 	 $(document).on('media_button_content_shortcode_options', $.proxy(this.hookShortCodeAction, this));
 }

 maxMedia.prototype.openModal = function()
 {
		this.maxmodal.newModal('media-popup');

		this.maxmodal.parent = this.parent;
		this.maxmodal.setTitle(mbtrans.windowtitle);

		this.maxmodal.setContent('<span class="loading"></span>');
		this.maxmodal.show();

		this.maxajax.showSpinner( $('.loading') );
		this.loadButtons();

		this.is_active = true;
 }


 maxMedia.prototype.loadButtons = function()
 {

		var data = this.maxajax.ajaxInit();
		data['plugin_action'] = 'getAjaxButtons';
		data['page'] = this.getPage;


		this.maxajax.ajaxPost(data, this.ajaxSuccessHandler);
 }

 maxMedia.prototype.putContent = function(result)
 {
	 var result = JSON.parse(result);
	 this.maxajax.removeSpinner();

	 if (typeof result.output !== 'undefined')
	 {
		 this.maxmodal.setContent(result.output);
	 }
	 if (typeof result.action !== 'undefined')
	 {
		 	$(document).trigger('media_button_content_' + result.action, result);
	 }
 }

 maxMedia.prototype.hookButtonAction = function()
 {
	 	 $(document).off('click', '.button-list');
		 $(document).on('click', '.button-list', $.proxy(function (e) // this selects the button, and enables / disabled the insert button based on that.
		 {
			 var target = $(e.target);

			 if ( typeof $(target).data('button') === 'undefined')
			 {
				 target = $(target).parents('.button-list');
			 }

			 var button = $(target).data('button');
			 $('.button-list').removeClass('selected');
			 $(target).addClass('selected');
			 $('.controls .button-primary').data('button', button);

			 this.maxmodal.currentModal.find('.controls .button-primary').removeClass('disabled');
		 },this));

		 $(document).off('click', ".button-preview a");
		 $(document).on('click', ".button-preview a", function(e) { e.preventDefault(); }); // prevent button clicks

		 this.maxmodal.resetControls();


		 if (this.useShortCodeOptions) // check if second window is required
		 	this.maxmodal.addControl(mbtrans.use, '', $.proxy(this.shortCodeOptions, this) );
		 else
		 	this.maxmodal.addControl(mbtrans.insert, '', $.proxy(this.selectAction, this) );

		 this.maxmodal.setControls();
		 this.maxmodal.currentModal.find('.controls .button-primary').addClass('disabled');

		 this.maxmodal.checkResize();

		// this.hookPagination();

 }

// load the shortcode options screen
 maxMedia.prototype.hookShortCodeAction = function (e, result)
 {

	 this.shortcodeData = result.shortcodeData;
	 var button_id = result.button_id;

	 this.maxmodal.resetControls();
	 this.maxmodal.addControl(mbtrans.insert, '', $.proxy(this.selectAction, this) );
	 this.maxmodal.setControls();

	 $(this.maxmodal.currentModal).find('.controls .button-primary').data('button', button_id);

	 $(this.maxmodal.currentModal).find('.more-options a').off('click');
	 $(this.maxmodal.currentModal).find('.more-options a').on('click', $.proxy(function (e) {
		 	$(this.maxmodal.currentModal).find('.more-field').show();
			$(e.target).parents('.option .more').hide();
			this.maxmodal.checkResize();
	 }, this));

	 this.maxmodal.checkResize();

 }

 // action to select a button, and get button id.
 maxMedia.prototype.selectAction = function(e)
 {
	 e.preventDefault();
	 if ($(e.target).hasClass('disabled'))
	  return; // disabled buttons don't say yes.

		 var button_id = $(e.target).data('button');

		 if (typeof button_id === 'undefined' || parseInt(button_id) <= 0)
			 return; // no button yet.

	 if (typeof this.callback == 'function')
	 {
		 this.callback(button_id, $(e.target) );
	 }
	 else
	 {
		 this.buttonToEditor(button_id);
		 this.close();
	 }
 }

 // ajax query for shortcode options
 maxMedia.prototype.shortCodeOptions = function (e)
 {
	 e.preventDefault();
	 if ($(e.target).hasClass('disabled'))
	 	return; // disabled buttons don't say yes.

	 var button_id = $(e.target).data('button');
	 var data = this.maxajax.ajaxInit();
	 data['plugin_action'] = 'mediaShortcodeOptions';
	 data['button_id'] = button_id;

	 this.maxajax.ajaxPost(data, this.ajaxSuccessHandler);
 }

 /*maxMedia.prototype.hookPagination = function ()
 {
	 $(document).off('click','.pagination span, .pagination-links a');
	 $(document).on('click','.pagination span, .pagination-links a', $.proxy(this.doPagination, this));
 } */

 maxMedia.prototype.doPagination = function (e)
 {
	 e.preventDefault();

	 if ( $(e.target).hasClass('disabled'))
		 return false;

		 var page = $(e.target).data('page');
		 if (page <= 1) page = 1;

		 this.getPage = page;
		 this.loadButtons();
 }

	// create the shortcode to send to the editor.
	maxMedia.prototype.generateShortcode = function (button_id)
	{
		var shortcode = '[maxbutton id="' + button_id + '"';
		if (typeof this.shortcodeData !== 'undefined')
		{
				$(this.shortcodeData).each(function(index, el){
						var input = $('input[name="' + el.name + '"]');
						var inputval = $('input[name="' + el.name + '"]').val();

						if (input.attr('type') == 'checkbox')
						{

							var checked = input.is(':checked');

							if (checked != el.original)
							{
								if (checked)
										shortcode += ' ' +  el.shortcode + '="' + el.checked + '"';
								else
									shortcode += ' ' +  el.shortcode + '="' + el.unchecked + '"';
							}
						}
						else if (inputval != el.original)
						{
							shortcode += ' ' +  el.shortcode + '="' + inputval + '"';
						}
				});
		}


		shortcode += ' ] ';
		return shortcode;
	}

	maxMedia.prototype.buttonToEditor = function (button_id)
	{
		shortcode = this.generateShortcode(button_id);
		window.send_to_editor(shortcode, button_id);
	}

	maxMedia.prototype.close = function()
	{
		this.maxmodal.close();
	}

  if (typeof window.maxFoundry === 'undefined')
  	window.maxFoundry = {};

  window.maxFoundry.maxMedia = maxMedia;

}); // jquery
