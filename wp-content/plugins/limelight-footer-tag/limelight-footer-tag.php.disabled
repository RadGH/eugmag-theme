<?php
/*
Plugin Name: Limelight - Footer Tag
Version: 1.5
Plugin URI: http://www.limelightdept.com/
Description: Adds search engine relevance by optimizing the last 25 words on the page with a customizable Footer Tag. To display the footer tag, insert the following into your template: <code>&lt;?php do_action('limelight-footer-tag'); ?&gt;</code>. Format the tag with CSS with the selector <code>div.ldft</code>.
Author: Radley Sustaire
Author URI: mailto:radleygh@gmail.com

Copyright 2012 Limelight Department (radley@limelightdept.com)
For use by Limelight Department and affiliates, do not distribute
*/

define('LDFT_VERSION', '1.4');

add_action('init', 'ldft_check_needed_upgrade');
function ldft_check_needed_upgrade() {
  $prev = get_option('ldft-version', null);
  
  // If the "previous version" is not current, perform an update.
  // Versions prior to 1.3 did not have a version number stored.
  if ( $prev != LDFT_VERSION ) {
    ldft_upgrade_plugin( $prev, LDFT_VERSION );
    update_option( 'ldft-version', LDFT_VERSION );
  }
}

function ldft_upgrade_plugin( $prev, $current ) {
  global $wpdb;
  
  if ( $prev == null && $wpdb->get_var( "SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = 'ldft_footer_tag' LIMIT 1;" ) ) {
    // Versions 1.2b or lower previously installed. We did not store version number back then.
    $prev = '1.2b';
  }
  
  if ( preg_match('/^1\.(0|1|2|3)[a-z]?$/', $prev) ) {
    // Switch key names to start with underscore (hidden from custom fields) and delete any blank entries.
    $wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = '_ldft_disable' WHERE meta_key = 'ldft_disable';" );
    $wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = '_ldft_footer_tag' WHERE meta_key = 'ldft_footer_tag';" );
    $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_ldft_disable' AND meta_value = '';" );
    $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_ldft_footer_tag' AND meta_value = '';" );
  }
  define('LDFT_PREV_VERSION', $prev);
  add_action('admin_notices', 'ldft_updated_message');
}

function ldft_updated_message() {
  echo '<div class="updated success"><p><strong>Limelight Footer Tag:</strong> Plugin successfully upgraded to version '. esc_html(LDFT_VERSION) .' (Upgraded from version '. LDFT_PREV_VERSION .').</p></div>';
}

function ldft_show_tag() {
  global $wp_query;
  wp_reset_query();
  
  if ( is_404() ) return;
  
  $target_id = get_the_ID();
  if ( is_home() && get_option('page_for_posts') ) $target_id = get_option('page_for_posts');
  
  $tag = get_post_meta($target_id, '_ldft_footer_tag', true);
  $disable = get_post_meta($target_id, '_ldft_disable', true);
  
  // If the page is not a single page, it cannot have a footer tag assigned to  it. Use the queried object name as that tag, if available.
  // This is especially useful for categories.
  if ( !is_single() && !$tag ) {
    $object = get_queried_object();
    if ( $object && property_exists( $object, 'name' ) ) $tag = $object->name;
  }
  
  // Get custom post meta if possible, otherwise get page title if possible
  if ( $tag ) $label = $tag;
  else if ($v = get_the_title($target_id)) $label = $v;
  
  if ( !$disable ) {
    echo '<div class="ldft">';
    echo $label;
    echo '</div>';
  }
}
add_action('limelight-footer-tag', 'ldft_show_tag');

// Create a meta box above the Publish button
function ldft_box_create() {
  global $post;

  $placeholder = get_bloginfo('title');
  
  if ($post->ID) $placeholder = get_the_title($post->ID);
  
  $tag = get_post_meta($post->ID, '_ldft_footer_tag', true);
  $disable = get_post_meta($post->ID, '_ldft_disable', true);
  
  ?>
  <div class="misc-pub-section" id="ldft_section">
    
    <span class="ldft-tag">
      <input type="text" name="_ldft_footer_tag" id="_ldft_footer_tag" class="footer-tag text" placeholder="Footer Tag: <?php echo esc_attr( $placeholder ); ?>" <?php if ($tag) { ?>value="<?php echo esc_attr( $tag ); ?>"<?php } ?> />
    </span>
    
    <span class="ldft-disable">
      <input type="checkbox" name="_ldft_disable" id="_ldft_disable" class="footer-tag-disable checkbox regular-checkbox" <?php checked( $disable ); ?> />
      <label for="_ldft_disable"> Hide</label>
    </span>
    
    <input type="hidden" name="ldft-nonce" value="<?php echo wp_create_nonce( 'save-footer-tag' ); ?>" />
    
    <div style="clear: both;"></div>
    
  </div>
  <style type="text/css">
  #_ldft_footer_tag {
    width: 75%;
    margin-right: 0;
  }
  
  #ldft_section span.ldft-disable {
    float: right;
    margin-top: 5px;
  }
  </style>
  <script type="text/javascript">
  jQuery(function() {
    jQuery('#_ldft_disable').change(function() {
      if (jQuery(this).prop('checked')) {
        jQuery('#_ldft_footer_tag')
          .attr('disabled', 'disabled')
          .css('opacity', '0.75');
      }else{
        jQuery('#_ldft_footer_tag')
          .removeAttr('disabled')
          .css('opacity', '');
      }
    }).change();
  });
  </script>
  <?php
}
// Save this metabox section
function ldft_box_save( $post_id ) {
  if ( isset($_REQUEST['ldft-nonce']) && wp_verify_nonce( $_REQUEST['ldft-nonce'], 'save-footer-tag' ) ) {
    $tag = isset($_REQUEST['_ldft_footer_tag']) ? $_REQUEST['_ldft_footer_tag'] : false;
    $disable = isset($_REQUEST['_ldft_disable']) ? true : false;
    
    if ( $tag ) update_post_meta($post_id, '_ldft_footer_tag', $tag);
    else delete_post_meta( $post_id, '_ldft_footer_tag' );
    
    if ( $disable ) update_post_meta($post_id, '_ldft_disable', $disable);
    else delete_post_meta( $post_id, '_ldft_disable' );
  }
}
add_action( 'post_submitbox_misc_actions', 'ldft_box_create' );
add_action( 'save_post', 'ldft_box_save' );