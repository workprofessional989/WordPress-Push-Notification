<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://plugins.db-dzine.com
 * @since      1.0.0
 */

class WordPress_Fire_Push
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     *
     * @var WordPress_Fire_Push_Loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     *
     * @var string The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct($version)
    {
        $this->plugin_name = 'wordpress-fire-push';
        $this->version = $version;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - WordPress_Fire_Push_Loader. Orchestrates the hooks of the plugin.
     * - WordPress_Fire_Push_i18n. Defines internationalization functionality.
     * - WordPress_Fire_Push_Admin. Defines all hooks for the admin area.
     * - WordPress_Fire_Push_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-wordpress-fire-push-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-wordpress-fire-push-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-fire-push-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-fire-push-notifications.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-fire-push-sender.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-fire-push-subscribers.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-fire-push-token-manager.php';
        
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'public/class-wordpress-fire-push-public.php';

        // Load Vendors
        if (file_exists(plugin_dir_path(dirname(__FILE__)).'vendor/autoload.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'vendor/autoload.php';
        }

        $this->loader = new WordPress_Fire_Push_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WordPress_Fire_Push_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function set_locale()
    {
        $plugin_i18n = new WordPress_Fire_Push_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_admin_hooks()
    {
        // Admin Interface
        $this->admin = new WordPress_Fire_Push_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->admin, 'init', 10);
        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_styles', 999);
        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_scripts', 999);
        $this->loader->add_action('admin_head', $this->admin, 'add_admin_js_vars', 10);
        $this->loader->add_action('plugins_loaded', $this->admin, 'load_extensions');

        $this->token_manager = new WordPress_Fire_Push_Token_Manager($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->token_manager, 'init', 10);
        // Update Token
        $this->loader->add_action('wp_ajax_nopriv_update_token', $this->token_manager, 'updateToken');
        $this->loader->add_action('wp_ajax_update_token', $this->token_manager, 'updateToken');        

        // Migrate tokens to table
        if (isset($_GET['migrate-tokens'])) {
            $this->loader->add_action('init', $this->token_manager, 'migrateGuestTokensToTable', 10);
        }

        // Delete duplicate tokens
        if (isset($_GET['delete-duplicate-tokens'])) {
            $this->loader->add_action('init', $this->token_manager, 'deleteDuplicateGuestTokens', 10);
        }

        $this->sender = new WordPress_Fire_Push_Sender($this->get_plugin_name(), $this->get_version(), $this->token_manager);
        $this->loader->add_action('init', $this->sender, 'init', 20);
        if (isset($_GET['send-custom-notification'])) {
            $this->loader->add_action('init', $this->sender, 'sendCustomNotification', 140);
        }

        $this->subscribers = new WordPress_Fire_Push_Subscribers($this->get_plugin_name(), $this->get_version(), $this->token_manager);
        $this->loader->add_action('admin_menu', $this->subscribers, 'add_menu', 11);

        $this->notifications = new WordPress_Fire_Push_Notifications($this->get_plugin_name(), $this->get_version(), $this->token_manager);
        $this->loader->add_action('admin_menu', $this->notifications, 'add_menu', 15);

        // Migrate notifications to table
        if (isset($_GET['migrate-notifications'])) {
            $this->loader->add_action('init', $this->notifications, 'migrateNotificationsToTable', 10);
        }

        $this->loader->add_action('wp_ajax_nopriv_update_notification_clicked', $this->notifications, 'updateClicked');
        $this->loader->add_action('wp_ajax_update_notification_clicked', $this->notifications, 'updateClicked');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_public_hooks()
    {
        $this->public = new WordPress_Fire_Push_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $this->public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $this->public, 'enqueue_scripts');
        $this->loader->add_action('admin_enqueue_scripts', $this->public, 'enqueue_scripts', 999);
        $this->loader->add_action('admin_enqueue_scripts', $this->public, 'enqueue_styles', 999);

        $this->loader->add_action('init', $this->public, 'init');
        $this->loader->add_action('wp_footer', $this->public, 'add_popup', 10);
        $this->loader->add_action('admin_footer', $this->public, 'add_popup', 10);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     *
     * @return string The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     *
     * @return WordPress_Fire_Push_Loader Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     *
     * @return string The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * Get Options
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @param   mixed                         $option The option key
     * @return  mixed                                 The option value
     */
    protected function get_option($option)
    {
        if(!isset($this->options)) {
            return false;
        }

        if (!is_array($this->options)) {
            return false;
        }

        if (!array_key_exists($option, $this->options)) {
            return false;
        }

        return $this->options[$option];
    }
}
