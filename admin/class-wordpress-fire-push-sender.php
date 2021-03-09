<?php

class WordPress_Fire_Push_Sender extends WordPress_Fire_Push
{
    protected $plugin_name;
    protected $version;
    protected $token_manager;

    protected $title = "";
    protected $body = "";
    protected $icon = "";
    protected $click_action = "";
    protected $server_key = "";

    protected $notice = "";    

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
    public function __construct($plugin_name, $version, $token_manager)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->token_manager = $token_manager;
    }

    /**
     * Getter
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @param   [type]                       $property [description]
     * @return  [type]                                 [description]
     */
    public function __get($property) {
        if(property_exists($this, $property)) {
            return $this->$property;
        }
    }
    /**
     * Setter
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @param   [type]                       $property [description]
     * @return  [type]                                 [description]
     */
    public function __set($property, $value) {
        if(property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
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

        if(!$this->get_option('enable')) {
            return false;
        }

        add_action('admin_notices', array($this, 'notice' ));

        $this->setDefaults();

        add_action( 'save_post', array($this, 'notifyOnPublishOrUpdate'), 10, 3);
        add_action( 'comment_post', array($this, 'notifyOnComment'), 10, 2);

        // WooCommerce        
        if($this->get_option('woocommerceNewProduct')) {
            add_action( 'save_post', array($this, 'notifyNewProduct'), 10, 3);
        }

        if($this->get_option('woocommercePriceDrop')) {
            add_action( 'update_post_meta', array($this, 'notifyPriceDrop'), 10, 4);
        }

        if($this->get_option('woocommerceNewOrder')) {
            add_action('wp_insert_post', array($this, 'notifyOnNewOrder'));
        }

        if($this->get_option('woocommerceLowStock')) {
            add_action( 'woocommerce_low_stock', array($this, 'notifyLowStock') );
            add_action( 'woocommerce_no_stock',  array($this, 'notifyLowStock') );
        }
    }

    public function setDefaults()
    {
        $this->title = $this->get_option('defaultTitle');
        $this->body = $this->get_option('defaultBody');
        $this->icon = $this->get_option('defaultIcon')['url'];
        $this->click_action = $this->get_option('defaultClickAction');
    }

    public function initNotification($i) {

    }

    public function notifyOnPublishOrUpdate($post_id, $post, $update)
    {

        if($post->post_type == 'revision') {
            return false;
        }

        if($post->post_status == 'draft' || $post->post_status == 'auto-draft' || $post->post_status == 'trash' || $post->post_status == "future") {
            return false;
        }

        if ( !get_post_meta( $post_id, 'firstpublish', true ) ) {
            update_post_meta( $post_id, 'firstpublish', true );
            $update = false;
        }

        for ($i=1; $i <= 4; $i++) {  

            $action = 'wpNotification' . $i;

            if(!$this->get_option($action . 'Enabled')) {
                continue;
            }

            $onUpdate = $this->get_option($action . 'OnUpdate');
            if(!$onUpdate && $update) {
                continue;
            }

            $onPublish = $this->get_option($action . 'OnPublish');
            if(!$onPublish && !$update) {
                continue;
            }

            $allowedPostTypes = $this->get_option($action . 'PostTypes');
            if(is_array($allowedPostTypes) && !in_array($post->post_type, $allowedPostTypes)) {
                continue;
            }

            $check = $this->setData($post, $action);

            if(!$check) {
                continue;
            }


            $userRoles = $this->get_option($action . 'UserRoles');
            $this->send($userRoles);
        }
    }

    public function notifyOnComment($comment_ID, $comment_approved)
    {
        if( 1 !== $comment_approved ){
            return false;
        }

        for ($i=1; $i <= 4; $i++) {  

            $action = 'wpNotification' . $i;

            if(!$this->get_option($action . 'Enabled')) {
                continue;
            }

            $onComment = $this->get_option($action . 'OnNewComment');
            if(!$onComment) {
                continue;
            }

            $comment = get_comment($comment_ID, ARRAY_A);

            $post = get_post($comment['comment_post_ID']);
            $check = $this->setData($post, $action, $comment);

            if(!$check) {
                continue;
            }

            $userRoles = $this->get_option($action . 'UserRoles');
            $this->send($userRoles);
        }
    }

    public function setData($post, $action, $comment = array())
    {

        do_action('fire_push_before_set_data', $post, $action);

        $post_metas = get_post_meta($post->ID, '', true);
        $tmp = array();
        foreach ($post_metas as $post_meta_key => $post_meta) {
            $tmp[$post_meta_key] = isset($post_meta[0]) ? $post_meta[0] : '';
        }
        $post_metas = $tmp;

        $data = array_merge($post_metas, (array) $post, (array) $comment);

        $data['post_author'] = get_userdata($data['post_author'])->user_nicename;
        $data['post_content'] = strip_tags( preg_replace("/\[[^\]]+\]/", '', $data['post_content']) );
        $data['post_excerpt'] = strip_tags( preg_replace("/\[[^\]]+\]/", '', $data['post_excerpt']) );

        if($action == "woocommerceNewOrder") {
            $data['post_url'] = admin_url() . 'post.php?post=' . $data['ID'] . '&action=edit';
        } else {
            $data['post_url'] = get_permalink($data['ID']);
        }

        $tmp = array();
        foreach ($data as $key => $data) {

            $tmp['{' . $key . '}'] = $data;
        }
        $data = apply_filters('fire_push_data_to_replace', $tmp, $action);


        $this->title = strtr($this->get_option($action . 'Title'), $data);
        $this->body = strtr($this->get_option($action . 'Body'), $data);
        if($this->get_option($action . 'UseFeatureImage') && has_post_thumbnail($data['ID'])) {
            $this->icon = get_the_post_thumbnail_url($data['ID']);
        } elseif(isset( $this->get_option($action . 'Icon')['url']) && !empty($this->get_option($action . 'Icon')['url'])) {
            $this->icon = $this->get_option($action . 'Icon')['url'];
        };

        $this->click_action = strtr($this->get_option($action . 'URL'), $data);

        do_action('fire_push_after_set_data', $data);

        return true;

    }

    public function notifyNewProduct($post_id, $post, $update)
    {
        if($update) {
            return false;
        }

        if($post->post_type !== 'product') {
            return false;
        }

        if($post->post_status == 'draft' || $post->post_status == 'auto-draft' || $post->post_status == 'trash' || $post->post_status == "future") {
            return false;
        }

        if($post->post_type !== 'product') {
            return false;
        }

        $check = $this->setData($post, 'woocommerceNewProduct');
        if(!$check) {
            return false;
        }

        $this->send(); 

    }

    public function notifyOnNewOrder($order_id)
    {
        if(get_post_type($order_id) !== 'shop_order') {
            return false;
        }

        // $order = new WC_Order($order_id);
        $post = get_post($order_id);
        $check = $this->setData($post, 'woocommerceNewOrder');
        if(!$check) {
            return false;
        }

        $this->send(array('administrator', 'shop_manager')); 
    }

    public function notifyLowStock($product)
    {
        $post = get_post($product->get_id());
        $check = $this->setData($post, 'woocommerceLowStock');
        if(!$check) {
            return false;
        }

        $this->send(array('administrator', 'shop_manager')); 
    }

    public function notifyPriceDrop( $meta_id, $object_id, $meta_key, $_meta_value )
    {
        $allowedMetaKeys = array('_regular_price','_sale_price');
        if(!in_array($meta_key, $allowedMetaKeys)) {
            return false;
        }

        $allowedPostTypes = array( 'product', 'product_variation' );
        if(!in_array(get_post_type( $object_id ), $allowedPostTypes)) {
            return false;
        }

        $priceBefore = get_post_meta($object_id, $meta_key, true);
        $priceAfter = $_meta_value;

        if(empty($priceBefore)) {
            return false;
        }

        if($priceAfter > $priceBefore) {
            return false;
        }

        $post = get_post($object_id);
        $check = $this->setData($post, 'woocommercePriceDrop');
        if(!$check) {
            return false;
        }

        $this->send(); 
    }   


    public function sendCustomNotification()
    {
        if(! is_admin()) {
            $this->notice .= __('You are not an admin', 'wordpress-fire-push');
            return false;
        }

        $this->title = $this->get_option('customTitle');
        $this->body = $this->get_option('customBody');
        if(isset( $this->get_option('customIcon')['url']) && !empty($this->get_option('customIcon')['url'])) {
            $this->icon = $this->get_option('customIcon')['url'];
        };
        $this->click_action = $this->get_option('customURL');

        if(empty($this->title) || empty($this->body)) {
            $this->notice .= __('Title or Text Missing!', 'wordpress-fire-push');
            return false;
        }

        // Remove Old Custom Notification Data
        $options = get_option('wordpress_fire_push_options');

        /*unset($options['customTitle']);
        unset($options['customBody']);
        unset($options['customIcon']);
        unset($options['customURL']);

        update_option('wordpress_fire_push_options', $options);*/

        $this->send();

        wp_redirect(get_admin_url() . 'admin.php?page=wordpress_fire_push_options_options');
        
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
    public function send($roles = 'all')
    {
        global $wpdb;

        if(empty($roles)) {
            $roles = "all";
        }

        $tokens = $this->token_manager->getTokens($roles);
        if(empty($tokens)) {
            return false;
        }

        $server_key = $this->get_option('serverKey');
        if(!$server_key) {
            wp_die('Server Key is missing. Can not send Notifications!');
        } else {
            $this->server_key = $server_key;
        }

        $fire_push_id = uniqid();

        
		
		$siteurl = site_url();
		$link = add_query_arg( array(
		'fire_push_id' => $fire_push_id,
		'surl' => $this->click_action,
		), $siteurl );
		
		
		
		$data = array(
            "registration_ids" => $tokens,
            'fire_push_id' => $fire_push_id,
            "notification" => array( 
                "title" => $this->title,
                "body" => $this->body,
                "icon" => $this->icon,
                "click_action" => $link
            )
        );


        $data = apply_filters('fire_push_data_to_send', $data);

        if($this->get_option('trackClicks') && !empty($data['notification']['click_action'])) {
            $data['notification']['click_action'] = $data['notification']['click_action'] . '?fire_push_id=' . $fire_push_id;
        }

        do_action('fire_push_before_send_notification', $data);

        $registration_ids = array();
        if(count($data['registration_ids']) > 300) {
            $registration_ids = array_chunk($data['registration_ids'], 400);
        } else {
            $registration_ids[] = $data['registration_ids'];
        }

        $headers = array(
             'Authorization: key=' . $this->server_key, 
             'Content-Type: application/json'
        );                                                                                 

        // Performance Tweak
        $failedSubcribers = array();
        foreach ($registration_ids as $registration_id) {

            $data['registration_ids'] = $registration_id;

            $data_string = json_encode($data); 

            $ch = curl_init();  

            curl_setopt( $ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );                                                                  
            curl_setopt( $ch, CURLOPT_POST, true );  
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
                                                                                                                                 
            $result = json_decode(curl_exec($ch), true);

            curl_close ($ch);

            do_action('fire_push_notification_result', $result);

            if($result['success'] > 0) {

                $multicast_id = $result['multicast_id'];

                $notification = serialize( array_merge($data, $result) );
                
                $table_name = $wpdb->prefix . "fire_push_notifications";
				$customDate = $this->get_option('customDate');
				$customTime = $this->get_option('customTime');
				$type = 0;
				$status = 1;
                $data = array(
                    'multicast_id' => $multicast_id,
                    'fire_push_id' => $fire_push_id,
                    'data' => $notification,
                    'date' => $customDate,
                    'time' => $customTime,
                    'type' => $type,
                    'status' => $status
                );

                $format = array('%s');
                $wpdb->insert($table_name, $data, $format);

                if($this->get_option('removeFailedSubscribers') && !empty($result['results'])) {
                    foreach ($result['results'] as $key => $subscribersSend) {
                        if(isset($subscribersSend['error'])) {
                            $failedSubcribers[] = $registration_ids[0][$key];
                        }
                    }
                }
            }
        }

        if($this->get_option('removeFailedSubscribers') && !empty($failedSubcribers)) {

            $table_name = $wpdb->prefix . "fire_push_tokens";

            foreach ($failedSubcribers as $failedSubcriber) {
                $wpdb->delete( $table_name, array( 'token' => $failedSubcriber ) );
            }

            delete_transient( 'fire_push_guest_tokens' );
        }


        do_action('fire_push_after_send_notification', $notification);
    }


    /**
     * Show a notice
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @return [type] [description]
     */
    public function notice()
    {
        if(empty($this->notice)) {
            return false;
        }

        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo $this->notice ?></p>
        </div>
        <?php
    }
}