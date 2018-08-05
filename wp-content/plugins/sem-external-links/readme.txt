=== External Links ===
Contributors: Denis-de-Bernardy, Mike_Koepke
Donate link: https://www.semiologic.com/donate/
Tags: external-links, nofollow, link-target, link-icon, semiologic
Requires at least: 2.8
Tested up to: 4.7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The external links plugin for WordPress lets you process outgoing links differently from internal links.


== Description ==

The external links plugin for WordPress lets you process outgoing links differently from internal links.

= Usage =

Under Settings / External Links, you can configure the plugin to:

- Process all outgoing links, rather than only those within your entries' content and text widgets.
- Add an external link icon to outgoing links. You can use a class="no_icon" attribute on links to override this.
- Add rel=nofollow to the links. (Note: You can use a rel="follow" attribute on links to override this.)
- Open outgoing links in new windows. Note that this can damage your visitor's trust towards your site in that they can think your site used a pop-under.
- Turn on "autolinks" functionality.
- Domains/subdomains you wish to Exclude from processing.


= Auto Links =

The Autolink functionality automatically converts urls to hyperlinked urls in post/page content, excerpts and text widgets.

Before:

> www.semiologic.com

After:

> [www.semiologic.com](http://www.semiologic.com)


= Help Me! =

The [Plugin's Forum](https://wordpress.org/support/plugin/sem-external-links) is the best place to report issues.


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= How can I make some links still be followed? =

The plugin supports a non-started rel="follow" attribute on links to override this.  If it detects rel="follow" it will not add the "nofollow" attribute to that link.


== Change Log ==

= 6.8.1 =

- Fix: Embedded Facebook urls no longer processed by Auto Convert text functionality

= 6.8 =

- Change: Handle links that space multiple lines (props archon810)
- Fix: Add url found in a tag with a data- attribute prefix to the Auto convert Text Urls exclusion list (Fixes problem with addtoany)
- Fix: Do not convert text urls found in schema.org meta and div tags
- Fix: Do not convert text urls found in svg xlmns attributes
- Fix: Remove strpos(): Empty needle PHP warning in sem-external-links.php (props archon810)
- Under Hood: Tested with PHP 7.x

= 6.7 =

- Fix: Some links with affiliate code may not be correctly detected as an external link.
- Fix: Auto Convert Text Urls were being applied to Text Widgets regardless of Settings
- Fix: A lengthy page or long embedded form could cause Auto Convert Text URLS to fail and result in some blanking of the text.
- Change: Site domain entered in the 'Domains To Exclude' with a trailing slash is handled better and trailing slash is simply removed.

= 6.6 =

- New option to exclude html code blocks (pre and code) from link processing
- Updated the text-domain to sem-external-links to match plugin's slug to support internationalization efforts

= 6.5.1 =

- No functionality changes in this release.
- WP 4.3 compat
- Tested against PHP 5.6

= 6.5 =

- Domains to Exclude no longer assumes subdomains meaning subdomains need to be specifically excluded now.
- Entry of Domains to Exclude now allows separation by comma, space, newline/return, and tab.  It will convert into a csv list.
- Fix placeholder links (href="#") not being excluded from processing correctly.
- Fix Plugin version number not being updated in the database options correctly.

= 6.4.1 =

- External Links with querystring not being excluded by Domains to Exclude
- Ensure http/https are stripped off exclude domains in External Links admin
- Fix wrong parameter count in strstr warning for sites using PHP 5.2 or earlier
- Fix subdomain logic when the subdomain is also used in the tld - co.city.co.us

= 6.4 =

- New setting to specify domain(s) to be excluded from any processing.  props Brian Wilson
- Remove multi-site check that was causing all links to be deemed internal.
- WP 4.1 Compat

= 6.3.1 =

- Fix bug that crept back in with embedded image with a class while the link itself had no class attribute

= 6.3 =

- Changed link detection to err on the side of a link being local rather than external to avoid false positives.
- Fixed double inclusion of class attribute under certain link attribute ordering conditions

= 6.2 =

- Additional performance changes for long post/page when Apply Globally is off.
- Links with no anchor text will have nofollow and/or target set, but no external icon
- Improved detection and handling of anchor links as internal.
- Initial support for AddThis placeholder links


= 6.1 =

- The nofollow attribute was not being set if a certain combination of the global, follow comments, set nofollow settings were set.
- Backtracked and removed the Follow Comments functionality.   It's usage was limited to certain cases and if Apply Globally was on, then it was disabled.  Supporting it was adding too complex logic.

= 6.0.1 =

- Well plugin decided to break classes on embedded images.  Obviously corrected.

= 6.0 =

- Fixed performance issues with the link processing.  Really was poor.
- Embedded the functionality of the autolink uri and the dofollow plugins (off by default in Settings)
- New option on how to treat subdomains of installed plugin domain as local or external
- Fixed several php strict warnings

= 5.5.4 =

- Remove HTML comments added in 5.4.1 to assist in troubleshooting some select site issues

= 5.5.3 =

- Use template_redirect hook and put ourselves after default hooks that most 404/redirect plugins want to use and Yoast's at priority 99999

= 5.5.2 =

- Fix compatibility with Yoast WP SEO plugin when Force Title Rewrite option is on and using the Apply Globally setting of this plugin.

= 5.5.1 =

- Additional tweak to global callback processing

= 5.5 =

- Use wp_print_footer_scripts hook instead of wp_footer as some themes fail to call wp_footer();
- Use own custom version of the anchor_utils class
- Content, excerpt and comment filters no longer called when Apply Globally is selected.   Improves performance.

= 5.4.1 =

- Troubleshooting release.  Adds a few html comments in the page source to ensure hooks are being called.

= 5.4 =

- Handle nested parenthesis in javascript event attributes on links and images

= 5.3.2 =

- Temporarily placeholders links - http:// and https:// (no other url components) are no longer processed.

= 5.3.1 =

- Fix localization

= 5.3 =

- Fix: Conflict with Auto Thickbox plugin that would result in text widgets still being filtered even though option was turned off
- Fix: Ensure this plugin filter is executed way back in the change to prevent other plugins/themes from reversing our changes
- Code refactoring
- WP 3.9 compat

= 5.2.1 =

- Checks for new sem_dofollow class to determine if Do Follow plugin is active
- WP 3.8 compat

= 5.2 =

- Further updates to the link attribute parsing code
- Fixed bug where external link was not processed if it was preceded by an empty text anchor link.

= 5.1 =

- Take two!  With issues now with breaking google adsense code reverted back to 4.2 parsing code but added more advanced dom attribute parsing code to handle various link configurations.

= 5.0 =

- Completely replaced the mechanism for parsing links to resolve the various errors that have been occurring with different external services' link attributes
- Tested with WP 3.7

= 4.2 =

- WP 3.6 compat
- PHP 5.4 compat
- Fixed issue with parsing of links with non-standard (class, href, rel, target) attributes included in the <a> tag.  This caused Twitter Widgets to break.
- Fixed issue where the external link icon was not added if the url specified by href had a preceding space  href=" http://www.example.com"
- Fixed issue with links containing onClick (or other javascript event) attributes with embedded javascript code.  WordPress' Threaded Comments does this
- Fixed issue with 2 spaces being injected between <a and class/href/rel/etc.   i.e   <a  href="http://example.com">

= 4.1 =

- WP 3.5 compat

= 4.0.6 =

- WP 3.0.1 compat

= 4.0.5 =

- WP 3.0 compat

= 4.0.4 =

- Force a higher pcre.backtrack_limit and pcre.recursion_limit to avoid blank screens on large posts

= 4.0.3 =

- Improve case-insensitive handling of domains
- Improve image handling
- Switch back to using a target attribute: work around double windows getting opened in Vista/IE7
- Disable entirely in feeds

= 4.0.2 =

- Don't enforce new window pref in feeds

= 4.0.1 =

- Ignore case when comparing domains

= 4.0 =

- Allow to force a follow when the nofollow option is toggled
- Enhance escape/unescape methods
- Localization
- Code enhancements and optimizations

