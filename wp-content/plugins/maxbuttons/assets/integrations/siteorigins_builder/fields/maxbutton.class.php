<?php
namespace MaxButtons;

/**
 * Use of this field requires at least WordPress 3.5.
 *
 * Class SiteOrigin_Widget_Field_Media
 */
class MaxButton_Widget_Field_MaxButton extends \SiteOrigin_Widget_Field_Base {
	/**
	 * A label for the title of the media selector dialog.
	 *
	 * @access protected
	 * @var string
	 */
	protected $choose;
	/**
	 * A label for the confirmation button of the media selector dialog.
	 *
	 * @access protected
	 * @var string
	 */
	protected $update;
	/**
	 * Sets the media library which to browse and from which media can be selected. Allowed values are 'image',
	 * 'audio', 'video', and 'file'. The default is 'file'.
	 *
	 * @access protected
	 * @var string
	 */
//	protected $library;
	/**
	 * Whether or not to display a URL input field which allows for specification of a fallback URL to be used in case
	 * the selected media resource isn't available.
	 *
	 * @access protected
	 * @var bool
	 */
	protected $fallback;
	/**
	 * Reference to the containing widget required for creating the fallback subfield.
	 *
	 * @access private
	 * @var SiteOrigin_Widget
	 */
	protected $for_widget;
	/**
	 * An array of field names of parent repeaters.
	 *
	 * @var array
	 */
	private $parent_repeater;

	protected static $field_count = 0;

	  public function __construct( $base_name, $element_id, $element_name, $field_options, $for_widget, $parent_container = array()  ) {
		parent::__construct( $base_name, $element_id, $element_name, $field_options );

		$this->for_widget = $for_widget;
		$this->parent_repeater = $parent_container;

		static::$field_count++;

	}

	protected function get_default_options() {
		return array(
			'choose' => __( 'Choose Maxbutton', 'maxbuttons' ),
			'update' => __( 'Set Media', 'maxbuttons' ),
		//	'library' => 'image'
		);
	}

	/** It looks like all of those fields are rendered every thing for every block, but they don't change. So there is a need to keep them apart */

	protected function render_field( $value, $instance ) {
		 $nonce = wp_create_nonce('maxajax');

		 if ($value == '' || ! is_numeric($value))
		 	$value = 0;

		?>
		<script language="javascript">
			var sop_mm = new window.maxFoundry.maxMedia();
			var mbbutton_number = '<?php echo $this->element_id ?>';

			var current_button_id = <?php echo $value ?>;

			function insertSOPageBuilder(id)
			{
				var button = jQuery('.media-popup .maxbutton-' + id).parents('.shortcode-container').children().clone();
				var button_id = id;

				//renderShortcode(shortcode);
				jQuery('.mbselected.' + mbbutton_number).find(".the_button").html(button);
				jQuery('.mbselected.' + mbbutton_number).find(".sop_button_id").val(button_id);

				sop_mm.close();
				return false;
			}

			/*function renderShortcode(shortcode)
			{
				var maxajax = window.maxFoundry.maxAjax;
				var data = maxajax.ajaxInit();
				data['plugin_action'] = 'shortcode';
				data['shortcode'] = shortcode;

				maxajax.ajaxPost(data, function(result) {
						if (result)
						{
							var result = JSON.parse(result);
							jQuery('.mbselected.' + mbbutton_number).find(".the_button").html(result.shortcode);
						}
				});
			} */

			function openButtonModal()
			{
				sop_mm.init({callback: insertSOPageBuilder, useShortCodeOptions: false});
				sop_mm.openModal();
			}


			if ( isNaN(current_button_id) || current_button_id <= 0 )
			{
				//openButtonModal();
			}
		</script>
		 <button class="button-primary maxbutton_media_button" id="mbbutton-<?php echo static::$field_count ?>" data-nonce="<?php echo $nonce ?>"  data-callback='insertSOPageBuilder' onClick='openButtonModal()'><?php _e("Select a Button"); ?></button>

		<p><h3><?php _e('Selected Button', 'maxbuttons') ?></h3></p>
 		<div class='mbselected <?php echo $this->element_id ?>'>
			<span class='the_button'>
			<?php
 			$button= MB()->getClass('button');

 			if (intval($value) > 0)
 			{
 				$button->set($value);
 				$button->display(array('load_css' => 'inline') );
 			}
 		?> </span>

			<input type="hidden" class='sop_button_id' value="<?php echo esc_attr( is_array( $value ) ? '-1' : $value ) ?>" name="<?php echo esc_attr( $this->element_name ) ?>" class="siteorigin-widget-input" />


	</div>
		<?php
	}


	// $instance was added somehow by siteorigin making the plugin crash - added null for backward compat.
	protected function sanitize_field_input( $value, $instance = null ) {
		// MB value should be integer - button_id
		return intval($value);
	}

	public function sanitize_instance( $instance ) {
		$fallback_name = $this->get_fallback_field_name( $this->base_name );
		if( !empty( $this->fallback ) && !empty( $instance[ $fallback_name ] ) ) {
			$instance[ $fallback_name ] = esc_url_raw( $instance[ $fallback_name ] );
		}
		return $instance;
	}

	public function get_fallback_field_name( $base_name ) {
		$v_name = $base_name;
		if( strpos($v_name, '][') !== false ) {
			// Remove this splitter
			$v_name = substr( $v_name, strpos($v_name, '][') + 2 );
		}
		return $v_name . '_fallback';
	}
}
