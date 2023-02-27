<?php
/*
Plugin Name: Limelight - Newsletter
Version: 3.0
Plugin URI: http://www.limelightdept.com/
Description: Provides a sidebar widget and shortcode to output Mailchimp newsletter subscription forms. Can also accept merge fields to send with the signup request.
Author: Radley Sustaire
Author URI: mailto:radleygh@gmail.com

Copyright 2012 Limelight Department (radley@limelightdept.com)
For use by Limelight Department and affiliates, do not distribute
*/

define( 'LMNEWS_URL', plugins_url() );      // No trailing slash
define( 'LMNEWS_PATH', dirname(__FILE__) ); // No trailing slash

add_option('lm_newsletter_apikey', '');
add_option('lm_newsletter_list_id', '');
add_option('lm_newsletter_merge_fields', '');


// Returns merge fields as key/value pair array.
function lm_get_merge_fields( $input = null ) {
	if ( $input !== null ) {
		$merge_fields = $input;
	}else{
		$merge_fields = get_option('lm_newsletter_merge_fields');
	}
	
	$merge_field_array = array();
	
	if ( is_array( $merge_fields ) ) {
		
		$merge_field_array = $merge_fields;
		
	}else if ( is_string($merge_fields) ) {
		
		$lines = preg_split('/(\r\n|\r|\n)+/', $merge_fields); // alpha : 111 \r\n beta : 222
		
		if ( $lines ) {
			foreach( $lines as $str ) {
				if ( !$str || trim($str) === '' ) break;
				
				// alpha : 111
				$split = preg_split('/ *: */', $str);
				
				if ( count($split) > 1 ) {
					$key = array_shift($split); // alpha
					$value = implode(':', $split); // 111
				}else{
					// did not include a colon, use key as value even though this is dumb
					$key = $str;
					$value = $str;
				}
				
				$merge_field_array[$key] = $value;
			}
		}
		
	}
	
	$merge_field_array = apply_filters( 'lm_merge_fields', $merge_field_array, $input );
	
	return $merge_field_array;
}

// Serializes a merge field array into a string.
function lm_merge_fields_serialize( $merge_fields ) {
	if ( !$merge_fields ) return false;
	
	return json_encode($merge_fields);
}

// Unserializes a merge field string back into key/value pair array.
function lm_merge_fields_unserialize( $merge_fields ) {
	if ( !$merge_fields ) return false;
	
	return json_decode($merge_fields, true);
}

function lm_merge_field_protect_defaults( $fields ) {
	if ( $fields ) {
		if ( isset($fields['FNAME']) ) unset($fields['FNAME']);
		if ( isset($fields['LNAME']) ) unset($fields['LNAME']);
	}
	
	return $fields;
}
add_filter( 'lm_merge_fields', 'lm_merge_field_protect_defaults', 10 );


function lm_newsletter_init() {
  if (isset($_REQUEST['lm_newsletter'])) {
    switch($_REQUEST['lm_newsletter']) {
      case 'subscribe':
        $is_json = (isset($_REQUEST['json']) && $_REQUEST['json'] == 1);
        lm_newsletter_submit( $is_json );
        break;
    }
  }
}
add_action( 'init', 'lm_newsletter_init' );

// Allows you to test your API/List ID and see a list of returned merge vars
// http://example.org/?mailchimp-test
function lm_newsletter_test() {
  if ( current_user_can('administrator') && isset($_REQUEST['mailchimp-test']) ) {
    include( LMNEWS_PATH . '/mailchimp-test.php' );
    echo 'wow';
    exit;
  }
}
add_action( 'init', 'lm_newsletter_test' );

// Add admin menus to the dashboard
function lm_newsletter_menu_init() {
	 add_submenu_page(
		'options-general.php',
		'Newsletter',
		'Newsletter',
		'manage_options',
		'limelight-newsletter', 
		'lm_newsletter_options'
	);
}
function lm_newsletter_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
  require_once( plugin_dir_path( __FILE__ ) . 'options.php' );
}
add_action( 'admin_menu', 'lm_newsletter_menu_init' );


function lm_newsletter_scripts() {
  wp_enqueue_script(
    'lm-newsletter',
    plugins_url('newsletter.js', __FILE__),
    array('jquery')
  );
  wp_enqueue_style(
    'lm-newsletter',
    plugins_url('newsletter.css', __FILE__)
  );
}
add_action('wp_enqueue_scripts', 'lm_newsletter_scripts');


