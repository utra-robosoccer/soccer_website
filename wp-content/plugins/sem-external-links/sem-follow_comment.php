<?php
/*
Module Name: Sem Do Follow
Description: Removes the <a href="http://www.semiologic.com/2005/02/05/prepare-for-more-comment-spam-not-less/">evil nofollow attribute</a> that WordPress adds in comments.
Version: 4.2.1
Author: Denis de Bernardy, Mike Koepke
Author URI: https://getsemiologic.com
License: Dual licensed under the MIT and GPLv2 licenses
*/

/*
Terms of use
------------

This software is copyright Denis de Bernardy & Mike Koepke, and is distributed under the terms of the MIT and GPLv2 licenses.


Hat tips
--------

	* Sebastian Herp <http://sebbi.de>
	* Thomas Parisot <http://oncle-tom.net>
**/


class sem_follow_comment {
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
		// more stuff: register actions and filters
		add_filter('get_comment_author_link', array($this, 'strip_nofollow'), 15);
		add_filter('comment_text', array($this, 'strip_nofollow'), 15);
		remove_filter('pre_comment_content', 'wp_rel_nofollow', 15);
	}

	/**
	 * strip_nofollow()
	 *
	 * @param string $text
	 * @return string $text
	 **/

	function strip_nofollow($text = '') {
		return preg_replace_callback("/<\s*a\s+(.+?)>/is", array($this, 'strip_nofollow_callback'), $text);
	} # strip_nofollow()


	/**
	 * strip_nofollow_callback()
	 *
	 * @param array $match
	 * @return string $text
	 **/

	function strip_nofollow_callback($match) {
		$attr = $match[1];
		$attr = " $attr ";
		$attr = preg_replace("/
			\s
			rel\s*=\s*(['\"])
			([^\\1]*?\s+)?
			nofollow
			(\s+[^\\1]*?)?
			\\1
			/ix", " rel=$1$2$3$1", $attr);
		$attr = preg_replace("/
			\s
			rel\s*=\s*(['\"])\s*\\1
			/ix", '', $attr);
		$attr = trim($attr);
		return '<a ' . $attr . '>';
	} # strip_nofollow_callback()
} // sem_follow_comment

$sem_follow_comment = sem_follow_comment::get_instance();