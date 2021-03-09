<?php

class WordPress_Fire_Push_Subscribers extends WordPress_Fire_Push
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
            __('Subscribers', 'wordpress-fire-push'),
            __('Subscribers', 'wordpress-fire-push'),
            'manage_options',
            'fire-push-subscribers',
            array($this, 'render')
        );
    }

    public function render()
    {
		
		if(isset($_GET['gdsub'])){
			$TokenID = $_GET['gdsub'];
			$guestTokens = $this->token_manager->deleteGuestTokens($TokenID);
		}
		if(isset($_GET['dsub'])){
			$userId = $_GET['dsub'];
            delete_user_meta($userId, 'fire_push_tokens');
		}
		
		$guestTokens = $this->token_manager->getGuestTokens();
        $tokensByUser = $this->token_manager->getUsersWithTokens();

        if(empty($guestTokens) && empty($tokensByUser)) {
            echo __('No Tokens found so far ...', 'wordpress-fire-push');
            return false;
        }

        echo '<h2>' . __('Subscribers', 'wordpress-fire-push') . ' (' . (count($tokensByUser) + count($guestTokens)) . ')</h2>';

    	?>

		<table class="fire-push-table">
			<thead>
				<tr>
					 <th><?php echo __('User', 'wordpress-fire-push') ?></th>
                     <th><?php echo __('Tokens', 'wordpress-fire-push') ?></th>
                     <th><?php echo __('Dekete', 'wordpress-fire-push') ?></th>
                     <!-- <th><?php echo __('Notifications Count', 'wordpress-fire-push') ?></th> -->
				</tr>
			</thead>

			<?php
	    	foreach ($tokensByUser as $tokenByUser) {

				$tokens = !empty(get_user_meta($tokenByUser->ID, 'fire_push_tokens', true)) ? get_user_meta($tokenByUser->ID, 'fire_push_tokens', true) : array();

				echo '<tr>';
					echo '
						<td>' . $tokenByUser->data->user_nicename . '</td>
                        <td>' . implode('<br>', $tokens) . '</td><td style="text-align: right;"><a href="admin.php?page=fire-push-subscribers&dsub='.$tokenByUser->ID.'" >Delete</a></td>';
				echo '</tr>';
	    	}

            foreach ($guestTokens as  $guestToken) {

                echo '<tr>';
                    echo '
                        <td>' . __('Guest token', 'wordpress-fire-push') . '</td>
                        <td>' . $guestToken->token . '</td><td style="text-align: right;"><a href="admin.php?page=fire-push-subscribers&gdsub='.$guestToken->id.'" >Delete</a></td>';

                echo '</tr>';
            }
	    	?>
		</table>
    	<?php
    }
}