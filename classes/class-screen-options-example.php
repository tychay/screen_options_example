<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Screen Options Examples Plugin class
 *
 * @package WordPress
 * @subpackage screen_options_example
 * @author tychay
 * @since 1.0.0
 */
class screen_options_example {

	/**
	 * Construct
	 * 
	 * @param string $file
	 */
	public function __construct( $file ) {
		$this->name = 'Screen Options Examples';
		$this->token = 'screen-options-example';

		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init the extension settings
	 * 
	 * @return void
	 */
	public function init() {
		$tabs = array(
			'screen-options-example' => 'screen_options_example_Settings'
		);

		foreach( $tabs as $key => $obj ) {
			if( !class_exists( $obj ) )
				continue;
			$this->settings_objs[ $key ] = new $obj;
			$this->settings[ $key ] = $this->settings_objs[ $key ]->get_settings();
			add_action( 'admin_init', array( $this->settings_objs[ $key ], 'setup_settings' ) );
		}

		$this->settings_screen = new screen_options_example_Settings_Screen( array(
			'default_tab' => 'screen-options-example'
		));
	}
}