<?php
/*
Plugin Name: Help and Screen Options Example
Plugin URI: https://terrychay.com/article/wpadmin-help-and-screen-options.shtml
Description: WordPress Plugin to demonstrate help & screen options tabs
Version: 1.0
Author: terry chay
Author URI: http://terrychay.com/
License: GPL v2.0 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Class/namespace for the Help & Screen Options Examples
 */
class scroptex
{
	/**
	 * @const string codepage for this plugin
	 */
	const SLUG='scroptex';
	/**
	 * This bootstraps the plugin and should be called on plugins_loaded
	 * 
	 * @return void
	 */
	static public function bootstrap() {
		$scroptex = new scroptex();
		// could save to property to make it an instance method
		// could trigger a hook here too
	}
	/**
	 * Does all the initialization.
	 * 
	 * Note that this plugin only operates on the admin page.
	 */
	public function __construct() {
		if ( is_admin() ) {
			// Stuff to do after initialization
			//add_action( 'admin_init', array( $this, 'init') );
			// Add Settings & ? pages to admin menu
			add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );		
		}
	}
	/**
	 * Add the admin menu page to wp-admin
	 *
	 * @return  void 
	 */
	public function create_admin_menu()
	{
		$this->_options_suffix = add_options_page(
			__( 'Help/Screen Options Examples', self::SLUG ), // page title
			__( 'Help/Screen Options Examples', self::SLUG ),             // menu title
			'manage_options',                                 // capability needed
			self::SLUG.'-settings',                           // menu slug
			array( $this, 'show_settings_page' )              // function that outputs content
		);
		if ( $this->_options_suffix ) {
			add_action( 'load-'.$this->_options_suffix, array($this, 'loading_settings_page') );
		}
	}
	/**
	 * Do work on settings page before rendering
	 *
	 * 1. Add Settings contextual help tabs
	 * 2. Add Settings contextual help sidebar
	 * 
	 * @return  void 
	 */
	public function loading_settings_page() {
		// 1. Add Settings contextual help tabs
		//add_filter('contextual_help', array($this,'filter_settings_help'), 10, 3); // old style
		$screen = get_current_screen();
		$screen->remove_help_tabs();
		$screen->add_help_tab( array(
			'title'    => __( 'Overview' ),            // Title for the tab
			'id'       => self::SLUG.'-help-overview', // HTML-safe Tab ID
			//'content'  => '',
			'callback' => array($this,'show_settings_help_overview') // function that outputs tab
		) );
		$screen->add_help_tab( array(
			'title'    => __('Another Tab', self::SLUG),
			'id'       => self::SLUG.'-help-anothertab', 
			'content'  => '<p>Or you could put content on another screen this way and use another tab to explain the hidden screen options features.</p>',
			//'callback' => array($this,'show_settings_help_flickrauth')
		) );

		// 2. Add Settings contextual help sidebar
		$screen->set_help_sidebar( $this->_get_settings_help_sidebar() );
	}
	/**
	 * Output settings page
	 *
	 * @return  void
	 */
	public function show_settings_page() {
?>
<div class="wrap">
	<h2><?php esc_html_e('Dummy Options', self::SLUG) ?></h2>
</div>
<?php
	}
	/**
	 * Output the default Help tab
	 *
	 * @return  void
	 */
	public function show_settings_help_overview() {
?>
<p><?php _e( 'This is the default help screen which you use to provide an overview of functionality.', self::SLUG); ?></p>
<?php
	}

	/**
	 * Returns the content of the contextual help sidebar
	 * 
	 * @return string the content of the sidebar
	 */
	private function _get_settings_help_sidebar() {
		ob_start();
?>
<p><strong><?php _e( 'For more information:' ); ?></strong></p>
<p><a href="https://terrychay.com/article/wpadmin-help-and-screen-options.shtml" target="_blank"><?php _e( 'Blog post', self::SLUG ) ?></a></p>
<p><a href="https://github.com/tychay/screen_options_example" target="_blank"><?php _e( 'Github', self::SLUG ) ?></a></p>
<?php
		return ob_get_clean();
	}
}

add_action('plugins_loaded', array( 'scroptex', 'bootstrap' ) );