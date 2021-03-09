<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              https://fullstackmonks.com
 * @since             1.0.0
 * @package           Push Notification
 *
 * @wordpress-plugin
 * Plugin Name:       Push Notification
 * Plugin URI:        https://fullstackmonks.com
 * Description:       Web Push Notification Solution
 * Version:           1.0
 * Author:            Monks
 * Author URI:        https://fullstackmonks.com

 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wordpress-fire-push-activator.php
 */
function activate_WordPress_Fire_Push() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-fire-push-activator.php';
	$activator = new WordPress_Fire_Push_Activator();
	$activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wordpress-fire-push-deactivator.php
 */
function deactivate_WordPress_Fire_Push() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-fire-push-deactivator.php';
	WordPress_Fire_Push_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_WordPress_Fire_Push' );
register_deactivation_hook( __FILE__, 'deactivate_WordPress_Fire_Push' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-fire-push.php';

/**
 * Run the Plugin
 * @author Daniel Barenkamp
 * @version 1.0.0
 * @since   1.0.0
 * @link    http://plugins.db-dzine.com
 */
function run_WordPress_Fire_Push() {

	$plugin_data = get_plugin_data( __FILE__ );
	$version = $plugin_data['Version'];

	$plugin = new WordPress_Fire_Push($version);
	$plugin->run();

}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active('redux-framework/redux-framework.php') || is_plugin_active('redux-dev-master/redux-framework.php')){
	run_WordPress_Fire_Push();
} else {
	add_action( 'admin_notices', 'run_WordPress_Fire_Push_Not_Installed' );
}


/*
if(isset($_GET['fire_push_id']) && isset($_GET['surl'])){

	
	$surl = $_GET['surl'];	
	header('Location: '.$surl.'');
	exit;
}

*/


if(isset($_GET['fire_push_id']) || !empty($_GET['fire_push_id']) &&  isset($_GET['surl'])) {
           

			global $wpdb;
			$table_name = $wpdb->prefix . "fire_push_notifications";

			$fire_push_id = $_GET['fire_push_id'];

			$notification = 
			$wpdb->get_results( 
				$wpdb->prepare( "SELECT * FROM $table_name WHERE fire_push_id = '%s'",  esc_sql( $fire_push_id) ) 
			);
		
			$notification = $notification[0];
			$notification_data = unserialize($notification->data);

			if(isset($notification_data['clicked'])) {
				$notification_data['clicked'] = $notification_data['clicked'] + 1;
			} else {
				$notification_data['clicked'] = 1;
			}

			$notification_data = serialize($notification_data);

			$asd = $wpdb->query(
				$wpdb->prepare("UPDATE $table_name SET data = '%s' WHERE fire_push_id = '%s'",
					$notification_data,
					$fire_push_id
				)
			); 

			$surl = $_GET['surl'];	
			header('Location: '.$surl.'');
			exit;
			
}




function run_WordPress_Fire_Push_Not_Installed()
{
	?>
    <div class="error">
      <p><?php _e( 'WordPress Fire_Push requires the Redux Framework Please install or activate it before!', 'wordpress-fire-push'); ?></p>
    </div>
    <?php
}