<?php
global $newsletter_form_index;
$_index = $newsletter_form_index;

if ( !isset($list_id) ) $list_id = '';
if ( !isset($merge_fields) ) $merge_fields = 0;

$newsletter_fields = array(
  'fname' => array(
    'type' => 'text',
    'class' => 'text',
    'placeholder' => 'First Name',
    'label' => 'First Name:',
  ),
  'lname' => array(
    'type' => 'text',
    'class' => 'text',
    'placeholder' => 'Last Name',
    'label' => 'Last Name:',
  ),
  'email' => array(
    'type' => 'email',
    'class' => 'text',
    'placeholder' => 'Email',
    'label' => 'Email:',
  ),
);

$newsletter_fields = apply_filters( 'newsletter_fields', $newsletter_fields, $list_id );
?>
<form action="" method="POST" class="newsletter-form">
  <input type="hidden" name="json" value="0"/>
  <input type="hidden" name="lm_newsletter" value="subscribe"/>
  <input type="hidden" name="list_id" value="<?php echo esc_attr( $list_id ); ?>"/>
  <input type="hidden" name="lm_merge_fields" value="<?php echo esc_attr($merge_fields); ?>" />

  <?php
  foreach ($newsletter_fields as $key => $field) {
    // Allow plugins to skip fields by using: add_filter('newsletter_hide_field-fname', '__return_true');
    if ( apply_filters('newsletter_hide_field-' . $key, false) ) continue;
    
    $field_id = esc_attr('lm_newsletter_' . $key . '-' . $_index);
    
    echo sprintf(
      '<div class="newsletter-field field-%s newsletter-%s-field">',
      esc_attr($key),
      esc_attr($key)
    );
    
    if ( $field['label'] ) echo sprintf(
      '<label for="%s">%s</label>',
      $field_id,
      $field['label']
    );
    
    if ( $field['label'] ) echo sprintf(
      '<input type="%s" placeholder="%s" name="%s" id="%s" class="%s" />',
      esc_attr($field['type']),
      esc_attr($field['placeholder']),
      esc_attr($key),
      $field_id,
      esc_attr( implode(' ', (array) $field['class']) )
    );
    
    echo '</div>';
  }
  ?>
  
  <?php 
  ob_start();
  do_action( 'newsletter-custom-field-display', $_index, $list_id );
  $fields = ob_get_clean();
  
  if ( $fields ) echo '<div class="custom-fields">', $fields, '</div>';
  ?>

  <?php if ( !apply_filters('newsletter_hide_submit', false) ): ?>
  <div class="newsletter-submit-field">
    <input class="submit button" type="submit" value="<?php echo apply_filters('newsletter_submit_text', "Subscribe"); ?>">
  </div>
  <?php endif; ?>
</form>