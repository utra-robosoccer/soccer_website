<?php
namespace MaxButtons;

		$action = 'install-plugin';
		$slug = 'share-button';
		$url = wp_nonce_url(
			add_query_arg(
				array(
				    'action' => $action,
				    'plugin' => $slug
				),
				admin_url( 'update.php' )
			),
			$action.'_'.$slug
		);

$page_title = __('WordPress Share Buttons', 'maxbuttons');

$admin = MB()->getClass('admin');
$admin->get_header(array("tabs_active" => true, "title" => $page_title, 'action' => 'gosocial'));

$img_url = MB()->get_plugin_url() . "images/gosocial";
?>


<div class='social-share-move'>
	<div class='container'>
		<div class='topbox'>

			<img src="<?php echo $img_url ?>/icon-128x128.png" class='icon-left'/>
			<h1><?php _e('MaxButtons Share Buttons', 'maxbuttons'); ?></h1>

			<p><?php _e('We created a brand new plugin to make your Social Share experiences better.','maxbuttons'); ?></p>
		</div>

		<div class='imagebox'>
			<img src="<?php echo $img_url ?>/screen-presets.png" alt='Presets example' />
		</div>

		<div class='featurebox'>
			<div class='block'>
				<h3><?php _e('Features','maxbuttons'); ?></h3>
				<ul class='fa-ul'>
						<li><span class="fa-li"><i class="fas fa-check"></i></span><?php _e('Presets to get going quickly','maxbuttons'); ?></li>
						<li><span class="fa-li"><i class="fas fa-check"></i></span><?php _e('Many networks: Facebook,
		    Twitter,
		    Instagram,
		    Pinterest,
		    Linkedin,
		    Google+,
		    YouTube,
		    Pinterest,
		    VKontakte,
		    StumbleUpon,
		    Reddit,
		    Whatsapp,
		    Buffer','maxbuttons'); ?>
						</li>
						<li><span class="fa-li"><i class="fas fa-check"></i></span><?php _e('Customizable layout','maxbuttons'); ?></li>
				</ul>
			</div>
			<div class='block'>
				<h3><?php _e('Only with MaxButton PRO:','maxbuttons'); ?></h3>

				<ul class='fa-ul'>
						<li><span class="fa-li"><i class="fas fa-check"></i></span><?php _e('Customize Twitter hashtag per post for maximum SEO value','maxbuttons'); ?> </li>
						<li><span class="fa-li"><i class="fas fa-check"></i></span><?php _e('More options for customization','maxbuttons'); ?></li>
						<li><span class="fa-li"><i class="fas fa-check"></i></span><?php _e('Add custom buttons via MaxButtons','maxbuttons'); ?> </li>
				</ul>
			</div> <!-- block -->

		</div>
		<div class='linkbox'>
			<p><a href='<?php echo $url ?>' class='button-primary install-now large '><?php _e('Install for Free!','maxbuttons') ?></a></p>
			<p><a href='https://wordpress.org/plugins/share-button/' target='_blank'><?php _e('Check plugin on WordPress site','maxbuttons'); ?></a></p>
		</div>
	</div> <!-- inside -->
</div>


</div> <!-- main -->

<div class="ad-wrap">
	<?php do_action("mb-display-ads"); ?>
</div>

<?php
$admin->get_footer();
