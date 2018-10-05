<?php
namespace MaxButtons;

defined( 'ABSPATH' ) || exit;

class MBGutenberg
{

  public static function init()
  {
    add_action( 'enqueue_block_editor_assets', array(maxUtils::namespaceit('MBGutenberg'), 'maxbuttons_gutenberg_editor') );
    add_action( 'enqueue_block_assets', array(maxUtils::namespaceit('MBGutenberg'), 'maxbuttons_gutenberg_block') );
  }


public static function maxbuttons_gutenberg_editor() {
  $url = MB()->get_plugin_url() . 'assets/integrations/gutenberg/';
  $version = MAXBUTTONS_VERSION_NUM;

//var_dump($url . 'block.js');exit();
  wp_enqueue_script(
		'mbgutenberg',
    $url . 'block.js',
		array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
		$version
	);
	/*wp_enqueue_style(
		'gutenberg-examples-03-editor',
		$url . 'editor.css',
		array( 'wp-edit-blocks' ),
		filemtime( $url . 'editor.css' )
	); */
}

public static function maxbuttons_gutenberg_block() {
  $url = MB()->get_plugin_url() . 'assets/integrations/gutenberg/';


	/*wp_enqueue_style(
		'mbgutenberg-css',
		$url . 'style.css',
		array( 'wp-blocks' ),
		filemtime( $url . 'style.css' )
	); */
}

} // class

MBGutenBerg::init();