/**
 * Adds LMNewsletterWidget widget.
 */
class LMNewsletterWidget extends WP_Widget {

	// Register widget with WordPress.
	public function __construct() {
		parent::__construct(
			'LMNewsletterWidget', // Base ID
			'Limelight Newsletter', // Name
			array( 'description' => 'Mailchimp newsletter widget created by Limelight Department.' ) // Args
		);
	}

	// Front-end display of widget.
	public function widget( $args, $instance ) {
		extract( $args );
    
		$title = apply_filters( 'widget_title', $instance['title'] );
		$text = $instance['text'];
    $list_id = get_option('lm_newsletter_list_id');
    $firstname = (!isset($instance['firstname']) || $instance['firstname'] ? 1 : 0);
    $lastname = (!isset($instance['lastname']) || $instance['lastname'] ? 1 : 0);
	
	$merge_field_text = isset($instance['merge_fields']) ? $instance['merge_fields'] : false;
    $merge_fields = lm_get_merge_fields( $merge_field_text );
    
    if ( !empty($instance['list_id']) ) $list_id = $instance['list_id'];
      
    $apikey = get_option('lm_newsletter_apikey');
    
    if ( !$list_id || !$apikey ) {
      $hidden_warning = get_option('lm_newsletter_hide_warning');
      
      if ( $hidden_warning ) return;
      
      if ( !empty($_REQUEST['newsletter_hide_warning']) ) {
        update_option('lm_newsletter_hide_warning', 1);
        return;
      }
      
      // If List ID or API Key is invalid, do not show the widget. Show an error widget only to admins.
      if ( current_user_can('administrator') ) {
      
        echo $before_widget;
          if ($title) echo $before_title, $title, $after_title;
          
          echo '<p>Warning: The API Key or List ID has not been entered. The newsletter widget will not be displayed. Please edit the <a href="'. esc_attr( admin_url('options-general.php?page=limelight-newsletter') ) .'">Newsletter Settings</a>.</p>';
          
          echo '<p><em>Normal visitors will not see this widget.</em></p>';
          
          echo '<p><a href="'. esc_attr(add_query_arg(array('newsletter_hide_warning'=>1))) .'" class="button">Hide Warning</a></p>';
        echo $after_widget;
      }
      return;
    }

		echo $before_widget;
    
    if ( !$firstname ) { add_filter('newsletter_hide_field-fname', '__return_true'); }
    if ( !$lastname ) { add_filter('newsletter_hide_field-lname', '__return_true'); }
    // ---------------------------------------------------------------------
    echo '<div class="newsletter_subscribe_widget">';
    
    if ($title) echo $before_title, $title, $after_title;
    
    if ($text) echo sprintf('<div class="newsletter-text">%s</div>', wpautop($text)); 
	
	$merge_field_str = lm_merge_fields_serialize( $merge_fields );
	
    echo do_shortcode('[limelight_newsletter list_id="'.$list_id.'" merge_fields="'. esc_attr($merge_field_str) .'"]');
    
    echo '</div>';
		
    // ---------------------------------------------------------------------
    if ( !$firstname ) { remove_filter('newsletter_hide_field-fname', '__return_true'); }
    if ( !$lastname ) { remove_filter('newsletter_hide_field-lname', '__return_true'); }
    
		echo $after_widget;
	}

	// Sanitize widget form values as they are saved.
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text'] = strip_tags( $new_instance['text'] );
		$instance['list_id'] = strip_tags( $new_instance['list_id'] );
		$instance['list_id'] = strip_tags( $new_instance['list_id'] );
		$instance['firstname'] = isset($new_instance['firstname']) ? 1 : 0;
		$instance['lastname'] = isset($new_instance['lastname']) ? 1 : 0;
		$instance['merge_fields'] = $new_instance['merge_fields'];

