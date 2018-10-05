<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["dimension"] = "dimensionBlock";
$blockOrder[20][] = "dimension";

class dimensionBlock extends maxBlock
{
	protected $blockname = "dimension";
	protected $fields = array("button_width" => array("default" => '160px',
										"css" => "width",
										),
							  "button_height" => array("default" => '50px',
							  			"css" => "height")
							  );

	public function parse_css($css, $mode = 'normal')
	{
		$css = parent::parse_css($css, $mode);

		if (! isset($css["maxbutton"]["normal"]["width"])) return $css;

		// do not allow zero's.
		if (isset($css["maxbutton"]["normal"]["width"]) && ($css["maxbutton"]["normal"]["width"] == 0 || $css["maxbutton"]["normal"]["width"] == '') )
			unset($css["maxbutton"]["normal"]["width"]);

		if (isset($css["maxbutton"]["normal"]["height"]) && ($css["maxbutton"]["normal"]["height"] == 0 ||  $css["maxbutton"]["normal"]["height"] == '') )
			unset($css["maxbutton"]["normal"]["height"]);


	/*	if ($css["normal"]["width"] > maxbuttons_strip_px(0) || $css["normal"]["height"] > maxbuttons_strip_px(0))
		{
			$css["normal"]["display"] = "inline-block";
		}
	*/

		return $css;
	}

	public function map_fields($map)
	{
		$map = parent::map_fields($map);

		$map["button_width"]["func"] = "updateDimension";
		$map["button_height"]["func"] = "updateDimension";

		return $map;

	}


	public function admin_fields()
	{
		return;

	} // admin_fields



} // class

?>
