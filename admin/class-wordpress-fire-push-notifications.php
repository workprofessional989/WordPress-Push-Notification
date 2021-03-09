<?php

class WordPress_Fire_Push_Notifications extends WordPress_Fire_Push
{
    protected $plugin_name;
    protected $version;
    protected $token_manager;

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
     * Init Reports Page in Admin
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @return  [type]                       [description]
     */
    public function add_menu()
    {       
        add_submenu_page(
            'wordpress_fire_push_options_options',
            __('Notifications', 'wordpress-fire-push'),
            __('Notifications', 'wordpress-fire-push'),
            'manage_options',
            'fire-push-notifications',
            array($this, 'render')
        );
    }

    public function render()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "fire_push_notifications";

        $notifications = 
        $wpdb->get_results( "SELECT * FROM $table_name" );

        if(!$notifications || empty($notifications)) {
            echo __('No Notifications sent so far ...', 'wordpress-fire-push');
            return false;
        }
		
		if(isset($_GET['delete'])){			
			$did = $_GET['delete'];
			$wpdb->delete( $table_name, array( 'id' => $did ) );
		}
		
		
		
		
		
		
        $notifications = array_reverse($notifications, true);
    	?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" >
		<table class="fire-push-table">
			<thead>
				<tr>
					 <th><?php echo __('Notification', 'wordpress-fire-push') ?></th>
                     <th><?php echo __('Sent', 'wordpress-fire-push') ?></th>
                     <th><?php echo __('Failed', 'wordpress-fire-push') ?></th>
                     <th><?php echo __('Clicked', 'wordpress-fire-push') ?></th>
					 <th><?php echo __('Date', 'wordpress-fire-push') ?></th>
					 <th><?php echo __('Type', 'wordpress-fire-push') ?></th>
					 <th><?php echo __('Actions', 'wordpress-fire-push') ?></th>
				</tr>
			</thead>

			<?php
	    	foreach ($notifications as $notification) {
                $notification_data = unserialize( $notification->data );
				$type = "Sent";
				if($type == 1){ $type = "Broadcast";    }
                if(!isset($notification_data['clicked'])) {
                     $notification_data['clicked'] = 0;
                }

                echo '<tr>';

                    echo '<td width="340px">';
                        echo '<a href="' . $notification_data['notification']['click_action'] . '" target="_blank" class="fire-push-notification">';
                            echo '<div class="fire-push-notification-left">';
                                echo '<div class="fire-push-icon"><img width="60" src="' . $notification_data['notification']['icon'] . '"></div>';
                            echo '</div>';
                            echo '<div class="fire-push-notification-right">';
                                echo '<div class="fire-push-notification-title">' . $notification_data['notification']['title'] . '</div>';
                                echo '<div class="fire-push-notification-text">' . $notification_data['notification']['body'] . '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</td>';
                    echo '<td>' . $notification_data['success'] . '</td>';
                    echo '<td>' . $notification_data['failure'] . '</td>';
                    echo '<td>' . $notification_data['clicked'] . '</td>';
					echo '<td>' . $notification->date .'&nbsp;'. $notification->time. '</td>';
					echo '<td>' . $type . '</td>';
					?>
							<td>
		<?php if($notification->status == 1){  ?>
		<i class="fa fa-clock-o" aria-hidden="true" style="font-size:18px;color:#FFEB3B;cursor:pointer" ></i>&nbsp;&nbsp;
		<?php  }else{   ?>
		<i class="fa fa-check-square-o" aria-hidden="true" style="font-size:18px;color:#00C853;cursor:pointer" ></i>&nbsp;&nbsp;
		<?php   } ?>
		
			<a href="admin.php?page=wordpress_fire_push_options_options&tab=6&clone=<?php echo $notification->id; ?>" ><i class="fa fa-clone" aria-hidden="true" style="font-size:18px;color:#2979FF;cursor:pointer" ></i></a>&nbsp;&nbsp;
			<!--<?php if($notification->status == 1){  ?><a href="admin.php?page=wordpress_push_options_options&tab=6&edit=<?php echo $notification->id; ?>" ><i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size:18px;color:#69F0AE;cursor:pointer" ></i></a>&nbsp;&nbsp;<?php   } ?> -->
			<i class="fa fa-trash" aria-hidden="true" style="font-size:18px;color:#D32F2F;cursor:pointer" onclick="deletepush(<?php echo $notification->id; ?>);" ></i></td>
			
			<?php
                echo '</tr>';
	    	}
	    	?>
			
			
			<script>		 
    		function deletepush(id){	
				if (confirm('Are you sure you want to delete?')) {
					location.href = "admin.php?page=fire-push-notifications&delete="+id;
				} 
	
			}
			
		</script>
			
			
			
			
			
		</table>
    	<?php
    }

    public function updateClicked()
    {
        /*if (!defined('DOING_AJAX') || !DOING_AJAX) {
            $this->msg = 'No AJAX call!';
            $this->message();
        }

        if(!isset($_POST['fire_push_id']) || empty($_POST['fire_push_id'])) {
            $this->msg = 'fire_push_id Missing';
            $this->message();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "fire_push_notifications";

        $fire_push_id = $_POST['fire_push_id'];

        $notification = 
        $wpdb->get_results( 
            $wpdb->prepare( "SELECT * FROM $table_name WHERE fire_push_id = '%s'",  esc_sql( $fire_push_id) ) 
        );
        if(!$notification) {
            $this->msg = 'Notification not found!';
            $this->message();
        }

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
        ); */       
    }

    public function message()
    {
        $return = array(
            'msg' => $this->msg,
        );
        die(json_encode($return));
    }

    public function migrateNotificationsToTable() 
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "fire_push_notifications";
        $options_table = $wpdb->prefix . 'options';

        $notifications = 
        $wpdb->get_results( "SELECT * FROM $options_table WHERE option_name LIKE 'fire_push_notification_%' LIMIT 500" );
        if(!$notifications || empty($notifications)) {
            wp_die('All notifications already migrated');
            return false;
        }

        $notifications_count = count($notifications);

        foreach ($notifications as $notification) {

            $multicast_id = str_replace('fire_push_notification_', '', $notification->option_name);

            $data = array(
                'multicast_id' => $multicast_id,
                'data' => $notification->option_value,
            );

            $format = array('%s');
            $wpdb->insert($table_name, $data, $format);
            
            delete_option($notification->option_name);
        }

        if($notifications_count > 500) {
            wp_die($notifications_count . ' Notifications migrated. Please click again to migrate remaining.');
        } else {
            wp_die('All ' . $notifications_count . ' Notifications migrated.');
        }
    }
}