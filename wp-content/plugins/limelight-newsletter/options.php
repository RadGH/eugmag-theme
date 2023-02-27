<?php
$updated = false;
if (isset($_REQUEST['lm_newsletter_action'])) {
  if ($_REQUEST['lm_newsletter_action'] == 'update') {
  
    update_option( 'lm_newsletter_apikey', stripslashes( $_REQUEST['apikey']) );
    update_option( 'lm_newsletter_list_id', stripslashes( $_REQUEST['list_id']) );
    update_option( 'lm_newsletter_merge_fields', stripslashes( $_REQUEST['merge_fields']) );
    
    $updated = true;
  }
}
 
$apikey = get_option('lm_newsletter_apikey');
$list_id = get_option('lm_newsletter_list_id');
$merge_fields = lm_get_merge_fields();

?>
<h2>Limelight - Newsletter</h2>

<?php if ($updated) { ?>
<div id="message" class="updated"><p>Newsletter settings updated.</p></div>
<?php } ?>

<form enctype="multipart/form-data" method="post" action="" class="media-upload-form type-form validate">

<table class="form-table">
	<tbody>

	<tr>
		<th><label for="apikey">API Key</label></th>
		<td><input name="apikey" type="text" id="apikey" value="<?php echo esc_attr($apikey); ?>" class="regular-text"></td>
	</tr>

	<tr>
		<th><label for="list_id">Default List ID</label></th>
		<td><input name="list_id" type="text" id="list_id" value="<?php echo esc_attr($list_id); ?>" class="regular-text"></td>
	</tr>

	<tr>
		<th><label for="merge_fields">Merge Fields <em>(Optional)</em></label></th>
		<td>
			<textarea name="merge_fields" id="merge_fields" style="width: 350px;" cols="40" rows="4"><?php
			if ( $merge_fields ) {
				$i = 0;
				foreach( $merge_fields as $k => $v ) {
					$i++;
					
					echo esc_textarea( $k ) . ' : ' . esc_textarea($v);
					
					if ( $i < count($merge_fields) ) echo "\n";
				}
			}
			?></textarea>
			<p class="description">Merge fields should be listed as <code>key : Value</code>.<br>One per line. The key must match the Mailchimp merge field ID exactly.</p>
		</td>
	</tr>

	</tbody>
</table>
<p class="submit">
  <input type="hidden" name="lm_newsletter_action" value="update" />
  <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
</p>
</form>


<p>Display the form with a shortcode (list_id is optional, overrides the Default List ID):</p>
<pre class="code">[limelight_newsletter list_id="your-list-id"]</pre>

<p><a href="#" class="button button-secondary" onclick="jQuery('#newsletter-custom-fields').show(); jQuery(this).parent('p').hide(); return false;">Add custom fields</a></p>

<div id="newsletter-custom-fields">
<h3>Adding new fields for Limelight Newsletter (Developers Only)</h3>

<p>To add new fields to the Mailchimp form, use the action hook <code>newsletter-custom-field-display</code> with two arguments: <code>$_index, $list_id</code>. The $_index should be appended to field IDs and label "for" attributes to avoid duplicate IDs when multiple forms are used.</p>

<pre class="code">do_action( 'newsletter-custom-field-display', $_index, $list_id );
<em>source: subscribe.php:53</em></pre>

<hr/>

<p>Next, you want to send your custom field when the plugin subscribes the user. Do this with the filter <code>newsletter-subscribe-fields</code> which has two arguments: <code>$fields, $filter_args</code>. The $fields variable is an array which may have "FNAME" and "LNAME" keys, while $filter_args contains other information about the subscription which may or may not be useful. Add your custom field(s) to the $fields variable, where the key is the name.</p>

<pre class="code">apply_filters( 'newsletter-subscribe-fields', $fields, $filter_args );
<em>source: limelight-newsletter.php:316</em></pre>

<hr/>

<p>Optionally, if you want to use a required field you may abort the subscription process. Do this by using the action <code>newsletter-plugin-error</code>, and return an array with the two keys <code>title</code> and <code>message</code>, which will be shown to the user. If you return false during the action, the subscription will continue normally.</p>

<pre class="code">apply_filters( 'newsletter-plugin-error', false, $filter_args );
<em>source: limelight-newsletter.php:318</em></pre>
</div>

<style type="text/css">
#newsletter-custom-fields {
  display: none;
  max-width: 560px;
  background: #FFF;
  padding: 10px 25px;
}
#newsletter-custom-fields code {
  background: #F8F8F8;
  background: rgba(0, 0, 0, 0.02);
}
#newsletter-custom-fields pre {
  padding: 3px 5px;
  background: #EAEAEA;
  background: rgba(0, 0, 0, 0.07);
  overflow: auto;
}
</style>