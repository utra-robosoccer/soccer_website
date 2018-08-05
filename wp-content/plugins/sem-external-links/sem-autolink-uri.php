<?php
/*
Module Name: Sem Autolink URI
Description: Automatically wraps unhyperlinked uri with html anchors.
Version: 2.7
Author: Denis de Bernardy & Mike Koepke
Author URI: https://www.semiologic.com
License: Dual licensed under the MIT and GPLv2 licenses
*/

/*
Terms of use
------------

This software is copyright Denis de Bernardy & Mike Koepke, and is distributed under the terms of the MIT and GPLv2 licenses.
**/


/**
 * autolink_uri
 *
 * @package Autolink URI
 **/

class sem_autolink_uri {
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;

	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_url = '';

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_path = '';

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @return  object of this class
	 */
	public static function get_instance()
	{
		NULL === self::$instance and self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 *
	 */

	public function __construct() {
		$this->plugin_url    = plugins_url( '/', __FILE__ );
		$this->plugin_path   = plugin_dir_path( __FILE__ );

		$this->init();
    }


	/**
	 * init()
	 *
	 * @return void
	 **/

	function init() {

		$opts = sem_external_links::get_options();

		// more stuff: register actions and filters
        // after shortcodes

        add_filter('the_content', array($this, 'filter'), 12);
        add_filter('the_excerpt', array($this, 'filter'), 12);
		if ( isset( $opts['text_widgets'] ) && $opts['text_widgets'] )
	        add_filter('widget_text', array($this, 'filter'), 12);
	}

    /**
	 * filter()
	 *
	 * @param string $text
	 * @return string $text
	 **/

	function filter($text) {

		if ( empty( $text ) )
			return $text;

		global $escape_autolink_uri;
		
		$escape_autolink_uri = array();
		
		$text = sem_autolink_uri::escape($text);
		
		$text = preg_replace_callback("/
			((?<![\"'])                                     # don't look inside quotes
            (\b
            (						    # protocol or www.
				[a-z]{3,}:\/\/
			|
				www\.
			)
			(?:						    # domain
				[a-zA-Z0-9_\-]+
				(?:\.[a-zA-Z0-9_\-]+)*
			|
				localhost
			)
			(?:	                        # port
				 \:[0-9]+
			)?
			(?:						    # path (optional)
				[\/|\?][\wa-z0-9#!:\.\?\+=&%@$!'~\*,;\/\(\)\[\]\-]*
			)?
            )
            (?![\"']))
			/ix", array($this, 'url_callback'), $text);

		$text = sem_autolink_uri::unescape($text);
		
		return $text;
	} # filter()
	
	
	/**
	 * url_callback()
	 *
	 * @param array $match
	 * @return string $text
	 **/

	function url_callback($match) {
		$url = $match[0];
		$href = $url;
		
		if ( strtolower($match[1]) === 'www.' )
			$href = 'http://' . $href;
		
		$href = esc_url($href);
		
		return '<a href="' . $href . '">' . $url . '</a>';
	} # url_callback()

	/**
	 * escape()
	 *
	 * @param string $text
	 * @return string $text
	 **/

	function escape($text) {
		global $escape_autolink_uri;
		
		if ( !isset($escape_autolink_uri) )
			$escape_autolink_uri = array();
		
		foreach ( array(
			'head' => "/
				.*?
				<\s*\/\s*head\s*>
				/isx",
			'blocks' => "/
				<\s*(script|style|object|code|pre|textarea)(?:\s.*?)?>
				.*?
				<\s*\/\s*\\1\s*>
				/isx",
			'smart_links' => "/
				\[.+?\]
				/x",
			'meta' => "/
				<meta .*?>
				/isx",
			'anchors' => "/
				<a .*?>.*?<\/a>
				/isx",
			'tags' => "/
				<[^<>]+?(?:src|href|codebase|archive|usemap|data|data-.*|itemtype|xmlns|value|action|cite|background|placeholder|onclick)=[^<>]+?>
				/ix",
			) as $regex ) {
			$t = preg_replace_callback($regex, array($this, 'escape_callback'), $text);
			if ( $t !== NULL )
				$text = $t;
		}

		return $text;
	} # escape()


	/**
	 * escape_callback()
	 *
	 * @param array $match
	 * @return string $tag_id
	 **/

	function escape_callback($match) {
		global $escape_autolink_uri;
		
		$tag_id = "----escape_sem_autolink_uri:" . md5($match[0]) . "----";
		$escape_autolink_uri[$tag_id] = $match[0];
		
		return $tag_id;
	} # escape_callback()
	
	
	/**
	 * unescape()
	 *
	 * @param string $text
	 * @return string $text
	 **/

	function unescape($text) {
		global $escape_autolink_uri;
		
		if ( !$escape_autolink_uri )
			return $text;
		
		$unescape = array_reverse($escape_autolink_uri);
		
		return str_replace(array_keys($unescape), array_values($unescape), $text);
	} # unescape()
} # sem_autolink_uri

$sem_autolink_uri = sem_autolink_uri::get_instance();