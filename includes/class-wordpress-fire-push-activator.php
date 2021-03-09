<?php

/**
 * Fired during plugin activation
 *
 * @link       http://plugins.db-dzine.com
 * @since      1.0.0
 *
 * @package    WordPress_Fire_Push
 * @subpackage WordPress_Fire_Push/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WordPress_Fire_Push
 * @subpackage WordPress_Fire_Push/includes
 * @author     Daniel Barenkamp <contact@db-dzine.de>
 */
class WordPress_Fire_Push_Activator {

    /**
     * On plugin activation -> Assign Caps
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @return  [type]                       [description]
     */
	public function activate() 
    {
        global $wpdb;

        if ( is_multisite() ) {

            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach ( $blog_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                $this->create_table();
                restore_current_blog();
            }
        } else {
            $this->create_table();
        }
	}

    public function delete_table()
    {
        global $wpdb;

        $db_name = $wpdb->prefix . 'fire_push_tokens';

        $wpdb->query( "DROP TABLE IF EXISTS " . $db_name );
    }

    public function create_table() {

        global $wpdb;

        $db_name = $wpdb->prefix . 'fire_push_tokens';
        if($wpdb->get_var("show tables like '$db_name'") != $db_name) 
        {
            $sql = "CREATE TABLE " . $db_name . " (
                `id` mediumint(9) NOT NULL AUTO_INCREMENT,
                `token` varchar(5000) COLLATE utf8_unicode_ci NULL,
                UNIQUE KEY id (id)
            );";
     
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        $db_name = $wpdb->prefix . 'fire_push_notifications';
        if($wpdb->get_var("show tables like '$db_name'") != $db_name) 
        {
            $sql = "CREATE TABLE " . $db_name . " (
                `id` mediumint(9) NOT NULL AUTO_INCREMENT,
                `multicast_id` varchar(255) COLLATE utf8_unicode_ci NULL,
                `fire_push_id` varchar(255) COLLATE utf8_unicode_ci NULL,
				`date` varchar(255) COLLATE utf8_unicode_ci NULL,
				`time` TIME COLLATE utf8_unicode_ci NULL,
				`type` TINYINT(1) COLLATE utf8_unicode_ci NULL,
				`status` TINYINT(1) COLLATE utf8_unicode_ci NULL, 
                `data` LONGTEXT COLLATE utf8_unicode_ci NULL,
                UNIQUE KEY id (id)
            );";
     
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}