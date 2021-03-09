<?php

class WordPress_Fire_Push_Public
{
    private $plugin_name;
    private $version;
    private $options;

    /**
     * Store Locator Plugin Construct
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
     * Enqueue Styles
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function enqueue_styles()
    {
        global $wordpress_fire_push_options;

        $this->options = $wordpress_fire_push_options;

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__).'css/wordpress-fire-push-public.css', array(), $this->version, 'all');

        return true;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function enqueue_scripts()
    {
        global $wordpress_fire_push_options;

        $this->options = $wordpress_fire_push_options;

        wp_enqueue_script('firebase-app', 'https://www.gstatic.com/firebasejs/5.3.0/firebase-app.js', array(), '5.3.0', true);
        wp_enqueue_script('firebase-messaging', 'https://www.gstatic.com/firebasejs/5.3.0/firebase-messaging.js', array(), '5.3.0', true);     
        wp_enqueue_script($this->plugin_name.'-public', plugin_dir_url(__FILE__).'js/wordpress-fire-push-public.js', array('jquery', 'firebase-app', 'firebase-messaging'), $this->version, true);
        
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

            'wpContentURL' => content_url(),
            // Blocked
            'deniedText' => __('Notifications are disabled by your browser. Please click on the info icon near your browser URL to adjust notification settings!', 'wordpress-fire-push'),

        );

        if(!isset($this->get_option('welcomeIcon')['url']) || empty($this->get_option('welcomeIcon')['url'])) {
            $forJS['welcomeIcon'] = $this->get_option('defaultIcon')['url'];
        } else {
            $forJS['welcomeIcon'] = $this->get_option('welcomeIcon')['url'];
        }

        wp_localize_script($this->plugin_name.'-public', 'fire_push_options', $forJS);

        return true;
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
    private function get_option($option)
    {
        if (!is_array($this->options)) {
            return false;
        }

        if (!array_key_exists($option, $this->options)) {
            return false;
        }

        return $this->options[$option];
    }

    /**
     * Init the Public
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

        if (!$this->get_option('enable')) {
            return false;
        }

        return true;
    }

    public function add_manifest()
    {
        echo '<link rel="manifest" href="/manifest.json">';
    }

    public function add_popup()
    {
        $popupEnable = $this->get_option('popupEnable');
        if(!$popupEnable) {
            return false;
        }

        $popupText = $this->get_option('popupText');
        $popupTextAgree = $this->get_option('popupTextAgree');
        $popupTextDecline = $this->get_option('popupTextDecline');
        $popupTitle = $this->get_option('popupTitle');
        $popupImage = $this->get_option('popupImage');


        $popupStyle = $this->get_option('popupStyle');
        $popupPosition = $this->get_option('popupPosition');
        $popupBackgroundColor = $this->get_option('popupBackgroundColor');
        $popupTextColor = $this->get_option('popupTextColor');
        $popupAgreeColor = $this->get_option('popupAgreeColor');
        $popupAgreeBackgroundColor = $this->get_option('popupAgreeBackgroundColor');
        $popupDeclineColor = $this->get_option('popupDeclineColor');
        $popupDeclineBackgroundColor = $this->get_option('popupDeclineBackgroundColor');
        $popupLinkColor = $this->get_option('popupLinkColor');

        $popupCloseIcon = $this->get_option('popupCloseIcon');
        $popupCloseIconColor = $this->get_option('popupCloseIconColor');
        $popupCloseIconBackgroundColor = $this->get_option('popupCloseIconBackgroundColor');

        $renderd = false;
        ?>
        <div class="wordpress-fire-push-popup <?php echo $popupStyle . ' ' . $popupPosition ?>" 
            style="background-color: <?php echo $popupBackgroundColor ?>; color: <?php echo $popupTextColor ?>;box-shadow: 0 10px 16px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19) !important;">
			
            <div class="wordpress-fire-push-popup-container" style="padding: 20px 5px;">
			<div style="max-width: 100px;float: left;"><img src="<?php print_r($popupImage['url']); ?>" /></div>
                <a href="#" id="wordpress-fire-push-popup-close" class="wordpress-fire-push-popup-close" style="background-color: <?php echo $popupCloseIconBackgroundColor ?>;">
                    <i style="color: <?php echo $popupCloseIconColor ?>;" class="<?php echo $popupCloseIcon ?>">X</i>
                </a>
                <div class="wordpress-fire-push-popup-text"><p style="font-weight:600;font-size:18px;width:100%;"><?php echo $popupTitle ?></p><?php echo wpautop($popupText) ?></div>
                <div class="wordpress-fire-push-popup-actions">
                    <div class="wordpress-fire-push-popup-actions-buttons">
                        <?php if(!empty($popupTextAgree)) { ?>
                            <a href="#" class="wordpress-fire-push-popup-agree" style="background-color: <?php echo $popupAgreeBackgroundColor ?>; color: <?php echo $popupAgreeColor ?>;border-radius: 10px;"><?php echo $popupTextAgree ?></a>
                        <?php } ?>
                    
                        <?php if(!empty($popupTextDecline)) { ?>
                            <a href="#" class="wordpress-fire-push-popup-decline" style="background-color: <?php echo $popupDeclineBackgroundColor ?>; color: <?php echo $popupDeclineColor ?>;border-radius: 10px;"><?php echo $popupTextDecline ?></a>
                        <?php } ?>
                        <div class="fire-push-clear"></div>
                    </div>
                    <div class="wordpress-fire-push-popup-actions-links">
                        <?php if(!empty($popupTextPrivacyCenter) && !empty($privacyCenterPage)) { ?>
                            <a href="<?php echo get_permalink($privacyCenterPage) ?>" class="wordpress-fire-push-popup-privacy-center" style="color: <?php echo $popupLinkColor ?>;"><?php echo $popupTextPrivacyCenter ?></a>
                        <?php } ?>

                        <?php if(!empty($popupTextPrivacySettings) && !empty($privacySettingsPopupEnable)) { ?>
                            <a href="#" class="wordpress-fire-push-popup-privacy-settings-text wordpress-fire-push-open-privacy-settings-modal" style="color: <?php echo $popupLinkColor ?>;"><?php echo $popupTextPrivacySettings ?></a>
                        <?php } ?>

                        <?php if(!empty($cookiePolicyPage) && !empty($popupTextCookiePolicy)) { ?>
                            <a href="<?php echo get_permalink($cookiePolicyPage) ?>" class="wordpress-fire-push-popup-read-more" style="color: <?php echo $popupLinkColor ?>;"><?php echo $popupTextCookiePolicy ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}