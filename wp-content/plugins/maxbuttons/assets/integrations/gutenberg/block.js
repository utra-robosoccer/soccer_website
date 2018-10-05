
( function( blocks, i18n, element ) {
	var el = element.createElement;
	var __ = i18n.__;
	var RichText = blocks.RichText;
	var PlainText = blocks.PlainText;
	//var Shortcode = blocks.library.shortcode;
	var children = blocks.source.children;


	blocks.registerBlockType( 'maxbuttons/gutenberg', {
		title: __( 'MaxButtons' ),
		icon: 'universal-access-alt',
		category: 'layout',
		attributes: {
			button_id: {
				type: 'number',
				source: 'children',
				selector: 'p',
			},
      url: {
        type: 'text',
        select: 'p',
      },
      text: {
        type: 'text',
        select: 'p',
      },
		},

		edit: function( props ) {
			var button_id = props.attributes.button_id;
      var url = props.attributes.url;
      var text = props.attributes.text;
			var focus = props.focus;
			function onChangeContent( newbuttonid ) {
				props.setAttributes( { button_id: newbuttonid } );
			}

			return el(
				PlainText,
				{
					tagName: 'p',
					className: props.className,
					onChange: onChangeContent,
					value: content,
					focus: focus,
					onFocus: props.setFocus
				}
			);
		},

		save: function( props ) {
			return el( 'p', {}, props.attributes.content );
		},
	} );
} )(
	window.wp.blocks,
	window.wp.i18n,
	window.wp.element
);
