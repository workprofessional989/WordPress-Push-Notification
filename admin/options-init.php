<?php

	/**
	 * For full documentation, please visit: http://docs.reduxframework.com/
	 * For a more extensive sample-config file, you may look at:
	 * https://github.com/reduxframework/redux-framework/blob/master/sample/sample-config.php
	 */

if (! class_exists('Redux')) {
	return;
}


$time  = date('h:i', time());
$date = date('m/d/Y', time());


if(isset($_GET['clone']) && !empty($_GET['clone'])){
		global $wpdb;
        $table_name = $wpdb->prefix . "fire_push_notifications";
		$cloneid = $_GET['clone'];	        
        $notification = $wpdb->get_row( "SELECT * FROM $table_name where id = $cloneid " );
		$clonedata = unserialize( $notification->data );
		
		// Remove Old Custom Notification Data
			$options = get_option('wordpress_fire_push_options');
			$options['customTitle']  =  $clonedata['notification']['title'];
			$options['customBody']  =  $clonedata['notification']['body'];
			$options['customURL']  =  $clonedata['notification']['click_action'];
			$options['customIcon']  = array('url' =>  $clonedata['notification']['icon']);
			$options['customDate']  = $notification->date;
			$options['customTime']  = $notification->time;
			update_option('wordpress_fire_push_options', $options);
		
	}


	// This is your option name where all the Redux data is fire-pushd.
	$opt_name = "wordpress_fire_push_options";

	/**
	 * ---> SET ARGUMENTS
	 * All the possible arguments for Redux.
	 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
	 * */

	$theme = wp_get_theme(); // For use with some settings. Not necessary.

	$args = array(
		'opt_name' => 'wordpress_fire_push_options',
		'use_cdn' => true,
		'dev_mode' => false,
		'display_name' => __('Push Notification', 'wordpress-fire-push'),
		'display_version' => '1.0',
		'page_title' => __('Push Notification', 'wordpress-fire-push'),
		'update_notice' => true,
		'intro_text' => '',
		'footer_text' => '&copy; ' . date('Y') . ' Monks',
		'admin_bar' => true,
		'menu_type' => 'menu',
		'menu_title' => __('Push Notification', 'wordpress-fire-push'),
		'menu_icon' => 'dashicons-testimonial',
		'allow_sub_menu' => true,
		'page_parent' => '',
		'page_parent_post_type' => '',
		'customizer' => false,
		'default_mark' => '*',
		'hints' => array(
			'icon_position' => 'right',
			'icon_color' => 'lightgray',
			'icon_size' => 'normal',
			'tip_style' => array(
				'color' => 'light',
			),
			'tip_position' => array(
				'my' => 'top left',
				'at' => 'bottom right',
			),
			'tip_effect' => array(
				'show' => array(
					'duration' => '500',
					'event' => 'mouseover',
				),
				'hide' => array(
					'duration' => '500',
					'event' => 'mouseleave unfocus',
				),
			),
		),
		'output' => true,
		'output_tag' => true,
		'settings_api' => true,
		'cdn_check_time' => '1440',
		'compiler' => true,
		'page_permissions' => 'manage_options',
		'save_defaults' => true,
		'show_import_export' => true,
		'database' => 'options',
		'transient_time' => '3600',
		'network_sites' => true,
	);

	Redux::setArgs($opt_name, $args);

	/*
	 * ---> END ARGUMENTS
	 */

	/*
	 * ---> START HELP TABS
	 */

	$tabs = array(
		array(
			'id'      => 'help-tab',
			'title'   => __('Information', 'wordpress-fire-push'),
			'content' => __('<p>Need support? Please use the comment function on codecanyon.</p>', 'wordpress-fire-push')
		),
	);
	Redux::setHelpTab($opt_name, $tabs);

	/*
	 * <--- END HELP TABS
	 */


	/*
	 *
	 * ---> START SECTIONS
	 *
	 */
	Redux::setSection( $opt_name, array(
		'title'  => __( 'General Settings', 'wordpress-fire-push' ),
		'id'     => 'general',
		'desc'   => __( 'Need support? Please use the comment function on codecanyon.', 'wordpress-fire-push' ),
		'icon'   => 'el el-home',
	) );

	Redux::setSection($opt_name, array(
		'title'      => __('General', 'wordpress-fire-push'),
		'id'         => 'general-settings',
		'subsection' => true,
		'fields'     => array(
			array(
				'id'       => 'enable',
				'type'     => 'checkbox',
				'title'    => __('Enable', 'wordpress-fire-push'),
				'default'  => '1',
			),
			/*array(
				'id'       => 'saveGuestToken',
				'type'     => 'checkbox',
				'title'    => __('Save Guest Token', 'wordpress-fire-push'),
				'subtitle'  => __('Tokens are used to send notifications to users. If you do not want to send notifictions to logged out users disable this option to only save tokens for logged in users.', 'wordpress-fire-push'),
				'default'  => '1',
			),
			array(
				'id'       => 'trackClicks',
				'type'     => 'checkbox',
				'title'    => __('Track Notification Clicks', 'wordpress-fire-push'),
				'default'  => '1',
			),
			array(
				'id'       => 'removeFailedSubscribers',
				'type'     => 'checkbox',
				'title'    => __('Remove failed Subscribers', 'wordpress-fire-push'),
				'subtitle'  => __('Remove subscribers who have blocked or removed their access.', 'wordpress-fire-push'),
				'default'  => '0',
			),
            array(
                'id'       => 'transientExpiration',
                'type'     => 'spinner',
                'title'    => __( 'Seconds when the transient token cache will be renewed in Seconds.', 'wordpress-fire-push' ),
                'min'      => '0',
                'step'     => '1',
                'max'      => '9999999999999999',
                'default'  => '86400',
            ),
			array(
				'id'   => 'migrateToken',
				'type' => 'info',
				'desc' => '<div style="text-align:center;">
						<a href="' . get_admin_url() . 'admin.php?page=wordpress_fire_push_options_options&migrate-tokens=true" class="button button-success">' . __('Migrate guest tokens', 'wordpress-fire-push') . '</a>
						<a href="' . get_admin_url() . 'admin.php?page=wordpress_fire_push_options_options&delete-duplicate-tokens=true" class="button button-success">' . __('Delete duplicate tokens', 'wordpress-fire-push') . '</a>
						<a href="' . get_admin_url() . 'admin.php?page=wordpress_fire_push_options_options&migrate-notifications=true" class="button button-success">' . __('Migrate notifications', 'wordpress-fire-push') . '</a>
					</div>'
			),*/
		)
	));

	Redux::setSection($opt_name, array(
		'title'      => __('Credentials', 'wordpress-fire-push'),
		'id'         => 'credentials',
		'desc'       => __('Please read our documentation how to get your Firebase API Credentials here.', 'wordpress-fire-push'),
		'subsection' => true,
		'fields'     => array(
			array(
				'id'       => 'serverKey',
				'type'     => 'text',
				'title'    => __('Server Key', 'wordpress-fire-push'),
				'default'  => '',
				'required' => array('enable','equals','1'),
			),
			array(
				'id'       => 'apiKey',
				'type'     => 'text',
				'title'    => __('apiKey', 'wordpress-fire-push'),
				'default'  => '',
				'required' => array('enable','equals','1'),
			),
			array(
				'id'       => 'authDomain',
				'type'     => 'text',
				'title'    => __('authDomain', 'wordpress-fire-push'),
				'default'  => '',
				'required' => array('enable','equals','1'),
			),
			array(
				'id'       => 'databaseURL',
				'type'     => 'text',
				'title'    => __('databaseURL', 'wordpress-fire-push'),
				'default'  => '',
				'required' => array('enable','equals','1'),
			),
			array(
				'id'       => 'projectId',
				'type'     => 'text',
				'title'    => __('projectId', 'wordpress-fire-push'),
				'default'  => '',
				'required' => array('enable','equals','1'),
			),
			array(
				'id'       => 'storageBucket',
				'type'     => 'text',
				'title'    => __('storageBucket', 'wordpress-fire-push'),
				'default'  => '',
				'required' => array('enable','equals','1'),
			),
			array(
				'id'       => 'messagingSenderId',
				'type'     => 'text',
				'title'    => __('messagingSenderId', 'wordpress-fire-push'),
				'default'  => '',
				'required' => array('enable','equals','1'),
			),
		)
	));

	/*Redux::setSection($opt_name, array(
		'title'      => __('Defaults', 'wordpress-fire-push'),
		'id'         => 'defaults',
		'desc'       => __('Theses will be used as a fallback in case no specific data was defined.', 'wordpress-fire-push'),
		'subsection' => true,
		'fields'     => array(
			array(
				'id'       => 'defaultTitle',
				'type'     => 'text',
				'title'    => __('Title', 'wordpress-fire-push'),
				'default'  => get_bloginfo('name'),
				'required' => array('enable','equals','1'),
			),
			array(
				'id'       => 'defaultBody',
				'type'     => 'text',
				'title'    => __('Text', 'wordpress-fire-push'),
				'default'  => get_bloginfo('description'),
				'required' => array('enable','equals','1'),
			),
			array(
				'id'       => 'defaultClickAction',
				'type'     => 'text',
				'title'    => __('Click Action URL', 'wordpress-fire-push'),
				'default'  => get_bloginfo('wpurl'),
				'required' => array('enable','equals','1'),
			),
			array(
				'id'        =>'defaultIcon',
				'type'      => 'media',
				'url'       => true,
				'title'     => __('Icon', 'wordpress-fire-push'),
				'subtitle'  => __('The icon must be in square format.', 'wordpress-fire-push'),
				'args'      => array(
					'teeny'            => false,
				),
				'required' => array('enable','equals','1'),
			),
		)
	));*/

    Redux::setSection($opt_name, array(
        'title'      => __('Popup', 'wordpress-fire-push'),
        'id'         => 'popup-settings',
        'desc'       => __('The popup when user has not given permission for messaging.', 'wordpress-fire-push'),
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'popupEnable',
                'type'     => 'checkbox',
                'title'    => __('Enable Popup', 'wordpress-fire-push'),
                'default'  => '1',
            ),array(
				'id'        => 'popupImage',
				'type'      => 'media',
				'url'       => true,
				'title'     => __('Left Image', 'wordpress-fire-push'),
				'subtitle'  => __('The icon must be in square format.', 'wordpress-fire-push'),
				'args'      => array(
					'teeny'            => false,
				),
			), array(
                'id'       => 'popupTitle',
                'type'     => 'text',
                'title'    => __('Popup Title', 'wordpress-fire-push'),
                'subtitle' => __('Leave Empty if not needed', 'wordpress-fire-push'),
                'default'  => __('Welcome', 'wordpress-fire-push'),
                'required' => array('popupEnable','equals','1'),
            ), array(
                'id'       => 'popupText',
                'type'     => 'editor',
                'title'    => __('Popup Text', 'wordpress-fire-push'),
                'default'  => __('We would like to keep you updated with special notifications.', 'wordpress-fire-push'),
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'       => 'popupTextAgree',
                'type'     => 'text',
                'title'    => __('Popup Agree Text', 'wordpress-fire-push'),
                'subtitle' => __('Leave Empty if not needed', 'wordpress-fire-push'),
                'default'  => __('I accept', 'wordpress-fire-push'),
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'       => 'popupTextDecline',
                'type'     => 'text',
                'title'    => __('Popup Decline Text', 'wordpress-fire-push'),
                'subtitle' => __('Leave Empty if not needed', 'wordpress-fire-push'),
                'default'  => __('I decline', 'wordpress-fire-push'),
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'       => 'popupStyle',
                'type'     => 'select',
                'title'    => __('Popup Style', 'wordpress-fire-push'),
                'options' => array(
                    'wordpress-fire-push-popup-full-width' => __('Full Width', 'wordpress-fire-push'),
                    'wordpress-fire-push-popup-small' => __('Small Width', 'wordpress-fire-push'),
                ),
                'default' => 'wordpress-fire-push-popup-small',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'       => 'popupPosition',
                'type'     => 'select',
                'title'    => __('Popup Position', 'wordpress-fire-push'),
                'options' => array(
                    'wordpress-fire-push-popup-top' => __('Top', 'wordpress-fire-push'),
                    'wordpress-fire-push-popup-bottom' => __('Bottom', 'wordpress-fire-push'),
                ),
                'default' => 'wordpress-fire-push-popup-top',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'     =>'popupBackgroundColor',
                'type' => 'color',
                'title' => __('Background Color', 'wordpress-fire-push'), 
                'validate' => 'color',
                'default' => '#f7f7f7',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'     =>'popupTextColor',
                'type' => 'color',
                'title' => __('Text Color', 'wordpress-fire-push'), 
                'validate' => 'color',
                'default' => '#333333',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'     =>'popupAgreeColor',
                'type' => 'color',
                'title' => __('Accept Button Text Color', 'wordpress-fire-push'), 
                'validate' => 'color',
                'default' => '#FFFFFF',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'     =>'popupAgreeBackgroundColor',
                'type' => 'color',
                'title' => __('Accept Button Background Color', 'wordpress-fire-push'), 
                'validate' => 'color',
                'default' => '#4CAF50',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'     =>'popupDeclineColor',
                'type' => 'color',
                'title' => __('Decline Button Text Color', 'wordpress-fire-push'), 
                'validate' => 'color',
                'default' => '#FFFFFF',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'     =>'popupDeclineBackgroundColor',
                'type' => 'color',
                'title' => __('Decline Button Background Color', 'wordpress-fire-push'), 
                'validate' => 'color',
                'default' => '#F44336',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'     =>'popupLinkColor',
                'type' => 'color',
                'title' => __('Link Color', 'wordpress-fire-push'), 
                'validate' => 'color',
                'default' => '#FF5722',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'       => 'popupCloseIcon',
                'type'     => 'text',
                'title'    => __('Close Icon', 'wordpress-fire-push'),
                'default'  => 'fa fa-times',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'     =>'popupCloseIconBackgroundColor',
                'type' => 'color',
                'title' => __('Close Icon Background Color', 'wordpress-fire-push'), 
                'validate' => 'color',
                'default' => '#000000',
                'required' => array('popupEnable','equals','1'),
            ),
            array(
                'id'     =>'popupCloseIconColor',
                'type' => 'color',
                'title' => __('Close Icon Color', 'wordpress-fire-push'), 
                'validate' => 'color',
                'default' => '#FFFFFF',
                'required' => array('popupEnable','equals','1'),
            ),
        )
    ));

	Redux::setSection($opt_name, array(
		'title'      => __('Welcome Notification', 'wordpress-fire-push'),
		'id'         => 'welcome',
		'desc'       => __('Send users visiting your website a welcome notification.', 'wordpress-fire-push'),
		'subsection' => true,
		'fields'     => array(
			array(
				'id'       => 'welcomeEnabled',
				'type'     => 'checkbox',
				'title'    => __('Enable', 'wordpress-fire-push'),
				'default'  => '1',
			),
			array(
				'id'       => 'welcomeTitle',
				'type'     => 'text',
				'title'    => __('Title', 'wordpress-fire-push'),
				'default'  => __('Thanks for Subscribing', 'wordpress-fire-push'),
				'required' => array('welcomeEnabled','equals','1'),
			),
			array(
				'id'       => 'welcomeBody',
				'type'     => 'text',
				'title'    => __('Text', 'wordpress-fire-push'),
				'default'  => __('We will keep you updated as soon as something special comes up!', 'wordpress-fire-push'),
				'required' => array('welcomeEnabled','equals','1'),
			),
			array(
				'id'       => 'welcomeURL',
				'type'     => 'text',
				'title'    => __('URL (Click Action)', 'wordpress-fire-push'),
				'default'  => get_bloginfo('wpurl'),
			),
			array(
				'id'        =>'welcomeIcon',
				'type'      => 'media',
				'url'       => true,
				'title'     => __('Icon', 'wordpress-fire-push'),
				'subtitle'  => __('The icon must be in square format.', 'wordpress-fire-push'),
				'args'      => array(
					'teeny'            => false,
				),
				'required' => array('welcomeEnabled','equals','1'),
			),
		)
	));

	Redux::setSection($opt_name, array(
		'title'      => __('Send Notification', 'wordpress-fire-push'),
		'id'         => 'custom',
		'desc'       => __('Send Notification. You need to save the options before sending!', 'wordpress-fire-push'),
		'subsection' => true,
		'fields'     => array(
			array(
				'id'        => 'customIcon',
				'type'      => 'media',
				'url'       => true,
				'title'     => __('Icon', 'wordpress-fire-push'),
				'subtitle'  => __('The icon must be in square format.', 'wordpress-fire-push'),
				'args'      => array(
					'teeny'            => false,
				),
			),array(
				'id'       => 'customTitle',
				'type'     => 'text',
				'title'    => __('Title', 'wordpress-fire-push'),
				'default'  => ''
			),
			array(
				'id'       => 'customBody',
				'type'     => 'text',
				'title'    => __('Text', 'wordpress-fire-push'),
				'default'  => ''
			),
			array(
				'id'       => 'customURL',
				'type'     => 'text',
				'title'    => __('URL (Click Action)', 'wordpress-fire-push'),
				'default'  => '',
			),array(
				'id'        => 'customDate',
				'type'      => 'date',
				'url'       => true,
				'title'     => __('Date', 'wordpress-push'),
				'subtitle'  => __('Select Date', 'wordpress-push'),
				'default'  => $date,
				'args'      => array(
					'teeny'            => false,
				),
			),	array(
				'id'        => 'customTime',
				'type'      => 'text',
				'url'       => true,
				'title'     => __('Time', 'wordpress-push'),
				'subtitle'  => __('Current Time = '.$time.'', 'wordpress-push'),
				'default'  => '00:00',				
				
				
			),
			array(
				'id'   => 'customSend',
				'type' => 'info',
				'desc' => '<div style="text-align:center;">
					<a href="' . get_admin_url() . 'admin.php?page=fire-push-notifications&send-custom-notification=true&notype=0" class="button button-success">' . __('Send Now', 'wordpress-push') . '</a>
					&nbsp;&nbsp;&nbsp;<a href="' . get_admin_url() . 'admin.php?page=fire-push-notifications&send-custom-notification=true&notype=1" class="button button-success">' . __('Schedule', 'wordpress-push') . '</a></div>'
			),
			array(
				'id'   => 'custombox',
				'type' => 'info',
				'desc' => '<div class="mainviewbox" ><div class="chromebox" ><h2>Chrome using Windows10</h2><div class="sample_notification chromeview" > </div></div><div class="firefoxbox" ><h2>Firefox using Windows</h2><div class="sample_notification firefoxview" ></div></div><div class="androidbox" ><h2>Chrome On Android View</h2><div class="sample_notification Androidview" ></div></div> </div><div class="androidbox" ><h2>Chrome using Mac OS View</h2><div class="sample_notification chromemac" ></div></div> <span id="siteurl" >'.site_url().'</span>'
			)
			
		)
	));