		return $instance;
	}


	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
    // Retrieve all of our fields from the $instance variable
    $fields = array('title', 'text', 'list_id', 'firstname', 'lastname', 'merge_fields');
    
    // Format each field into ID/Name/Value array
    foreach($fields as $name) {
      $fields[$name] = array(
        'id' => $this->get_field_id( $name ),
        'name' => $this->get_field_name( $name ),
        'value' => false,
      );
      
      if ( isset( $instance[$name] ) ) {
        $fields[$name]['value'] = $instance[$name];
      }
    }
    
    // Display the widget in admin dashboard:
    ?>
    
    <p>
      <label for="<?php esc_attr_e( $fields['title']['id'] ); ?>"><?php _e( 'Title:' ); ?></label>
      <input class="widefat" type="text"
        id="<?php esc_attr_e( $fields['title']['id'] ); ?>"
        name="<?php esc_attr_e( $fields['title']['name'] ); ?>"
        value="<?php esc_attr_e( $fields['title']['value'] ); ?>" />
    </p>
    
    <p>
      <label for="<?php esc_attr_e( $fields['text']['id'] ); ?>"><?php _e( 'Signup Text:' ); ?></label>
      <textarea class="widefat" rows="8" cols="20"
        id="<?php esc_attr_e( $fields['text']['id'] ); ?>"
        name="<?php esc_attr_e( $fields['text']['name'] ); ?>"><?php echo esc_textarea( $fields['text']['value'] ); ?></textarea>
    </p>
    
    <p><strong>Additional Options:</strong></p>
    
    <p>
      <label for="<?php esc_attr_e( $fields['firstname']['id'] ); ?>">
        <input type="checkbox" 
          name="<?php esc_attr_e( $fields['firstname']['name'] ); ?>"
          id="<?php esc_attr_e( $fields['firstname']['id'] ); ?>"
          <?php if ( $fields['firstname']['value'] || $fields['firstname']['value'] === false ) checked(true); ?>
          />
        Display first name field
      </label>
    </p>
    
    <p>
      <label for="<?php esc_attr_e( $fields['lastname']['id'] ); ?>">
        <input type="checkbox" 
          name="<?php esc_attr_e( $fields['lastname']['name'] ); ?>"
          id="<?php esc_attr_e( $fields['lastname']['id'] ); ?>"
          <?php if ( $fields['lastname']['value'] || $fields['lastname']['value'] === false ) checked(true); ?>
          />
        Display last name field
      </label>
    </p>
    
    <p>
      <label for="<?php esc_attr_e( $fields['list_id']['id'] ); ?>"><?php _e( 'List ID <em>(Leave blank for default)</em>:' ); ?></label>
      <input class="widefat" type="text"
        id="<?php esc_attr_e( $fields['list_id']['id'] ); ?>" 
        name="<?php esc_attr_e( $fields['list_id']['name'] ); ?>" 
        value="<?php esc_attr_e( $fields['list_id']['value'] ); ?>" />
    </p>
    
    <p>
      <label for="<?php esc_attr_e( $fields['merge_fields']['id'] ); ?>"><?php _e( 'Merge Fields <em>(Leave blank for default)</em>:' ); ?></label>
      <textarea class="widefat" rows="4" cols="20"
        id="<?php esc_attr_e( $fields['merge_fields']['id'] ); ?>"
        name="<?php esc_attr_e( $fields['merge_fields']['name'] ); ?>"><?php
			$merge_fields = lm_get_merge_fields( $fields['merge_fields']['value'] );
			
			if ( $merge_fields ) {
				$i = 0;
				foreach( $merge_fields as $k => $v ) {
					$i++;
					
					echo esc_textarea( $k ) . ' : ' . esc_textarea($v);
					
					if ( $i < count($merge_fields) ) echo "\n";
				}
			}
			?></textarea>
    </p>

    <?php
	}

} // class LMNewsletterWidget

function registerLMNewsletterWidget() {
	register_widget( "LMNewsletterWidget" );
}
add_action( 'widgets_init', 'registerLMNewsletterWidget' );



