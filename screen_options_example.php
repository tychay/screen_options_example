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
	 * @const string version of plugin
	 */
	const VERSION='1.0';
	/**
	 * Option information
	 *
	 * This is an array of hashes indexed by the option type. (The only reserved
	 * type is "per_page").
	 *
	 * It has the following params:
	 * - name: the option key name (we have to store this because WordPress in
	 *         its infinite wisdom calls set_screen_options() before nearly
	 *         everything, but especially before screen is set up.
	 * - default: the default value
	 * - display: the display name
	 * 
	 * @var array
	 */
	private $_option_info = array(
		'per_page' => array(
			'name'    => 'scroptex_per_page',
			'default' => 3,
		),
		'bgcolor'  => array(
			'name'    => 'scroptex_bgcolor',
			'default' => 'transparent',
		),
	);
	/**
	 * Rendered names of the options
	 *
	 * This is separate from the checkboxes hidden column because of the
	 * array_merge() needed to generate filter.
	 * 
	 * @var array
	 */
	private $_opt_display_names = array();
	/**
	 * Hidden checkboxes stored as hidden columns.
	 *
	 * The key is the id of column, the value is the display name
	 * 
	 * @var array
	 */
	private $_option_checkboxes = array();

	//
	// INITALIZATIONS
	// 
	/**
	 * Does Initialization of variables and the like
	 */
	public function __construct() {
		$this->_option_info['per_page']['display'] = __( 'Counts', self::SLUG );
		$this->_option_info['bgcolor']['display']  = __( 'Background Color', self::SLUG );

		$this->_option_checkboxes = array(
			'arabic_counting' => __( 'Count from zero', self::SLUG ),
			'eight_the_great' => __( 'Sing <i>Eight the Great</i>', self::SLUG ),
		);
	}
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
	public function run_admin() {
		// 1. Add settings page to admin menu
		add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );

		// 2. Add filter to process screen option (note, we need all 3 parameters)
		// Note: this filter is called after init and wp-loaded but BEFORE admin_init and load-*
		add_filter( 'set-screen-option', array( $this, 'filter_screen_option'), 10, 3 );

		// 3. Stuff to do after initialization
		add_action( 'admin_init', array( $this, 'admin_init') );
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
			__( 'Help & Screen Options Example', self::SLUG ),// menu title
			'manage_options',                                 // capability needed
			self::SLUG,                                       // menu slug (screen will be settings_page_scroptex)
			array( $this, 'show_settings_page' )              // function that outputs content
		);
		if ( $this->_options_suffix ) {
			add_action( 'load-'.$this->_options_suffix, array($this, 'loading_settings_page') );
		}
	}
	/**
	 * Process screen options for the things we know (that aren't automatically handled).
	 *
	 * FYWP: This is called before get_current_screen() is set.
	 * {@see https://core.trac.wordpress.org/ticket/24786#comment:8}
	 * 
	 * @param  mixed  $status current filter return state
	 * @param  string $option name of the option being processed
	 * @param  mixed  $value  value to set it to
	 * @return mixed          return state with this pages options processed
	 */
	public function filter_screen_option( $status, $option, $value ) {
		switch ( $option ) {
			case $this->_option_info['per_page']['name']:
				$value = (int) $value;
				if ($value < 1) { $value = $this->_option_info['per_page']['default']; } //the default
				return $value;
			case $this->_option_info['bgcolor']['name']:
				// add any css color validation here
				return $value;
			default:
				return $status;
		}
	}
	/**
	 * Run on init
	 *
	 * This loads the ajax action for handling the custom screen option
	 * 
	 * @return void 
	 */
	public function admin_init() {
		// Add ajax server for handling ajax screen options
		add_action( 'wp_ajax_scroptex_set_value', array($this, 'handle_ajax_screen_option_set') );
	}
	/**
	 * Handle the ajax call for setting custom screen options through the
	 * ajax API.
	 *
	 * FYWP: Note that we could just piggy back off of set_screen_options() but
	 * that code was hard-coded to do a redirect which isn't the recommended way
	 * of handling ajax calls. The developers should have made it a default
	 * parameter with the option of returning a boolean if it is set.
	 * {@see https://developer.wordpress.org/reference/functions/set_screen_options/}
	 * 
	 * @return void  Should never return, should die
	 */
	public function handle_ajax_screen_option_set() {
		// we are pirating the nonce created in screen for screen options :-)
		check_ajax_referer('screen-options-nonce','screenoptionnonce');
		if ( isset($_POST['wp_screen_options']) && is_array($_POST['wp_screen_options']) ) {
 			$option = $_POST['wp_screen_options']['option'];
	        $value = $_POST['wp_screen_options']['value'];
	        // validate it's a scren option
	        $value = apply_filters( 'set-screen-option', false, $option, $value );

	        if ( false === $value ) {
	        	wp_send_json(array(
	        		'stat' => 'fail',
	        		'code' => -100,
	        		'msg'  => 'Unsupported option.',
	        	));
	        	// will die
	        }

	        if ( !$user = wp_get_current_user() ) {
	        	wp_send_json(array(
	        		'stat' => 'fail',
	        		'code' => -101,
	        		'msg'  => 'Unknown user',
	        	));
	        	// will die
	        }
	        // FYWP: inconsistent behavior between user_meta and user_option
	        // in set_screen_option
	        update_user_meta( $user->ID, $option, $value);
	        wp_send_json(array(
	        	'stat' => 'ok',
	        	'action' => array( 'option'=>$option, 'value'=>$value ),
	        ));
		}
		wp_send_json(array(
			'stat' => 'fail',
			'code' => -102,
			'msg'  => 'Missing or improperly formatted wp_screen_options.',
		));
	}
	/**
	 * Do work on Settings page before rendering
	 *
	 * 1. Add contextual help tabs
	 * 2. Add contextual help sidebar
	 * 3. Add per_page screen option
	 * 4. Add metaboxes
	 * 5. Set defaults for metaboxes
	 * 6. Trigger adding of metaboxes
	 * 7. Queue javascript for metabox handling (just in case)
	 * 8. Add hidden columns
	 * 9. Add filter to add arbitrary screen options
	 * 10. Add javascript handling of screen options
	 * 11. Add stylesheet for page
	 * 
	 * @return  void 
	 */
	public function loading_settings_page() {
		$screen    = get_current_screen();
		$screen_id = $screen->id; //needed for a lot of stuff, should be settings_page_scroptex

		// 1. Add contextual help tabs
		//add_filter('contextual_help', array($this,'filter_settings_help'), 10, 3); // old style
		$screen->remove_help_tabs();
		$screen->add_help_tab( array(
			'title'    => __( 'Overview' ),            // Title for the tab
			'id'       => self::SLUG.'-help-overview', // HTML-safe Tab ID
			'callback' => array($this,'show_settings_help_overview') // function that outputs tab
		) );
		$screen->add_help_tab( array(
			'title'    => __('Another Tab', self::SLUG),
			'id'       => self::SLUG.'-help-anothertab', 
			'content'  => $this->_get_settings_help_tab(), // content to inject
		) );

		// 2. Add contextual help sidebar
		$screen->set_help_sidebar( $this->_get_settings_help_sidebar() );

		// 3. Add reserved "per_page" screen option
		$optinfo = $this->_option_info['per_page'];
		$screen->add_option(
			'per_page', // built-in type
			array(
				'label'   => $optinfo['display'], // Label to use in screen_options
				'default' => $optinfo['default'], // default # when empty
				'option'  => $optinfo['name'],    // db option name
			)
		);

		// 4. Add metaboxes
		add_action( 'add_meta_boxes_'.$screen_id, array( $this, 'add_settings_metas' ) );

		// 5. Set defaults for metaboxes
		add_filter( 'default_hidden_meta_boxes', array( $this, 'filter_default_hidden_meta_boxes' ) );

		// 6. Trigger adding of metaboxes
		do_action( 'add_meta_boxes_'.$screen_id, null );
	    do_action( 'add_meta_boxes', $screen_id, null );

		// 7. Queue javascript for metabox handling (just in case)
		wp_enqueue_script('postbox');

		// 8. Add hidden columns
		add_filter( 'manage_'.$screen->id.'_columns', array($this, 'filter_hidden_columns') );

		// FYWP: There is no column defaults witout modifying everyone's
		// user_meta. There _IS_ defaults for meta boxes because of a filter.
		// Why didn't they modify get_hidden_columns() with the $use_defaults
		// code and a default_hidden_columns filter? I have no idea. Clearly
		// core devs are on crack.
		// see: https://wordpress.org/support/topic/default-custom-post-column-to-off-in-screen-options
		// see: https://core.trac.wordpress.org/ticket/31989
		// If the above patch is applied, then the next line sets all the
		// hidden columns to default off :-)
		add_filter( 'default_hidden_columns', array( $this, 'filter_hidden_columns' ) );
		
		// FYWP: column handling code is in admin common.js, while metabox
		// handling code is in postbox. Why?
		
		// 9. add filter to add arbitrary screen options
		// // see: https://developer.wordpress.org/reference/hooks/screen_settings/
		add_filter( 'screen_settings', array( $this, 'filter_settings_screen_options' ), 10, 2 );	
		// we could $screen->add_option() here but it gives us nothing.

		// 10. Add javascript handling for screen options
		wp_enqueue_script(
			self::SLUG.'-screen-options-script',             // handle
			plugin_dir_url( __FILE__ ).'/admin-settings.js', // src
			array('jquery'),                                 // dependencies: ajax
			self::VERSION,                                   // version
			true                                             // ok in footer
		);

		// 11. Add stylesheet for page
		wp_enqueue_style(
			self::SLUG.'-screen-options-style',               // handle
			plugin_dir_url( __FILE__ ).'/admin-settings.css', // src
			array(),                                          // no dependencies
			self::VERSION                                     // version
			                                                  // media
		);
	}
	/**
	 * It's good form to hook metaboxes to one of the add_meta_boxes triggers
	 */
	public function add_settings_metas() {
		$screen = get_current_screen();
		// see: https://codex.wordpress.org/Plugin_API/Action_Reference/add_meta_boxes
		add_meta_box(
			self::SLUG.'-portrait',                        // HTML id
			__( 'Count von Count', self::SLUG ),                  // title of edit screen
			array( $this, 'show_settings_meta_portrait' ), // renderer callback,
			$screen->id,                                   // register to this page only
			'side'                                         // where
		);
	}
	/**
	 * Set the defaults for which boxes are hidden
	 * @param  array $hidden  array of metaboxes currently hidden
	 * @return array          array of metaboxes to hide by default
	 */
	public function filter_default_hidden_meta_boxes( $hidden ) {
		// you can add to the array
		return $hidden;
	}
	/**
	 * Inject in hidden columns for this page
	 * 
	 * @param  array  $columns The currrent state of the filter
	 * @return array           The filter with our hidden columns added
	 */
	function filter_hidden_columns( $columns ) {
		return array_merge( $columns, $this->_option_checkboxes );
	}

	//
	// OUTPUTS
	// 
	/**
	 * Output settings page
	 *
	 * 1. Title
	 * 2. A Count from 1 to the number of "per_page" options
	 * 3. Sidebar with metaboxes
	 * 4. Hidden columns table
	 * 
	 * @return  void
	 */
	public function show_settings_page() {
		$screen = get_current_screen();
		$option_name = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_option( $option_name ); // WPMU PITFALL: get_user_option() is per blog while get_user_meta() is global
		if ( empty($per_page) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}
		$bgcolor = get_user_option( $this->_option_info['bgcolor']['name'] );
		if ( empty($bgcolor) ) {
			$bgcolor = $this->_option_info['bgcolor']['default'];
		}
?>
<div class="wrap">
	<h2><?php esc_html_e('Dummy Options', self::SLUG) ?></h2>
	<form name="dummy_form" method="post">
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content" style="background-color:<?php echo $bgcolor; ?>;"><?php
		printf(
			'<span id="display_arabic_counting" style="%s">%s</span>',
			( $this->_column_is_hidden( 'arabic_counting') ) ? 'display:none;' : '',
			$this->_get_bubble('0')
		);
		for ($i=1; $i<=$per_page; ++$i) {
			if ( $i == 8 ) {
				echo $this->_get_bubble( $i );
				echo $this->_get_bubble(
		             __( '&#9835;You can hold it this way, you can hold it that way, it is still eight!&#9836;', self::SLUG ),
					'display_eight_the_great',
					$this->_column_is_hidden( 'eight_the_great')
				);
			} else {
				echo $this->_get_bubble( $i );
			}
		}
		if ( $per_page < 8 ) {
			echo $this->_get_bubble(
                 __( 'The Count is sad he can’t count to eight &#x1f622;', self::SLUG ),
				'display_eight_the_great',
				$this->_column_is_hidden( 'eight_the_great')
			);
		}
?>
				</div>
				<div id="postbox-container-1" class="postbox-container"><?php
		do_meta_boxes( $screen->id, 'side', null );
?>
				</div>
			</div>
		</div>
		<table class="hidden_column_table">
			<tr>
<?php
		foreach( $this->_option_checkboxes as $key=>$value ) {
			printf('<th scope="col" id="%1$s" class="manage-column column-%1$s" style="%2$s"></th>',
				$key,
				( $this->_column_is_hidden( $key ) ) ? 'display: none;' : ''
			);
		}
?>
			</tr>
		</table>
	</form>
</div>
<br clear="both" />
<?php
		/* //debugging
		var_dump(array(
			'screen_id' => $screen->id,
			'manage_$SID_columnshidden' => get_user_option( 'managesettings_page_scroptexcolumnshidden'),
			'per_page' => $screen->get_option( 'per_page', 'option' ),
			'per_page_option' => get_user_option( 'scroptex_per_page' ),
			'bgcolor_option' => get_user_option( 'scroptex_bgcolor' ),
			'closed_postboxes' => get_user_option( 'closedpostboxes_settings_page_scroptex'),
			'metabox_hidden' => get_user_option( 'metaboxhidden_settings_page_scroptex'),
			//'screen' => $screen,
		));
		/* */
	}
	/**
	 * Make html for iOS speech bubble
	 * @param  string  $message HTML for inside bubble
	 * @param  string  $id      id for bubble
	 * @param  boolean $hidden  should bubble start as hidden?
	 * @return string            HTML for speech bubble
	 */
	private function _get_bubble( $message, $id='', $hidden=false ) {
		return sprintf(
			'<div class="bubble"%s%s>%s</div>',
			( empty($id) ) ? '' : sprintf( ' id="%s"', $id ),
			( $hidden ) ? ' style="display:none;"' : '',
			$message
		);
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
	private function _get_settings_help_tab() {
		return '<p>' . __( 'Or you could put content on another screen this way and use another tab to explain the hidden screen options features.', self::SLUG ) . '</p>';
	}
	/**
	 * Adds custom screen options to page
	 * 
	 * @param  string    $screen_settings The current state of the return value
	 * @param  WP_SCREEN $screen the screen object that triggered this
	 * @return string    form element html with the custom screen options tacked on
	 */
	public function filter_settings_screen_options($screen_settings, $screen) {
		$opt_info = $this->_option_info['bgcolor'];
		$opt_value = get_user_option( $opt_info['name']);
		if ( $opt_value === false ) {
			$opt_value = $opt_info['default'];
		}
		$screen_settings .= sprintf(
			'<label for="%1$s">%2$s</label> <input class="screen-bgcolor" name="%1$s" id="%1$s" value="%3$s" />',
			$opt_info['name'],
			$opt_info['display'],
			$opt_value
		);
		return $screen_settings;
	}
	/** * Output the metabox for portraits
	 * 
	 * @return void
	 */
	public function show_settings_meta_portrait() {
?>
<a href="http://en.wikipedia.org/wiki/Count_von_Count"><img src="http://upload.wikimedia.org/wikipedia/en/2/29/Count_von_Count_kneeling.png" alt="Count von Count kneeling" /></a>
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
	//
	// UTILITY FUNCTIONS
	//
	/**
	 * Returns true if the column name is hidden
	 * @param  string $column_name name of column
	 * @return boolean             the hidden status of column (opposite of checked status)
	 */
	private function _column_is_hidden( $column_name ) {
		$screen = get_current_screen();
		return ( in_array( $column_name, get_hidden_columns ( $screen ) ) );
	}
}

add_action('plugins_loaded', array( 'scroptex', 'bootstrap' ) );