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
	 * Names for option parameters
	 *
	 * We have to create and store this because WordPress in its infinite
	 * wisdom calls set_screen_options() before nearly everything including
	 * the screen.
	 * 
	 * @var array
	 */
	private $_option_names = array();

	//
	// INITALIZATIONS
	// 
	/**
	 * Does Initialization of variables and the like
	 */
	public function __construct() {
		$this->_option_names = array(
			'per_page' => str_replace('-','_',self::SLUG).'_per_page',
		);
	}
	/**
	 * Note that this plugin only operates on the admin page.
	 */
	//public function run() {}
	/**
	 * Register actions on Admin pages (that can't be registered later)
	 *
	 * 1. Add settings page to admin menu
	 * 2. Add filter to process screen option on settings page
	 * 
	 * @return void
	 */
	/**
	 * This bootstraps the plugin and should be called on plugins_loaded
	 * 
	 * @return void
	 */
	static public function bootstrap() {
		$scroptex = new scroptex();
		// could save to property to make it an instance method
		// bury hooks into run() and run_admin(), we can trigger hooks in
		// those or not
		if ( is_admin() ) {
			$scroptex->run_admin();
		}
	}
	public function run_admin() {
		// Stuff to do after initialization
		//add_action( 'admin_init', array( $this, 'admin_init') );
		// 1. Add settings page to admin menu
		add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );

		// 2. Add filter to process screen option (note, we need all 3 parameters)
		// Note: this filter is called after init and wp-loaded but BEFORE admin_init and load-*
		add_filter( 'set-screen-option', array( $this, 'filter_screen_option'), 10, 3 );
	}
	//
	// PROCESSING ACTIONS
	// 
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
	 * Process screen options for the things we know
	 *
	 * This is called before get_current_screen() is set.
	 * 
	 * @param  mixed  $status current filter return state
	 * @param  string $option name of the option being processed
	 * @param  mixed  $value  value to set it to
	 * @return mixed          return state with this pages options processed
	 */
	public function filter_screen_option( $status, $option, $value ) {
		switch ( $option ) {
			case $this->_option_names['per_page']:
				$value = (int) $value;
				if ($value < 1) { $value = 10; } //the default
				return $value;
			default:
				return $status;
		}
	}
	/**
	 * Run on init
	 * @return [type] [description]
	 */
	public function admin_init() {
	}
	/**
	 * Do work on Settings page before rendering
	 *
	 * 1. Add contextual help tabs
	 * 2. Add contextual help sidebar
	 * 3. Add per_page screen option
	 * 
	 * @return  void 
	 */
	public function loading_settings_page() {
		// 1. Add contextual help tabs
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

		// 2. Add contextual help sidebar
		$screen->set_help_sidebar( $this->_get_settings_help_sidebar() );

		// 3. Add per_page screen option
		add_screen_option(
			'per_page', // built-in type
			array(
				'label' => __( 'Counts', self::SLUG ),         // Label to use in screen_options
				'default' => 10,                               // default # when empty
				'option'  => $this->_option_names['per_page'], // db option name
			)
		);

	}

	//
	// OUTPUTS
	// 
	/**
	 * Output settings page
	 *
	 * Outputs the following
	 * 1. Title
	 * 2. A Count from 1 to the number of "per_page" options
	 * @return  void
	 */
	public function show_settings_page() {
		//$screen = get_current_screen(); var_dump( $screen->get_option( 'per_page', 'option') ); die;
		$screen = get_current_screen();
		$option_name = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta( get_current_user_id(), $option_name, true );
		if ( empty($per_page) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}
?>
<div class="wrap">
	<h2><?php esc_html_e('Dummy Options', self::SLUG) ?></h2>
		<table class="form-table">
			<!-- begin api screen options stuff -->
			<tr class>
				<th scope="row"><?php _e('Count von Count', self::SLUG); ?></th>
				<td><?php
			for ($i=0; $i<$per_page; ++$i) {
				echo $i+1 . '<br />';
			}
?></td>
			</tr>
		</table>
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