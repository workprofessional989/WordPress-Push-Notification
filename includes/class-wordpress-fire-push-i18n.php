<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://plugins.db-dzine.com
 * @since      1.0.0
 *
 * @package    WordPress_Fire_Push
 * @subpackage WordPress_Fire_Push/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WordPress_Fire_Push
 * @subpackage WordPress_Fire_Push/includes
 * @author     Daniel Barenkamp <contact@db-dzine.de>
 */
class WordPress_Fire_Push_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$loaded = load_plugin_textdomain(
			'wordpress-fire-push',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
