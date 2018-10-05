
/** New AJAX Call methods
/* Get the standard AJAX vars for this plugin */

function maxAjax() {
		this.spinnerFunction = this.showSpinner;
		this.successHandler = this.defaultSuccesHandler;
		this.errorHandler = this.defaultErrorHandler;
}

maxAjax.prototype.init = function()
{
	// default actions that trigger ajax action.
	$(document).on('submit', '.mb-ajax-form', function (e) { e.preventDefault(); return false; }); // don't submit ajax forms ( prevent enter submit )
	$(document).on('click', '.mb-ajax-form .mb-ajax-submit', $.proxy(this.ajaxForm, this ));
	$(document).on('click', '.mb-ajax-action', $.proxy(this.ajaxCall, this ));
	$(document).on('change', '.mb-ajax-action-change', $.proxy(this.ajaxCall, this));
	$(document).trigger('maxajax_init'); // for hanging in other actions.
}

maxAjax.prototype.ajaxInit = function()
{
	data = {
		action: maxajax.ajax_action,
		nonce:  maxajax.nonce,
	}

	return data;
}

maxAjax.prototype.setFunction = function(type, new_function)
{
		if (type == 'spinner')
		{
			this.spinnerFunction = new_function;
		}
}

maxAjax.prototype.ajaxForm = function (e)
{
	e.preventDefault();

	var target = $(e.target);
	var form = $(target).parents('form');
	var action = $(target).data('action');

	var data = this.ajaxInit();
	data['form'] = form.serialize();
	data['plugin_action'] = action;
//	data['action'] = 'mb_button_action';

	$(document).trigger('maxajax_formpost_' + action, [data,target]);

	this.spinnerFunction(target);

	this.ajaxPost(data);
}

/* Ajax call functionality */
maxAjax.prototype.ajaxCall = function (e)
{
	e.preventDefault();
	var target = $(e.target);

	if (! $(target).hasClass('.mb-ajax-action') && ! $(target).hasClass('.mb-ajax-action-change') )
	{
			if ($(target).parents('.mb-ajax-action'). length > 0)
				target  = $(e.target).parents('.mb-ajax-action');

			if ($(target).parents('.mb-ajax-action-change'). length > 0)
				target  = $(e.target).parents('.mb-ajax-action');
	}

	var param = false;
	var plugin_action = $(target).data('action');
	var check_param = $(target).data('param');
	var param_input = $(target).data('param-input');

	if (typeof check_param !== 'undefined')
		param = check_param;
	if (typeof param_input !== 'undefined')
		param = $(param_input).val();

	data = this.ajaxInit();

	data['plugin_action'] = plugin_action;
	data['param'] = param;
	data['post'] = $('form').serialize(); // send it all

	var spinnerFunc = this.spinnerFunction;

	this.spinnerFunction(target);

	this.ajaxPost(data);
}

maxAjax.prototype.showSpinner = function(target)
{
		// spinner styling in elements
	var spinner = '<div class="maxajax-load-spinner"></div>';
	//$('.maxajax-load-spinner').remove();
	$(target).after(spinner);
	//return spinner;
}

maxAjax.prototype.removeSpinner = function()
{
	$('.maxajax-load-spinner').remove();

}

maxAjax.prototype.ajaxPost = function(data, successHandler, errorHandler)
{
	var self = this;

	if (typeof successHandler == 'undefined')
	{
		var action = data['plugin_action'];
		var successHandler = function (r,s,o,) { self.defaultSuccessHandler(r,s,o,action) } ;

	}

	if (typeof errorHandler == 'undefined')
	{
		var action = data['plugin_action'];
		var errorHandler = function (r,s,o,) { self.defaultErrorHandler(r,s,o,action) } ;
	}

	$.ajax({
		type: "POST",
		url: maxajax.ajax_url,
		data: data,
		success: successHandler,
		error: errorHandler,
		});
}

maxAjax.prototype.defaultSuccessHandler = function (result, status, object, action)
{
  	this.removeSpinner();
		$(document).trigger('maxajax_success_' + action, [result, status, object]);

}

maxAjax.prototype.defaultErrorHandler = function(jq,status,error, action)
{
		  this.removeSpinner();
			$(document).trigger('maxajax_error_' + action, jq, status, error);
			console.log(jq);
			console.log(status);
			console.log(error);
}