// [limelight-newsletter list_id="hello"]
function lm_newsletter_shortcode( $args ) {
  $list_id = get_option('lm_newsletter_list_id');
  if ( isset($args['list_id']) && $args['list_id'] != '' ) $list_id = $args['list_id'];
  
  $merge_fields = isset($args['merge_fields']) ? $args['merge_fields'] : 0;
  
  $firstname_value = true;
  if ( isset($args['first-name']) ) $firstname_value = $args['first_name'];
  else if ( isset($args['first_name']) ) $firstname_value = $args['first-name'];
  else if ( isset($args['firstname']) ) $firstname_value = $args['firstname'];
  
  $lastname_value = true;
  if ( isset($args['last-name']) ) $lastname_value = $args['last_name'];
  else if ( isset($args['last_name']) ) $lastname_value = $args['last-name'];
  else if ( isset($args['lastname']) ) $lastname_value = $args['lastname'];
  
  $was_submitted = (defined('LM_NEWSLETTER_SUCCESS') ? true : false);
  $submitted_successful = false;
  if ($was_submitted && LM_NEWSLETTER_SUCCESS == true) $submitted_successful = true;
    
  ob_start();
  
  echo '<div class="newsletter_subscribe_widget shortcode">';

  if (isset($args['title']) && $args['title']) echo '<h3 class="entry-title">', $args['title'], '</h3>';
 
  // If form was submitted successful, do nothing. Otherwise show form.
  if (!$was_submitted || ($was_submitted && !$submitted_successful)) {
    // Display newsletter signup form:
    global $newsletter_form_index;
    if ( empty($newsletter_form_index) ) $newsletter_form_index = 1;
    else $newsletter_form_index++;
    
    
    
    if ( !$firstname_value ) { add_filter('newsletter_hide_field-fname', '__return_true'); }
    if ( !$lastname_value ) { add_filter('newsletter_hide_field-lname', '__return_true'); }
		
    // ---------------------------------------------------------------------
    include( plugin_dir_path(__FILE__) . 'subscribe.php' );
    // ---------------------------------------------------------------------
	
    if ( !$firstname_value ) { remove_filter('newsletter_hide_field-fname', '__return_true'); }
    if ( !$lastname_value ) { remove_filter('newsletter_hide_field-lname', '__return_true'); }
  }


  // Display result title/message if necessary
  if ($was_submitted) {
    // Newsletter has been submitted:
    if (!$submitted_successful)
      echo '<div class="newsletter-response error">';
    else
      echo '<div class="newsletter-response success">';
    
    if (defined('LM_NEWSLETTER_TITLE'))  echo '<p class="result-title"><strong>', LM_NEWSLETTER_TITLE, '</strong></p>';
    if (defined('LM_NEWSLETTER_MESSAGE')) echo '<p class="result-message">', strip_tags(str_replace('<br', "\n\n<br", LM_NEWSLETTER_MESSAGE)), '</p>';
    
    echo '</div>';
  }
  echo '</div>';
  
  return ob_get_clean();
}
add_shortcode('limelight_newsletter', 'lm_newsletter_shortcode');



// Processes a $_REQUEST submission
// Returns array with [success:true|false, title:string, message:string]
function lm_newsletter_submit($ajax = false) {  
  $email = stripslashes($_REQUEST['email']);
  $fname = !empty($_REQUEST['fname']) ? stripslashes($_REQUEST['fname']) : '';
  $lname = !empty($_REQUEST['lname']) ? stripslashes($_REQUEST['lname']) : '';
  
  $list_id = get_option('lm_newsletter_list_id');
  if ( !empty($_REQUEST['list_id']) ) $list_id = stripslashes($_REQUEST['list_id']);
  
  $fields = array();
  if ( $fname ) $fields['FNAME'] = $fname;
  if ( $lname ) $fields['LNAME'] = $lname;
  
	$merge_data = isset($_REQUEST['lm_merge_fields']) ? stripslashes($_REQUEST['lm_merge_fields']) : false;
  
	if ( !$merge_data || (string) $merge_data === '0' ) {
		$merge_fields = lm_get_merge_fields();
	}else{
		$merge_data = lm_merge_fields_unserialize( $merge_data );
		$merge_fields = lm_get_merge_fields( $merge_data );
	}
  
	// Add user defined merge fields to our api request fields. Do not let these override defaults.
	if ( !empty($merge_fields) ) {
		foreach( $merge_fields as $k => $v ) {
			if ( isset($fields[$k]) ) continue;
			$fields[$k] = $v;
		}
	}
  
  $result = lm_newsletter_subscribe( $email, $fields, $list_id );
  
  if ($ajax) {
    echo json_encode($result);
    exit;
  }else{
    $success = (isset($result['success']) && $result['success']);
    
    define('LM_NEWSLETTER_SUCCESS', $success);
    define('LM_NEWSLETTER_TITLE', $result['title']);
    define('LM_NEWSLETTER_MESSAGE', $result['message']);
  }
}


