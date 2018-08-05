<?php
/*
Plugin Name: External Links
Plugin URI: http://www.semiologic.com/software/external-links/
Description: Marks outbound links as such, with various effects that are configurable under <a href="options-general.php?page=external-links">Settings / External Links</a>.
Version: 6.8.1
Author: Denis de Bernardy & Mike Koepke
Author URI: https://www.semiologic.com
Text Domain: sem-external-links
Domain Path: /lang
License: Dual licensed under the MIT and GPLv2 licenses
*/

/*
Terms of use
------------

This software is copyright Denis de Bernardy & Mike Koepke, and is distributed under the terms of the MIT and GPLv2 licenses.

**/

define('sem_external_links_version', '6.8.1');

/**
 * external_links
 *
 * @package External Links
 **/

class sem_external_links {

	protected $opts;

	protected $exclude_domains;

	protected $anchor_utils;

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
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and
	 * front-end for example).
	 *
	 * @wp-hook init
	 * @param   string $domain
	 * @return  void
	 */
	public function load_language( $domain )
	{
		load_plugin_textdomain(
			$domain,
			FALSE,
			dirname(plugin_basename(__FILE__)) . '/lang'
		);
	}

	/**
	 * Constructor.
	 *
	 *
	 */
    public function __construct() {
	    $this->plugin_url    = plugins_url( '/', __FILE__ );
        $this->plugin_path   = plugin_dir_path( __FILE__ );
        $this->load_language( 'sem-external-links' );

	    add_action( 'plugins_loaded', array ( $this, 'init' ) );
    }


	/**
	 * init()
	 *
	 * @return void
	 **/

	function init() {
		// more stuff: register actions and filters
		$this->opts = sem_external_links::get_options();

		$this->exclude_domains = array();
		if ( isset($this->opts['exclude_domains']) ) {
			$this->exclude_domains = preg_split("/[\s,]+/", $this->opts['exclude_domains']);
		}

		if ( !is_admin() ) {
			$inc_text_widgets = false;
			if ( isset( $this->opts['text_widgets'] ) && $this->opts['text_widgets'] )
				$inc_text_widgets = true;

			if ( $this->opts['icon'] )
				add_action('wp_enqueue_scripts', array($this, 'styles'), 5);

			if ( $this->opts['autolinks'] ) {
				if ( !class_exists('sem_autolink_uri') )
				    include $this->plugin_path . '/sem-autolink-uri.php';
			}

			if ( $this->opts['global'] ) {
				if ( !class_exists('external_links_anchor_utils') )
				    include $this->plugin_path . '/external-links-anchor-utils.php';

				$this->anchor_utils = new external_links_anchor_utils( $this );
			}
			else {
				add_filter('the_content', array($this, 'process_content_content'), 100000);
//				add_filter('the_excerpt', array($this, 'process_content_excerpt'), 100000);
				add_filter('comment_text', array($this, 'process_content_comment'), 100000);
				if ( $inc_text_widgets )
					add_filter('widget_text', array($this, 'process_content_widget'), 100000);
			}
		}
		else {
			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('load-settings_page_external-links', array($this, 'external_links_admin'));
		}
	}

	/**
	* external_links_admin()
	*
	* @return void
	**/
	function external_links_admin() {
		include_once $this->plugin_path . '/sem-external-links-admin.php';
	}

    /**
	 * styles()
	 *
	 * @return void
	 **/

	function styles() {
		$folder = plugin_dir_url(__FILE__);
		wp_enqueue_style('external-links', $folder . 'sem-external-links.css', null, '20090903');
	} # styles()


	/**
	 * process_content()
	 *
	 * @param string $text
	 * @param string $context
	 * @return string $text
	 **/

	function process_content($text, $context = "global") {
		// short circuit if there's no anchors at all in the text
		if ( false === stripos($text, '<a ') )
			return($text);

		$escape_needed = array( 'global', 'content', 'widgets' );
		if ( in_array($context, $escape_needed ) ) {
			global $escape_anchor_filter;
			$escape_anchor_filter = array();

			$text = $this->escape($text, $context);
		}

		// find all occurrences of anchors and fill matches with links
		preg_match_all("/
					<\s*a\s+
					([^<>]+)
					>
					(.*?)
					<\s*\/\s*a\s*>
					/isx", $text, $matches, PREG_SET_ORDER);

		$raw_links = array();
		$processed_links = array();

		foreach ($matches as $match)
		{
			$updated_link = $this->process_link($match);
			if ( $updated_link ) {
				$raw_links[]     = $match[0];
				$processed_links[] = $updated_link;
			}
		}

		if ( !empty($raw_links) && !empty($processed_links) )
			$text = str_replace($raw_links, $processed_links, $text);

		if ( in_array($context, $escape_needed ) ) {
			$text = $this->unescape($text);
		}

		return $text;
	} # process_content()


	/**
	 * process_content_comment()
	 *
	 * @param string $text
	 * @return string $text
	 **/

	function process_content_comment($text) {
		return $this->process_content( $text, 'comment' );
	}

	/**
	 * process_content_content()
	 *
	 * @param string $text
	 * @return string $text
	 **/

	function process_content_content($text) {
		return $this->process_content( $text, 'content' );
	}

	/**
	 * process_content_excerpt()
	 *
	 * @param string $text
	 * @return string $text
	 **/

	function process_content_excerpt($text) {
		return $this->process_content( $text, 'excerpt' );
	}

	/**
	 * process_content_widget()
	 *
	 * @param string $text
	 * @return string $text
	 **/

	function process_content_widget($text) {
		return $this->process_content( $text, 'widget' );
	}

	/**
	 * escape()
	 *
	 * @param string $text
	 * @param string $context
	 * @return string $text
	 **/

	function escape($text, $context) {
		global $escape_anchor_filter;

		if ( !isset($escape_anchor_filter) )
			$escape_anchor_filter = array();

		$exclusions = array();

		if ( $context == 'global' ) {
			$exclusions['head'] = "/
							.*?
							<\s*\/\s*head\s*>
							/isx";
		}

		$ignore_blocks = 'script|style|object|textarea';
		if ( $this->opts['exclude_code_blocks'] ) {
			$ignore_blocks .= '|pre|code';
		}

		$exclusions['blocks'] = "/
						<\s*(" . $ignore_blocks . ")(?:\s.*?)?>
						.*?
						<\s*\/\s*\\1\s*>
						/isx";

		foreach ( $exclusions as $regex ) {
			$text = preg_replace_callback($regex, array($this, 'escape_callback'), $text);
		}

		return $text;
	} # escape()


	/**
	 * escape_callback()
	 *
	 * @param array $match
	 * @return string $text
	 **/

	function escape_callback($match) {
		global $escape_anchor_filter;

		$tag_id = "----escape_sem_external_links:" . md5($match[0]) . "----";
		$escape_anchor_filter[$tag_id] = $match[0];

		return $tag_id;
	} # escape_callback()


	/**
	 * unescape()
	 *
	 * @param string $text
	 * @return string $text
	 **/

	function unescape($text) {
		global $escape_anchor_filter;

		if ( !$escape_anchor_filter )
			return $text;

		$unescape = array_reverse($escape_anchor_filter);

		return str_replace(array_keys($unescape), array_values($unescape), $text);
	} # unescape()


	/**
	 * filter_callback()
	 *
	 * @param array $match
	 * @return string $str
	 **/

	function process_link($match) {
		# parse anchor
		$anchor = $this->parse_anchor($match);

		if ( !$anchor )
			return false;

		# filter anchor
		$anchor = $this->filter_anchor( $anchor );

		if ( $anchor )
			$anchor = $this->build_anchor($match[0], $anchor);

		return $anchor;
	} # process_link()


	/**
	 * parse_anchor()
	 *
	 * @param array $match
	 * @return array $anchor
	 **/

	function parse_anchor($match) {
		$anchor = array();
		$anchor['attr'] = $this->parseAttributes( $match[1] );

		if ( !is_array($anchor['attr']) || empty($anchor['attr']['href']) # parser error or no link
		|| trim($anchor['attr']['href']) != esc_attr($anchor['attr']['href'], null, 'db') ) # likely a script
			return false;

		foreach ( array('class', 'rel') as $attr ) {
			if ( !isset($anchor['attr'][$attr]) ) {
				$anchor['attr'][$attr] = array();
			} else {
				$anchor['attr'][$attr] = explode(' ', $anchor['attr'][$attr]);
				$anchor['attr'][$attr] = array_map('trim', $anchor['attr'][$attr]);
			}
		}

		$anchor['body'] = $match[2];

		$anchor['attr']['href'] = @html_entity_decode($anchor['attr']['href'], ENT_COMPAT, get_option('blog_charset'));

		return $anchor;
	} # parse_anchor()


	/**
	 * build_anchor()
	 *
	 * @param $link
	 * @param array $anchor
	 * @return string $anchor
	 */

	function build_anchor($link, $anchor) {

		$attrs = array( 'class', 'rel', 'target');

		foreach ( $attrs as $attr ) {
			if ( isset($anchor['attr'][$attr]) ) {
				$new_attr_value = null;
				$values = $anchor['attr'][$attr];
				if ( is_array($values) ) {
					$values = array_unique($values);
					if ( $values )
						$new_attr_value = implode(' ',  $values );
				} else {
					$new_attr_value = $values;
				}

				if ( $new_attr_value )
					$link = $this->update_attribute($link, $attr, $new_attr_value);
			}
		}

		return $link;
	} # build_anchor()


	/**
	 * Updates attribute of an HTML tag.
	 *
	 * @param $html
	 * @param $attr_name
	 * @param $new_attr_value
	 * @return string
	 */
	function update_attribute($html, $attr_name, $new_attr_value) {

		$attr_value     = false;
		$quote          = false; // quotes to wrap attribute values

		preg_match('/(<a.*>)/isU', $html, $match);

		$link_str = $match[1];
		if ($link_str == "")
			return $html;

		$re = '/' . preg_quote($attr_name) . '=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/is';
		if (preg_match($re, $link_str, $matches)
		) {
			// two possible ways to get existing attributes
			$attr_value = $matches[2];

			$quote = false !== stripos($html, $attr_name . "='") ? "'" : '"';
		}

		if ($attr_value)
		{
			//replace current attribute
			$html = str_ireplace("$attr_name=" . $quote . "$attr_value" . $quote,
				$attr_name . '="' . esc_attr($new_attr_value) . '"', $html);
		}
		else {
			// attribute does not currently exist, add it
			$pos = strpos( $html, '>' );
			if ($pos !== false) {
				$html = substr_replace( $html, " $attr_name=\"" . esc_attr($new_attr_value) . '">', $pos, strlen('>') );
			}
		}

		return $html;
	} # update_attribute()

	/**
	 * filter_anchor()
	 *
	 * @param $anchor
	 * @return string
	 */

	function filter_anchor($anchor) {
		# disable in feeds
		if ( is_feed() )
			return null;

		// if we don't have a href or find a ? only obviously this some illformed or temp link
		if ( empty( $anchor['attr']['href'] ) || (substr($anchor['attr']['href'], 0, 1) == '?' ) )
			return null;

		// we have a placeholder link.    Normally treat as a local but there are exceptions like add this
		if ( substr($anchor['attr']['href'], 0, 1) == '#' ) {
			// now is this a full blown anchor?
			if ( strlen( $anchor['attr']['href'] ) > 1 ) {
				// yep, bail
				return null;
			}

			// can we find addthis_button class?  if so then don't bail and process external
			$addthis = false;
			foreach( $anchor['attr']['class'] as $c => $class) {
				$pos = strpos( $class, "addthis_button" );
				if ( false !== $pos ) {
					$addthis = true;
					break;
				}
			}
			// no addthis.  bail as we got just a an anchor only
			if ( !$addthis )
				return null;
		}
		# ignore local urls
		elseif ( !sem_external_links::is_external($anchor['attr']['href']) )
			return null;

		# no icons for images
		$is_image = ( false !== strpos($anchor['body'], "<img ") );

		$updated = false;
		if ( !in_array('external', $anchor['attr']['class']) ) {
			$anchor['attr']['class'][] = 'external';
			$updated = true;
		}

		if ( !$is_image && $this->opts['icon'] && !in_array('external_icon', $anchor['attr']['class'])
			&& !in_array('no_icon', $anchor['attr']['class'])
			&& !in_array('noicon', $anchor['attr']['class']) ) {
			// don't add an icon if there is no text in the link
			if ($anchor['body'] != null) {
				$anchor['attr']['class'][] = 'external_icon';
				$updated = true;
			}
		}

		if ( $this->opts['nofollow'] && !in_array('nofollow', $anchor['attr']['rel'])
			&& !in_array('follow', $anchor['attr']['rel']) ) {
				$anchor['attr']['rel'][] = 'nofollow';
				$updated = true;
		}

		if ( $this->opts['target'] && empty($anchor['attr']['target']) ) {
		 	$anchor['attr']['target'] = '_blank';
			$updated = true;
		}

		if ( $updated )
			return $anchor;
		else
			return null;
	} # filter_anchor()


	function parseAttributes($text) {
	    $attributes = array();
	    $pattern = '#(?(DEFINE)
	            (?<name>[a-zA-Z][a-zA-Z0-9-:]*)
	            (?<value_double>"[^"]+")
	            (?<value_single>\'[^\']+\')
	            (?<value_none>[^\s>]+)
	            (?<value>((?&value_double)|(?&value_single)|(?&value_none)))
	        )
	        (?<n>(?&name))(=(?<v>(?&value)))?#xs';

	    if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
	        foreach ($matches as $match) {
	            $attributes[$match['n']] = isset($match['v'])
	                ? trim($match['v'], '\'"')
	                : null;
	        }
	    }

	    return $attributes;
	}

	/**
	 * is_external()
	 *
	 * @param string $url
	 * @return bool $is_external
	 **/

	function is_external($url) {
		if ( (substr($url, 0, 2) != '//') && (strpos($url, 'http://') !== false)
			&& (strpos($url, 'https://') !== false) )
			return false;

		if ( $url == 'http://' || $url == 'https://' )
			return false;

/*		if ( preg_match("~/go(/|\.)~i", $url) )
			return false;
*/

		static $site_domain;

		if ( !isset($site_domain) ) {
			$site_domain = home_url();
			$site_domain = parse_url($site_domain);
			$site_domain = $site_domain['host'];
            if ($site_domain == false)
                return false;
            elseif (is_array($site_domain)) {
                if (isset($site_domain['host']))
                    $site_domain = $site_domain['host'];
                else
                    return false;
            }
			$site_domain = str_replace('www.', '', $site_domain);

			# The following is not bullet proof, but it's good enough for a WP site
			if ( $site_domain != 'localhost' && !preg_match("/\d+(\.\d+){3}/", $site_domain) ) {
				if ( preg_match("/\.([^.]+)$/", $site_domain, $tld) ) {
					$tld = end($tld);
				} else {
					$site_domain = false;
					return false;
				}

				$site_domain = substr($site_domain, 0, strlen($site_domain) - 1 - strlen($tld));

				if ( preg_match("/\.([^.]+)$/", $site_domain, $subtld) ) {
					$subtld = end($subtld);
					if ( strlen($subtld) <= 4 ) {
						$site_domain = substr($site_domain, 0, strlen($site_domain) - 1 - strlen($subtld));
						$site_domain = explode('.', $site_domain);
						$site_domain = array_pop($site_domain);
						$site_domain .= ".$subtld";
					} else {
						$site_domain = $subtld;
					}
				}

				$site_domain .= ".$tld";
			}

			$site_domain = strtolower($site_domain);
		}

		if ( !$site_domain )
			return false;

		$link_domain = @parse_url($url);
        if ($link_domain === false)
            return true;
        elseif (is_array($link_domain)) {
            if (isset($link_domain['host'])) {
		        $link_domain = $link_domain['host'];
	            $link_domain = $this->remove_querystring( $link_domain );
            }
            else
                return false;
        }

		$link_domain = strtolower($link_domain);
		$link_domain = str_replace('www.', '', $link_domain);

		if ( !empty($this->exclude_domains) ) {
			if ( in_array( $link_domain, $this->exclude_domains) )
				return false;
		}

		if ( $this->opts['subdomains_local'] ) {
			$subdomains = $this->extract_subdomains($link_domain);
			if ( $subdomains != '')
				$link_domain = $this->str_replace_first($subdomains, '', $link_domain);
		}

		if ( $site_domain == $link_domain ) {
			return false;
		}

/*			elseif ( function_exists('is_multisite') && is_multisite() ) {
			return false;
		}
		else {
			$site_elts = explode('.', $site_domain);
			$link_elts = explode('.', $link_domain);

			while ( ( $site_elt = array_pop($site_elts) ) && ( $link_elt = array_pop($link_elts) ) ) {
				if ( $site_elt !== $link_elt )
					return false;
			}

			return empty($link_elts) || empty($site_elts);
		}
*/
		// we made it to the end so we must have an external link
	    return true;
	} # is_external()

	/**
	 * extract_domain()
	 *
	 * @param string $domain
	 * @return string
	 **/
	function extract_domain($domain)
	{
	    if(preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches))
	    {
	        return $matches['domain'];
	    } else {
	        return $domain;
	    }
	} # extract_domain()

	/**
	 * extract_subdomains()
	 *
	 * @param string $domain
	 * @return string
	 **/
	function extract_subdomains($domain)
	{
	    $subdomains = $domain;
	    $domain = $this->extract_domain($subdomains);

	    if(!empty($domain)){
	        $subdomains = rtrim( substr($subdomains, 0, strpos($subdomains, $domain)) );
	    }

	    return $subdomains;
	} # extract_subdomains()

	/**
	 * is_valid_domain_name()
	 *
	 * @param string $domain_name
	 * @return bool
	 **/
	function is_valid_domain_name($domain_name)
	{
	    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
	            && preg_match("/^.{1,253}$/", $domain_name) //overall length check
	            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
	}

	/**
	 * remove_querystring_var()
	 *
	 * @param string $url
	 * @return string
	 **/
	function remove_querystring($url) {
		$arr = explode("?", $url, 2);
		$domain = $arr[0];

		return $domain;
	}

	function str_replace_first($search, $replace, $subject) {
	    $pos = strpos($subject, $search);
	    if ($pos !== false) {
	        $subject = substr_replace($subject, $replace, $pos, strlen($search));
	    }
	    return $subject;
	}

	/**
	 * get_options
	 *
	 * @return array $options
	 **/

	static function get_options() {
		static $o;
		
		if ( !is_admin() && isset($o) )
			return $o;
		
		$o = get_option('external_links');

		if ( $o === false || !isset($o['text_widgets']) || !isset($o['autolinks']) || !isset($o['version']) )
			$o = sem_external_links::init_options();

		if ( version_compare( sem_external_links_version, $o['version'], '>' ) )
			$o = sem_external_links::init_options();

		return $o;
	} # get_options()


	/**
	 * init_options()
	 *
	 * @return array $options
	 **/

	static function init_options() {
		$o = get_option('external_links');

		$defaults = array(
					'global' => false,
					'icon' => false,
					'target' => false,
					'nofollow' => true,
					'text_widgets' => true,
					'autolinks' => false,
					'subdomains_local' => true,
					'version' => sem_external_links_version,
					'exclude_domains' => '',
					'exclude_code_blocks' => false,
					);

		if ( !$o )
			$updated_opts  = $defaults;
		else
			$updated_opts = wp_parse_args($o, $defaults);

		if ( !isset( $o['version'] )) {

			if ( sem_external_links::replace_plugin('sem-autolink-uri/sem-autolink-uri.php') )
				$updated_opts['autolinks'] = true;
		}

		$updated_opts['version'] = sem_external_links_version;

		update_option('external_links', $updated_opts);

		return $updated_opts;
	} # init_options()

	/**
	 * replace_plugin()
	 *
	 * @param $plugin_name
	 * @return bool
	 */
	static function replace_plugin( $plugin_name ) {
		$active_plugins = get_option('active_plugins');

		if ( !is_array($active_plugins) )
		{
			$active_plugins = array();
		}

		$was_active = false;
		foreach ( (array) $active_plugins as $key => $plugin )
		{
			if ( $plugin == $plugin_name )
			{
				$was_active = true;
				unset($active_plugins[$key]);
				break;
			}
		}

		sort($active_plugins);

		update_option('active_plugins', $active_plugins);

		return $was_active;
	}
	/**
	 * admin_menu()
	 *
	 * @return void
	 **/
	
	function admin_menu() {
		add_options_page(
			__('External Links', 'sem-external-links'),
			__('External Links', 'sem-external-links'),
			'manage_options',
			'external-links',
			array('external_links_admin', 'edit_options')
			);
	} # admin_menu()


} # external_links

$sem_external_links = sem_external_links::get_instance();
