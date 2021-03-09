<?php

class WordPress_Fire_Push_Admin extends WordPress_Fire_Push
{
    protected $plugin_name;
    protected $version;

    protected $options;

    /**
     * Construct Fire_Push Admin Class
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @param   string                         $plugin_name
     * @param   string                         $version
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Enqueue Admin Styles
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name.'-admin', plugin_dir_url(__FILE__).'css/wordpress-fire-push-admin.css', array(), $this->version, 'all');
    }
    
    /**
     * Enqueue Admin Scripts
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name.'-admin', plugin_dir_url(__FILE__).'js/wordpress-fire-push-admin.js', array('jquery'), $this->version, false);

        $forJS = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            // Configs
            'apiKey' => $this->get_option('apiKey'),
            'authDomain' => $this->get_option('authDomain'),
            'databaseURL' => $this->get_option('databaseURL'),
            'projectId' => $this->get_option('projectId'),
            'storageBucket' => $this->get_option('storageBucket'),
            'messagingSenderId' => $this->get_option('messagingSenderId'),
            // Welcome Notification
            'welcomeEnabled' => $this->get_option('welcomeEnabled'),
            'welcomeTitle' => $this->get_option('welcomeTitle'),
            'welcomeBody' => $this->get_option('welcomeBody'),
            'welcomeURL' => $this->get_option('welcomeURL'),
            // Blocked
            'deniedText' => __('Notifications are disabled by your browser. Please click on the info icon near your browser URL to adjust notification settings!', 'wordpress-fire-push'),

        );

        if(!isset($this->get_option('welcomeIcon')['url']) || empty($this->get_option('welcomeIcon')['url'])) {
            $forJS['welcomeIcon'] = $this->get_option('defaultIcon')['url'];
        } else {
            $forJS['welcomeIcon'] = $this->get_option('welcomeIcon')['url'];
        }

        wp_localize_script($this->plugin_name.'-public', 'fire_push_options', $forJS);
    }

    /**
     * Add admin JS vars
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function add_admin_js_vars()
    {
    ?>
    <script type='text/javascript'>
        var wordpress_fire_push_settings = <?php echo json_encode(array(
            'ajax_url' => admin_url('admin-ajax.php'),
        )); ?>
    </script>
    <?php
    }

    /**
     * Load Extensions
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function load_extensions()
    {
        // Load the theme/plugin options
        if (file_exists(plugin_dir_path(dirname(__FILE__)).'admin/options-init.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'admin/options-init.php';
        }
        return true;
    }

    /**
     * Init
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function init()
    {
        global $wordpress_fire_push_options;
        $this->options = $wordpress_fire_push_options;
    }
}