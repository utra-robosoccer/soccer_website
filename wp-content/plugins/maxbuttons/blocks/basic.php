<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

$blockClass["basic"] = "basicBlock";
$blockOrder[0][] = "basic";


class basicBlock extends maxBlock
{
	protected $blockname = "basic";
	protected $fields = array("name" => array("default" => ''),
							  "status" => array("default" => "publish"),
							  "description" => array("default" => ''),
							  "url" => array("default" => ''),
							  'link_title' => array('default' => ''),
							//  "text" => array("default" => ''),
							  "new_window" => array("default" => 0),
							  "nofollow" => array("default" => 0)
							 );
	protected $protocols = array("http","https",'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'sms', 'callto',  'fax', 'xmpp', "javascript", 'file', 'ms-windows-store', 'steam', 'webcal'); 	 // allowed url protocols for esc_url functions


	function __construct()
	{
		parent::__construct();
		$extra_protocols = get_option('maxbuttons_protocol');
		$extra_protocols = array_map('trim', array_filter(explode(',', $extra_protocols)));

		if (is_array($extra_protocols) && count($extra_protocols) > 0)
		{
			$this->protocols = array_merge($this->protocols, $extra_protocols);
		}
	}


	public function parse_css($css,  $mode = 'normal')
	{
		// emtpy string init is not like by PHP 7.1
		if (! is_array($css))
			$css = array();

		$data = $this->data[$this->blockname];

		$css["maxbutton"]["normal"]["position"] = "relative";
		$css["maxbutton"]["normal"]["text-decoration"] = "none";
//		$css["maxbutton"]["normal"]["white-space"] = "nowrap";  // hinders correct rendering of oneline-multilines
		$css["maxbutton"]["normal"]["display"] = "inline-block";
		$css["maxbutton"]["normal"]["vertical-align"] = 'middle';

		/*if (isset($data["url"]) && $data["url"] == '') // don't show clickable anchor if there is no URL.
		{
			$css["maxbutton"]["normal"]["cursor"] = 'default';
		//	$css[":hover"]["cursor"] = 'default';
		} */

		return $css;

	}

	public function save_fields($data, $post)
	{
		// Possible solution:
	//	$post["url"] = isset($post["url"]) ? urldecode(urldecode($post["url"])) : '';

		$description = false;

  		if (isset($post["description"]) && $post["description"] != '')
  		{
  			$description = str_replace("\n", '-nwline-', $post["description"]);
			$description = sanitize_text_field($description);
			$description = str_replace('-nwline-', "\n", $description);

  		}

		$data = parent::save_fields($data, $post);

		// bypass sanitize for description - causing the end of line-breaks
		if ($description)
			$data["basic"]["description"] = $description;

		// bypassing sanitize text field - causes problems with URLs and spaces
		$url = isset($post["url"]) ? trim($post["url"]) : '';

		$parsed_url = parse_url($url);
		$rawEncode = array("query","fragment");
		foreach($rawEncode as $item)
		{
			if (isset($parsed_url[$item]))
			{
				$parsed_url[$item] = rawurlencode($parsed_url[$item]);
			}
		}

		$url = $this->unParseURL($parsed_url);

		$url = str_replace(" ", "%20", trim($url) );

 		if (! $this->checkRelative($parsed_url))
			$url = esc_url_raw($url, $this->protocols);  // str replace - known WP issue with spaces

		$data[$this->blockname]["url"] = $url;

		if (isset($post["name"]))
			$data["name"] = sanitize_text_field($post["name"]);
		if (isset($post["status"]))
			$data["status"] = sanitize_text_field($post["status"]); // for conversion old - new.
 		return $data;
	}

	protected function unparseURL($parsed_url)
	{
		  // Don't add // to these schemes
		  $noslash_schemes = array('javascript', 'mailto', 'tel', 'sms');
		  if (isset($parsed_url['scheme']) && in_array($parsed_url['scheme'], $noslash_schemes) )
			  $scheme = $parsed_url["scheme"] . ":";
		  else
			  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';


		  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		  $pass     = ($user || $pass) ? "$pass@" : '';
		  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
		  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
		  return "$scheme$user$pass$host$port$path$query$fragment";
	}

	/* Check for a relative URL that gets killed by esc_url ( if there is no / first ) */
	protected function checkRelative($parsed_url)
	{
		if (! isset($parsed_url['host']) && ! isset($parsed_url['scheme']) )
		{
			if (isset($parsed_url['path']) && $parsed_url['path'] !== '' && substr($parsed_url['path'], 0,1) !== '/')
			{
				return true;
			}
		}
		return false;
	}

	public function parse_button($domObj, $mode = 'normal')
	{

		$data = $this->data[$this->blockname];
		$button_id = $this->data["id"];
		$rels = array();

		$anchor = $domObj->find("a",0);

		if (isset($data["nofollow"]) && $data["nofollow"] == 1)
		{
			$rels[] = 'nofollow';
			$rels[] = 'noopener';
		}
		//	$anchor->rel = "nofollow";
		//	$buttonAttrs[] = "rel=nofollow";

		if (isset($data["new_window"]) && $data["new_window"] == 1)
		{
			$anchor->target = "_blank";
			if (! in_array('noopener', $rels))
				$rels[] = 'noopener';
		}
		if (isset($data['link_title']) && strlen($data['link_title']) > 0)
			$anchor->title = $data['link_title'];

		$rels = apply_filters('mb/button/rel', $rels);

		if (count($rels) > 0)
		{
			$anchor->rel = implode(' ', $rels);
		}

		if (isset($data["url"]) && $data["url"] != '')
		{
			$url = $data["url"];
			$parsed_url = parse_url($url);

			if (! $this->checkRelative($parsed_url))
				$url = esc_url($url, $this->protocols);

		 	$url = rawurldecode($url);  // removes the + from a URL part.
			$url = apply_filters('mb-url', $url, $data['url']);  // passes processed url / raw url.
			$url = apply_filters('mb-url-' . $button_id, $url, $data['url']);


			$anchor->href = $url;
			//do_shortcode( esc_url($url, $this->protocols) );

		}
		else  // fixing an iOS problem which renders anchors without URL wrongly.
		{
			$anchor->href = 'javascript:void(0);';
		}


		return $domObj;

	}

	public function map_fields($map)
	{

		$map["url"]["attr"] = "href";
		$map["link_title"]["attr"] = "title";

//		$map["text"]["func"] = "updateAnchorText";

		return $map;
	}

	public function admin_fields()
	{

		$icon_url = MB()->get_plugin_url() . 'images/icons/';
		$admin = MB()->getClass('admin');
?>

		<div class="mb_tab option-container mb_tab">
				<div class="title"><?php _e('Basics', 'maxbuttons') ?></div>
				<div class="inside basic">
					<?php
					$color_copy_self = __("Replace color from other field", "maxbuttons");
					$color_copy_move  = __("Copy Color to other field", "maxbuttons");

 					// Name
					$field_name = new maxField() ;
					$field_name->label = __('Button Name', 'maxbuttons');
				//	$field_name->note = __('Something that you can quickly identify the button with.', 'maxbuttons');
					$field_name->value = maxBlocks::getValue('name');
					$field_name->id = 'name';
					$field_name->name = $field_name->id;
					$field_name->placeholder = __("Button Name","maxbuttons");
				//	$field_name->output('start','end');

					$admin->addField($field_name, 'start', 'end');

					// URL
					$field_url = new maxField();
					$field_url->label = __('URL', 'maxbuttons');
				//	$field_url->note = __('The link when the button is clicked.', 'maxbuttons');
					$field_url->value = rawurldecode(maxBlocks::getValue('url') );
					$field_url->id = 'url';
					$field_url->placeholder = __("http://","maxbuttons");
					$field_url->name = $field_url->id;
					$field_url->help = "<p>Enter any URL you wish to link to. </p>
					<p>Examples: <br><ul><li> https://example.com </li>
								 <li> /local-page/ </li>
								 <li> javascript:window.history.back(); </li></ul></p>
					<p class='shortcode'> Shortcode attribute : url </p> ";

					//$field_url->output('start','end');
					$admin->addField($field_url,'start');

					$url_button = new maxField('button');
					$url_button->id = 'url_button';
					$url_button->name = $url_button->id;
					$url_button->button_label = __('Select Site Content', 'maxbuttons');
					$admin->addField($url_button, '', 'end');

					$url_nonce = new maxField('hidden');
					$url_nonce->id = '_ajax_linking_nonce';
					$url_nonce->name = $url_nonce->id;
					$url_nonce->value = wp_create_nonce('internal-linking');

					$admin->addField($url_nonce);

					// Spacer
					$fspacer = new maxField('spacer');
					$fspacer->name = 'url_options';
					$fspacer->label = '&nbsp;';
				//	$fspacer->output('start');

				  $admin->addField($fspacer, 'start');

					// New Window
					$fwindow = new maxField('checkbox');
					$fwindow->label = __('Open in New Window', 'maxbuttons');
					$fwindow->name = 'new_window';
					$fwindow->id = $fwindow->name;
					$fwindow->value = 1;
					//$fwindow->inputclass = 'check_button';
					$fwindow->checked = checked( maxBlocks::getValue('new_window'), 1, false);

					//$fwindow->output('','');
					$admin->addField($fwindow);

					// NoRel
					$ffollow = new maxField('checkbox');
					$ffollow->label = __('Use rel="nofollow"', 'maxbuttons');
					$ffollow->value = 1;
					$ffollow->name = 'nofollow';
					$ffollow->id = $ffollow->name;
					$ffollow->checked = checked( maxBlocks::getValue('nofollow'), 1, false);

					//$ffollow->output('','end');
					$admin->addField($ffollow, '','end');
					// TITLE

					$field_title = new maxField();
					$field_title->label = __('Button Tooltip', 'maxbuttons');
					$field_title->name = 'link_title';  // title is too generic
					$field_title->id = $field_title->name;
					$field_title->value =  maxBlocks::getValue('link_title');
					$field_title->help = __('<p>This text will appear when hovering over the button</p>
					<p class="shortcode">Shortcode attribute : linktitle</p>', 'maxbuttons');

					//$field_title->output('start','end');
					$admin->addField($field_title, 'start', 'end');

					// TEXT
					$field_text = new maxField();
					$field_text->label = __('Text','maxbuttons');
					$field_text->name = 'text';
					$field_text->id = 'text';
					$field_text->value = maxBlocks::getValue('text') ;

				//	$field_text->output('start','end');
					$admin->addField($field_text, 'start', 'end');

 					// FONTS
					$fonts = MB()->getClass('admin')->loadFonts();

 					$field_font = new maxField('option_select');
 					$field_font->label = __('Font','maxbuttons');
 					$field_font->name = 'font';
 					$field_font->id = $field_font->name;
 					$field_font->selected = maxBlocks::getValue('font');
 					$field_font->options = $fonts;

					$admin->addField($field_font,'start');
 					?>

				<?php
					// FONT SIZE
					//global $maxbuttons_font_sizes;
					$sizes = apply_filters('mb/editor/fontsizes', maxUtils::generate_font_sizes(10,50) );


					$field_size = new maxField('number');
				//	$field_size->label = '';
					$field_size->name = 'font_size';
					$field_size->id= $field_size->name;
					$field_size->inputclass = 'tiny';
					$field_size->min = 8;
					$field_size->value = maxUtils::strip_px(maxBlocks::getValue('font_size'));
					//$field_size->content = maxUtils::selectify($field_size->name, $sizes, $field_size->value, '', 'small');

					//$field_size->output();
					$admin->addField($field_size);

					// Font style checkboxes
			 		$fweight = new maxField('checkbox');
			 		$fweight->icon = 'dashicons-editor-bold';
			 		$fweight->title = __("Bold",'maxbuttons');
			 		$fweight->id = 'check_fweight';
			 		$fweight->name = 'font_weight';
			 		$fweight->value = 'bold';
			 		$fweight->inputclass = 'check_button icon';
			 		$fweight->checked = checked( maxBlocks::getValue('font_weight'), 'bold', false);

			 	//	$fweight->output('group_start');
					$admin->addField($fweight, 'group_start');

			 		$fstyle = new maxField('checkbox');
			 		$fstyle->icon = 'dashicons-editor-italic';
			 		$fstyle->title = __("Italic",'maxbuttons');
			 		$fstyle->id = 'check_fstyle';
			 		$fstyle->name = 'font_style';
			 		$fstyle->value = 'italic';
			 		$fstyle->inputclass = 'check_button icon';
			 		$fstyle->checked = checked( maxBlocks::getValue('font_style'), 'italic', false);

			 		//$fstyle->output('','group_end');
					$admin->addField($fstyle, '', 'group_end');

			 		$falign_left = new maxField('radio');
			 		$falign_left->icon = 'dashicons-editor-alignleft';
			 		$falign_left->title = __('Align left','maxbuttons');
			 		$falign_left->id = 'radio_talign_left';
			 		$falign_left->name = 'text_align';
			 		$falign_left->value = 'left';
			 		$falign_left->inputclass = 'check_button icon';
			 		$falign_left->checked = checked ( maxblocks::getValue('text_align'), 'left', false);

			 		//$falign_left->output('group_start');
					$admin->addField($falign_left, 'group_start');

			 		$falign_center = new maxField('radio');
			 		$falign_center->icon = 'dashicons-editor-aligncenter';
			 		$falign_center->title = __('Align center','maxbuttons');
			 		$falign_center->id = 'radio_talign_center';
			 		$falign_center->name = 'text_align';
			 		$falign_center->value = 'center';
			 		$falign_center->inputclass = 'check_button icon';
			 		$falign_center->checked = checked( maxblocks::getValue('text_align'), 'center', false);

			 		//$falign_center->output();
					$admin->addField($falign_center);

			 		$falign_right = new maxField('radio');
			 		$falign_right->icon = 'dashicons-editor-alignright';
			 		$falign_right->title = __('Align right','maxbuttons');
			 		$falign_right->id = 'radio_talign_right';
			 		$falign_right->name = 'text_align';
			 		$falign_right->value = 'right';
			 		$falign_right->inputclass = 'check_button icon';
			 		$falign_right->checked = checked( maxblocks::getValue('text_align'), 'right', false);

			 		//$falign_right->output('', array('group_end','end') );
					$admin->addField($falign_right, '',  array('group_end','end') );

			 		// Padding - trouble
			 		$ptop = new maxField('number');
			 		$ptop->label = __('Padding', 'maxbuttons');
			 		$ptop->id = 'padding_top';
			 		$ptop->name = $ptop->id;
 					$ptop->min = 0;
			 		$ptop->inputclass = 'tiny';
			 		$ptop->before_input = '<img src="' . $icon_url . 'p_top.png" title="' . __("Padding Top","maxbuttons") . '" >';
			 		$ptop->value = maxUtils::strip_px(maxBlocks::getValue('padding_top'));

			 		//$ptop->output('start');
					$admin->addField($ptop,'start');

			 		$pright = new maxField('number');
			 		$pright->id = 'padding_right';
			 		$pright->name = $pright->id;
 					$pright->min = 0;
			 		$pright->inputclass = 'tiny';
			 		$pright->before_input = '<img src="' . $icon_url . 'p_right.png" class="icon padding" title="' . __("Padding Right","maxbuttons") . '" >';
			 		$pright->value = maxUtils::strip_px(maxBlocks::getValue('padding_right'));

			 		//$pright->output();
					$admin->addField($pright);

			 		$pbottom = new maxField('number');
			 		$pbottom->id = 'padding_bottom';
			 		$pbottom->name = $pbottom->id;
 					$pbottom->min = 0;
			 		$pbottom->inputclass = 'tiny';
			 		$pbottom->before_input = '<img src="' . $icon_url . 'p_bottom.png" class="icon padding" title="' . __("Padding Bottom","maxbuttons") . '" >';
			 		$pbottom->value = maxUtils::strip_px(maxBlocks::getValue('padding_bottom'));

			 		//$pbottom->output();
					$admin->addField($pbottom);

				 	$pleft = new maxField('number');
			 		$pleft->id = 'padding_left';
			 		$pleft->name = $pleft->id;
 					$pleft->min = 0;
			 		$pleft->inputclass = 'tiny';
			 		$pleft->before_input = '<img src="' . $icon_url . 'p_left.png" class="icon padding" title="' . __("Padding Left","maxbuttons") . '" >';
			 		$pleft->value = maxUtils::strip_px(maxBlocks::getValue('padding_left'));

			 		//$pleft->output('','end');
					$admin->addField($pleft,'', 'end');


 					// Text Color
 					$fcolor = new maxField('color');
 					$fcolor->id = 'text_color';
 					$fcolor->name = $fcolor->id;
 					$fcolor->value = maxBlocks::getColorValue('text_color');
 					$fcolor->label = __('Text Color','maxbuttons');
 					$fcolor->copycolor = true;
 					$fcolor->bindto = 'text_color_hover';
 					$fcolor->copypos = 'right';
					$fcolor->right_title = $color_copy_move;
					$fcolor->left_title = $color_copy_self;

				//	$fcolor->output('start');
					$admin->addField($fcolor, 'start');

 					// Text Color Hover
 					$fcolor_hover = new maxField('color');
 					$fcolor_hover->id = 'text_color_hover';
 					$fcolor_hover->name = $fcolor_hover->id;
 					$fcolor_hover->value = maxBlocks::getColorValue('text_color_hover');
 					$fcolor_hover->label = __('Text Color Hover','maxbuttons');
 					$fcolor_hover->copycolor = true;
 					$fcolor_hover->bindto = $fcolor->id;
 					$fcolor_hover->copypos = 'left';
					$fcolor_hover->right_title = $color_copy_self;
					$fcolor_hover->left_title = $color_copy_move;
 					//$fcolor_hover->output('','end');

					$admin->addField($fcolor_hover, '','end');

 					// Dimension : width
 					$field_width = new maxField('number');
 					$field_width->label = __('Button Width','maxbuttons');
 					$field_width->name = 'button_width';
 					$field_width->id = $field_width->name;
 					$field_width->inputclass = 'small';
 					$field_width->min = 0;
 					$field_width->value=  maxUtils::strip_px(maxBlocks::getValue('button_width'));  // strippx?
 					//$field_width->output('start');

					$admin->addField($field_width, 'start');

 					// Dimension : height
 					$field_height = new maxField('number');
 					$field_height->label = __('Button Height','maxbuttons');
 					$field_height->name = 'button_height';
 					$field_height->id = $field_height->name;
 					$field_height->inputclass = 'small';
 					$field_height->min = 0;
 					$field_height->value=  maxUtils::strip_px(maxBlocks::getValue('button_height'));  // strippx?
 					//$field_height->output('','end');

					$admin->addField($field_height, '', 'end');

 					// Description
 					$description_hide = get_option('maxbuttons_hidedescription');
 					if ($description_hide == 1)
 						$field_desc = new maxField('hidden');
					else
 	 					$field_desc = new maxField('textarea');

					$field_desc->label = __('Description', 'maxbuttons');
					$field_desc->name = 'description';
					$field_desc->id = $field_desc->name;
					$field_desc->esc_function = 'esc_textarea';
					$field_desc->value = maxBlocks::getValue('description') ;
					$field_desc->placeholder = __('Brief explanation about how and where the button is used.','maxbuttons');
					//$field_desc->output('start','end');

					$admin->addField($field_desc, 'start', 'end');

					$admin->display_fields();
					?>


				</div>
			</div>
<?php }  // admin_display

 } // class

 ?>
