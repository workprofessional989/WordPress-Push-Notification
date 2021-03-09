<?php

class WordPress_Fire_Push_Token_Manager extends WordPress_Fire_Push
{
    protected $plugin_name;
    protected $version;

    protected $tokenExists = false;
    protected $tokenSaved = false;
    protected $msg = '';

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
     * Getter
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @param   [type]                       $property [description]
     * @return  [type]                                 [description]
     */
    public function __get($property) {
        if (property_exists($this, $property)) {
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
        if (property_exists($this, $property)) {
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
    }

    public function updateToken()
    {
        global $wpdb;

        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            $this->msg = 'No AJAX call!';
            $this->message();
        }

        if(!isset($_POST['token']) || empty($_POST['token'])) {
            $this->msg = 'Token Missing';
            $this->message();
        }

        $token = $_POST['token'];
        $userID = get_current_user_id();

        $saveGuestToken = $this->get_option('saveGuestToken');
        if(!$saveGuestToken && $userID == 0) {
            $this->msg = 'Guest token saving disabled';
            $this->message();
        }

        if($userID !== 0) {

            $tokens = $this->getTokensByUser($userID);

            if(!is_array($tokens)) {
                $tokens = array();
            }

            if(in_array($token, $tokens)) {
                $this->tokenExists = true;
                $this->msg = 'User token already saved';
                $this->message();
            } else {
                $tokens[] = $token;
            }

            $this->tokenSaved = update_user_meta($userID, 'fire_push_tokens', $tokens);
        } else {

            $table_name = $wpdb->prefix . "fire_push_tokens";

            $checkExists = 
            $wpdb->get_results( 
                $wpdb->prepare( "SELECT token FROM $table_name WHERE token = '%s'",  esc_sql( $token) ) 
            );

            if(in_array($token, $tokens)) {
                $this->tokenExists = true;
                $this->msg = 'Guest token already saved';
                $this->message();
            }

            $data = array('token' => $token);
            $format = array('%s');
            $wpdb->insert($table_name, $data, $format);
            $this->tokenSaved = $wpdb->insert_id;

            delete_transient( 'fire_push_guest_tokens' );
        }

        if($this->tokenSaved) {
            $this->msg = 'Token saved!';
            $this->message();
        } else {
            $this->msg = 'Token not saved!';
            $this->message();
        }
    }

    public function message()
    {
        $return = array(
            'tokenExists' => $this->tokenExists,
            'tokenSaved' => $this->tokenSaved,
            'msg' => $this->msg,
        );
        die(json_encode($return));
    }

    public function getTokens($roles)
    {
        $tokens = array();

        $query = array(
            'meta_key'  => 'fire_push_tokens',
        );

        if(is_array($roles) && !empty($roles)) {
            $query['role__in'] = $roles;
        }  

        $users = get_users($query);

        $transientKeyUser = md5(json_encode($roles));
        $transient = get_transient( 'fire_push_user_tokens_' . $transientKeyUser );
        
        if( ! empty( $transient ) ) {
            $tokens = array_merge($transient, $tokens);
        } else {
            foreach ($users as $user) {
                $user_tokens = get_user_meta( $user->ID, 'fire_push_tokens', true);
                if(is_array($user_tokens)) {
                    set_transient( 'fire_push_user_tokens_' . $transientKeyUser, $user_tokens, $this->get_option('transientExpiration') );
                    $tokens = array_merge($user_tokens, $tokens);
                }
            }
        }

        if($roles == 'all') {

            global $wpdb;

            $transientAll = get_transient( 'fire_push_guest_tokens' );
            if( ! empty( $transientAll ) ) {
                $tokens = array_merge($transientAll, $tokens);
            } else {
                $table_name = $wpdb->prefix . "fire_push_tokens";

                $guestTokens = $wpdb->get_results( "SELECT token FROM $table_name", 'ARRAY_N' );
                $guestTokens = $this->flatten($guestTokens);

                if(is_array($guestTokens)) {
                    set_transient( 'fire_push_guest_tokens', $guestTokens, $this->get_option('transientExpiration') );
                    $tokens = array_merge($tokens, $guestTokens);
                }
            }
        }

        return $tokens;
    }

    private function flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }

    public function getGuestTokens($userID = 0)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . "fire_push_tokens";

        $tokens = $wpdb->get_results( "SELECT id, token FROM $table_name" );
        

        return $tokens;
    }


	 public function deleteGuestTokens($TokenID = 0)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . "fire_push_tokens";
		$wpdb->delete( $table_name, array( 'id' => $TokenID ) );
     
    }

  public function deleteloginTokens($TokenID = 0)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . "fire_push_tokens";
		$wpdb->delete( $table_name, array( 'id' => $TokenID ) );
     
    }

    public function getTokensByUser($userID = 0)
    {
        if($userID === 0) {
            $userID = get_current_user_id();
        }

        $tokens = get_user_meta($userID, 'fire_push_tokens', true);

        return $tokens;
    }

    public function getUsersWithTokens()
    {
        $users = get_users(array(
            'meta_key'     => 'fire_push_tokens',
        ));

        return $users;
    }

    /**
     * Init
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function getTokenData($token)
    {
        // https://developers.google.com/instance-id/reference/server
        $headers = array
        (
             'Authorization: key=AAAArkh9r0w:APA91bFZDnBvbzOpxc1-3KcVBypuxBTppoc5nmO6wVix7A67GVM6e3QyHLqWE53WnYHG2ZMDxkqDcTEtYqHOPFXAYSCo9fwvEoE4ashbVX8JFulvkn2Ysi_StayaRWM6pPt3MWJc9a09ODyzKEcJl8X8JKOQJfo0og', 
             'Content-Length: 0'
        );                                                                                 
                                                                                                                             
        $ch = curl_init();  

        curl_setopt( $ch,CURLOPT_URL, 'https://iid.googleapis.com/iid/info/' . $token . '?details=true' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        // curl_setopt( $ch,CURLOPT_POSTFIELDS, $data_string);                                                                  
                                                                                                                             
        $result = curl_exec($ch);

        curl_close ($ch);

        echo "<p>&nbsp;</p>";
        echo "The Result : ".$result;
        die();
    }

    public function migrateGuestTokensToTable() 
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "fire_push_tokens";

        $guestTokens = get_option('fire_push_guest_tokens');
        if(!$guestTokens || empty($guestTokens)) {
            wp_die('Guest tokens already migrated');
            return false;
        }

        foreach ($guestTokens as $guestToken) {

            $data = array('token' => $guestToken);
            $format = array('%s');
            $wpdb->insert($table_name, $data, $format);
            $this->tokenSaved = $wpdb->insert_id;
        }
        delete_option('fire_push_guest_tokens');
        delete_transient( 'fire_push_guest_tokens' );

        wp_die('All guest tokens migrated');
    }

    public function deleteDuplicateGuestTokens() 
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "fire_push_tokens";

        $wpdb->query("delete $table_name
        from $table_name
        inner join (
            select max(id) as lastId, token
            from $table_name
            group by token
            having count(*) > 1
        ) duplic on duplic.token = $table_name.token
        where $table_name.id < duplic.lastId;");

        delete_transient( 'fire_push_guest_tokens' );

        wp_die('Duplicates Deleted');
    }

    
}