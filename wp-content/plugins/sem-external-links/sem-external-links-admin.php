<?php
/**
 * external_links_admin
 *
 * @package External Links
 **/

class external_links_admin {
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
		// register actions and filters
		add_action('settings_page_external-links', array($this, 'save_options'), 0);
	}

    /**
	 * save_options()
	 *
	 * @return void
	 **/
	
	function save_options() {
		if ( !$_POST || !current_user_can('manage_options') )
			return;
		
		check_admin_referer('sem_external_links');
		
		foreach ( array('global', 'icon', 'target', 'nofollow', 'text_widgets', 'autolinks',
			          'subdomains_local', 'exclude_code_blocks') as $var )
			$$var = isset($_POST[$var]);

		$exclude_domains = stripslashes($_POST['exclude_domains']);
		$domains = preg_split("/[\s,]+/", $exclude_domains);
		$exclude_domains = array();

		global $sem_external_links;
		foreach( $domains as $num => $domain ) {
			$domain = trim($domain);
			$domain = untrailingslashit($domain);
			$domain = str_replace('http://', '', $domain);
			$domain = str_replace('https://', '', $domain);
			if (  $sem_external_links->is_valid_domain_name($domain)) {
				$domain = str_replace('www.', '', $domain);
				if ( !in_array( $domain, $exclude_domains) )
					$exclude_domains[] = $domain;
			}
		}

		$exclude_domains = implode( ', ', $exclude_domains );

		$version = sem_external_links_version;
		update_option('external_links', compact('global', 'icon', 'target', 'nofollow', 'text_widgets',
			'autolinks', 'subdomains_local', 'version', 'exclude_domains', 'exclude_code_blocks'));
		
		echo "<div class=\"updated fade\">\n"
			. "<p>"
				. "<strong>"
				. __('Settings saved.', 'sem-external-links')
				. "</strong>"
			. "</p>\n"
			. "</div>\n";
	} # save_options()
	
	
	/**
	 * edit_options()
	 *
	 * @return void
	 **/
	
	static function edit_options() {
		echo '<div class="wrap">' . "\n"
			. '<form method="post" action="">';

		wp_nonce_field('sem_external_links');
		
		$options = sem_external_links::get_options();
		
		if ( $options['nofollow'] && ( function_exists('strip_nofollow') || class_exists('sem_dofollow') ) ) {
			echo "<div class=\"error\">\n"
				. "<p>"
					. __('Note: Your rel=nofollow preferences is being ignored because the dofollow plugin is enabled on your site.', 'sem-external-links')
				. "</p>\n"
				. "</div>\n";
		}
		
		echo '<h2>' . __('External Links Settings', 'sem-external-links') . '</h2>' . "\n";
		
		echo '<table class="form-table">' . "\n";

		echo '<tr>' . "\n"
			. '<th scope="row">'
			. __('Apply Globally', 'sem-external-links')
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. '<input type="checkbox" name="global"'
				. checked($options['global'], true, false)
				. ' />'
			. '&nbsp;'
			. __('Apply these settings to all outbound links on the site except those in scripts, styles and the html head section.', 'sem-external-links')
			. '</label>'
			. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '<tr>' . "\n"
			. '<th scope="row">'
			. __('Apply to Text Widgets', 'sem-external-links')
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. '<input type="checkbox" name="text_widgets"'
				. checked($options['text_widgets'], true, false)
				. ' />'
			. '&nbsp;'
			. __('Apply these settings to any text widgets in addition to post, page and comments content.', 'sem-external-links')
			. '</label>'
			. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '<tr>' . "\n"
			. '<th scope="row">'
			. __('Treat Subdomains as Local', 'sem-external-links')
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. '<input type="checkbox" name="subdomains_local"'
				. checked($options['subdomains_local'], true, false)
				. ' />'
			. '&nbsp;'
			. __('Treat any subdomains for this site as a local link.', 'sem-external-links')
			. '</label>'
			. '<br />' . "\n"
			. '<i>' . __('Example: If your site is at domain.com and you also have store.domain.com, any link to store.domain.com will be treated as local.', 'sem-external-links') . '<i>'
			. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '<tr>' . "\n"
			. '<th scope="row">'
			. __('Auto Convert Text Urls', 'sem-external-links')
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. '<input type="checkbox" name="autolinks"'
				. checked($options['autolinks'], true, false)
				. ' />'
			. '&nbsp;'
			. __('Automatically converts text urls into clickable urls.', 'sem-external-links')
			. '</label>'
			. '<br />' . "\n"
			. '<i>' . __('Note: If this option is enabled then if www.example.com is found in your text, it will be converted to an html &lt;a&gt; link."', 'sem-external-links')
			. '<br />' . "\n"
			. __('This conversion will occur first so external link treatment for nofollow, icon and target will be applied to this auto links.', 'sem-external-links') . '</i>'
			. '</td>' . "\n"
			. '</tr>' . "\n";
		
		echo '<tr>' . "\n"
			. '<th scope="row">'
			. __('Add No Follow', 'sem-sem-external-links')
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. '<input type="checkbox" name="nofollow"'
				. checked($options['nofollow'], true, false)
				. ' />'
			. '&nbsp;'
			. __('Add a rel="nofollow" attribute to outbound links.', 'sem-external-links')
			. '</label>'
			. '<br />' . "\n"
			. '<i>' . __('Note: You can override this behavior by adding the attribute rel="follow" to individual links.', 'sem-external-links')
			. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '<tr>' . "\n"
			. '<th scope="row">'
			. __('Add Icons', 'sem-external-links')
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. '<input type="checkbox" name="icon"'
				. checked($options['icon'], true, false)
				. ' />'
			. '&nbsp;'
			. __('Mark outbound links with an icon.', 'sem-external-links')
			. '</label>'
			. '<br />' . "\n"
			. '<i>' .__('Note: You can override this behavior by adding a class="no_icon" or "noicon" to individual links.', 'sem-external-links') . '</i>'
			. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '<tr>' . "\n"
			. '<th scope="row">'
			. __('Open in New Windows', 'sem-external-links')
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. '<input type="checkbox" name="target"'
				. checked($options['target'], true, false)
				. ' />'
			. '&nbsp;'
			. __('Open outbound links in new windows.', 'sem-external-links')
			. '</label>'
			. '<br />' . "\n"
			. '<i>' . __('Note: Some usability experts discourage this, claiming that <a href="http://www.useit.com/alertbox/9605.html">this can damage your visitors\' trust</a> towards your site. Others highlight that computer-illiterate users do not always know how to use the back button, and encourage the practice for that reason.', 'sem-external-links') . '</i>'
			. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '<tr>' . "\n"
			. '<th scope="row">'
			. __('Exclude HTML Code Blocks', 'sem-external-links')
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. '<input type="checkbox" name="exclude_code_blocks"'
				. checked($options['exclude_code_blocks'], true, false)
				. ' />'
			. '&nbsp;'
			. __('Do not process links in html code blocks.', 'sem-external-links')
			. '</label>'
			. '<br />' . "\n"
			. '<i>' . __('Links found inside either &ltcode&gt or &ltpre&gt html tags will be ignored.', 'sem-external-links') . '</i>'
			. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '<tr>' . "\n"
			. '<th scope="row">'
			. __('Domains to Exclude', 'sem-external-links')
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. __('External site domains that should be excluded from processing:', 'sem-external-links')
			. '<textarea name="exclude_domains" cols="58" rows="4" class="widefat">'
			. esc_html($options['exclude_domains'])
			. '</textarea>' . "\n"
			. __('Domains and subdomains should be separated by a comma, space or carriage return. &nbsp;http://, https://, www. should not be included and will be stripped off.', 'sem-external-links')
			. '</label>&nbsp;&nbsp;'
			. '<i>' .__('Example: domain.com, domain.net, sub.domain.com, somesite.com, external.org.', 'sem-external-links') . '</i>'
			. '<br />' . "\n"
			. '</td>'
			. '</tr>' . "\n";

		echo '</table>' . "\n";
		
		echo '<p class="submit">'
			. '<input type="submit"'
				. ' value="' . esc_attr(__('Save Changes', 'sem-external-links')) . '"'
				. ' />'
			. '</p>' . "\n";
		
		echo '</form>' . "\n"
			. '</div>' . "\n";
	} # edit_options()
} # external_links_admin

$external_links_admin = external_links_admin::get_instance();
