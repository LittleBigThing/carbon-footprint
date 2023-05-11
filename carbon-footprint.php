<?php
/**
 * Plugin Name: Carbon Footprint
 * Plugin URI: https://littlebigthings.be
 * Description: A plugin to spread awareness about the carbon footprint of websites and to help making WordPress sites become more sustainable. The information is based on data from the Website Carbon Calculator.
 * Requires at least: 5.8
 * Requires PHP: 5.6
 * Version: 1.0
 * Author: LittleBigThings
 * Author URI: https://littlebigthings.be
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: carbon-footprint
 *
 * @package carbon-footprint
 */

// Only load when in admin.
if ( is_admin() ) {

	require_once plugin_dir_path( __FILE__ ) . 'dashboard/widget.php';

	require_once plugin_dir_path( __FILE__ ) . 'inc/helpers.php';
	require_once plugin_dir_path( __FILE__ ) . 'inc/class-site-health.php';
}

// Get translation going.
function carbonfootprint_plugin_init() {
	
	load_plugin_textdomain( 'carbon-footprint', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'carbonfootprint_plugin_init' );

/**
 * Set transient when plugin is activated to show an admin notice on plugin activation (only).
 *
 * @since 1.0
 */
function carbonfootprint_admin_notice_hook() {

	set_transient( 'carbonfootprint_activated', 1, 5 );
}
register_activation_hook( __FILE__, 'carbonfootprint_admin_notice_hook' );

/**
 * Show admin notice as a guide for user right after plugin activation
 *
 * @since 1.0
 */
function carbonfootprint_admin_notice() {

	// Display notice if the transient is available
	if ( get_transient( 'carbonfootprint_activated' ) ) {

		$class = 'notice notice-info';

		// if this site is local show the notice that this plugin won't do a lot fof now
		if ( 'local' === wp_get_environment_type() ) {

			printf(
				'<div class="%1$s"><p>%2$s</p></div>',
				esc_attr( $class ),
				esc_html__( 'Sorry, it seems that your website is a local site, so it cannot be accessed to measure its carbon footprint. Try to activate the plugin on a site that can be accessed through the internet. ', 'carbon-footprint' )
			);

		} else {

			$site_health = admin_url( 'site-health.php' );

			printf(
				'<div class="%1$s"><p>%2$s</p><p><a href="%3$s">%4$s</a></p><p><i>%5$s</i></p></div>',
				esc_attr( $class ),
				esc_html__( 'Learn more about how sustainable your website is. Discover what kind of energy your server uses and how your site&rsquo;s carbon footprint compares to others. ', 'carbon-footprint' ),
				esc_url( $site_health ),
				esc_html__( 'Test your site using Site Health', 'carbon-footprint' ),
				esc_html__( 'Note that the test may take a while and make sure to look for passed tests if you do not see any new information after testing.', 'carbon-footprint' )
			);
		}

		// Delete transient to only display notice once
		delete_transient( 'carbonfootprint_activated' );
	}
}
add_action( 'admin_notices', 'carbonfootprint_admin_notice' );

/**
 * Clean up plugin data when plugin is deactivated.
 *
 * @since 1.0
 */
function carbonfootprint_clean_up() {

	// Delete transient with report data
	delete_transient( 'carbonfootprint_test' );
	// Delete options if saved
	delete_option( 'carbonfootprint_data' );
}
register_deactivation_hook( __FILE__, 'carbonfootprint_clean_up' );