function lm_newsletter_subscribe( $email, $fields, $list_id = null ) {
  if (!class_exists('MailChimp')) require_once('MailChimp-2.0.php');

  $api_key = get_option('lm_newsletter_apikey');
  if ( $list_id === null ) $list_id = get_option('lm_newsletter_list_id');
  
  // These args are for use in filters.
  $filter_args = array(
    'api_key' => &$api_key,
    'list_id' => &$list_id,
    'email' => &$email,
    'fields' => &$fields,
  );
  
  // You can filter the data here. The arguments are for reference/comparison only.
  $api_key  = apply_filters( 'newsletter-subscribe-api-key', $api_key, $filter_args );
  $list_id = apply_filters( 'newsletter-subscribe-list-id', $list_id, $filter_args );
  $email = apply_filters( 'newsletter-subscribe-email', $email, $filter_args );
  $fields = apply_filters( 'newsletter-subscribe-fields', $fields, $filter_args );
  
  $plugin_error = apply_filters( 'newsletter-plugin-error', false, $filter_args );
  
  $html = apply_filters( 'newsletter-subscribe-method', 'html' );
  
  if ( !$plugin_error ) {
    $api = new MailChimp($api_key);
    
    do_action( 'before_mc_api_subscribe', $api, $list_id, $email, $fields, $html );

    $args = array( 
      'id'            => $list_id, // ID of subscription list
      'email'         => array( 'email' => $email ), // Email Address to subscribe
      'merge_vars'    => $fields, // Array passed to Mailchimp
      'email_type'    => ($html == 'text') ? 'text' : 'html', // Default method of recieving emails
    );
    
    $result = $api->call( 'lists/subscribe', $args );
    
    do_action( 'after_mc_api_subscribe', $api, $list_id, $email, $fields, $html );
  }
  
  if ( $plugin_error ) {
    $error = array(
      'title' => 'Plugin Error:',
      'message' => 'An unspecified error occurred with a custom plugin. Your subscription was not added.'
    );
    
    if ( isset($plugin_error['title']) ) $error['title'] = $plugin_error['title'];
    if ( isset($plugin_error['message']) ) $error['message'] = $plugin_error['message'];
    
    if ( has_filter('lm_subscribe_error_response') ) $error = apply_filters( 'lm_newsletter_error_response', $error, $filter_args );
    
    do_action( 'limelight_newsletter_error', false, $email, $filter_args );
    
    return $error;
  
  }else if ( isset($result['status']) && $result['status'] == 'error' ){
    
    
    // if ( isset($result['code']) && isset($result['error']) ) {
      $errorCode = $result['code'];
      $errorMessage = $result['error'];
    // }else{
      // $errorCode = 'INVALID_RESPONSE';
      // $errorMessage = "An invalid response was returned by the MailChimp API.";
      // $errorMessage .= "\n\n<pre style=\"text-align: left; font-size: 14px; font-family: monospace; color: #444; background: #fff;\">" . print_r( $result, true ) . "</pre>";
    // }
    
    $the_email = sanitize_email($email);
  
    // Default message
    $error = array(
      'title' =>
        "Error {$errorCode}:",
      'message' =>
        $errorMessage
    );

    switch($errorCode) {
    // Server issues:
      case -98: // Timeout
        $error['message'] = "Request timed out, please try again later.";
        break;
      case 104: // Invalid API Key
        $error['message'] = "Server API key is invalid.";
        break;
      case 106: // Invalid App Key
        $error['message'] = "Server App key is invalid.";
        break;
      case 200: // Invalid List ID
        $error['message'] = "Server List ID is invalid.";
        break;
        
    // Subscriber issues:
      case 214: // Already Subscribed
        $error['message'] = 
          "\"{$the_email}\" is already subscribed to our newsletter.\n\n".
          "To change subscription settings, follow the link in any of our Newsletters.";
        break;
      case 502: // Invalid Email
        $error['title'] = "Email address is invalid";
        $error['message'] = "";
        break;
    }
    
    if ( has_filter('lm_subscribe_error_response') ) $error = apply_filters( 'lm_newsletter_error_response', $error, $filter_args );
    
    do_action( 'limelight_newsletter_error', false, $email, $filter_args );
    
    return $error;
    
  } else {
    $result = array(
      'title'   => 'Subscription Added!',
      'message' => 'You have been subscribed to our newsletter. Check your email for a confirmation message.',
      'success' => true 
    );
    
    if ( has_filter('lm_subscribe_success_response') ) $result = apply_filters( 'lm_subscribe_success_response', $result, $filter_args );
    
    do_action( 'limelight_newsletter_success', $result, $filter_args );
   
    return $result;
  }
}