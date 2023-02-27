jQuery(function() {
  jQuery("div.newsletter_subscribe_widget form").each(function() {
    var $form = jQuery(this);
    var $submitbtn = $form.find('input[type=submit]');
    
    $submitbtn.data('button_text', $submitbtn.val());
    
    $submitbtn.click(function(e) {
      var list_id = $form.find('input[name=list_id]').val();
      var lm_merge_fields = $form.find('input[name=lm_merge_fields]').val();
      var email = $form.find('input[name=email]').val();
      var error = "";
      
      if (email.length < 3 || email == 'Email Address') error = "Please enter a valid email";
      
      var $fname = $form.find('input[name=fname]');
      if ( $fname.length ) {
        var firstname = $fname.val();
        if (firstname.length < 3 || firstname == 'First Name') error = "Please enter your first name";
      }
      
      var $lname = $form.find('input[name=lname]');
      if ( $lname.length ) {
        var lastname = $lname.val();
        if (lastname.length < 3 || lastname == 'Last Name') error = "Please enter your last name";
      }
      
      if (error !== "") {
        alert(error);
        $submitbtn.removeAttr('disabled').removeClass('disabled');
        e.preventDefault();
        return  false;
      }
      
      $form.siblings('div.success, div.error').remove();
      $submitbtn.attr('disabled', 'disabled').addClass('disabled').val('Please wait...');
      
      var time = new Date().getTime();
      
      var data_obj = {
        email: email,
        json: 1,
        lm_newsletter: 'subscribe',
      };
      
      if ( list_id ) data_obj.list_id = list_id;
      if ( lm_merge_fields ) data_obj.lm_merge_fields = lm_merge_fields;
      if ( firstname ) data_obj.fname = firstname;
      if ( lastname ) data_obj.lname = lastname;
      
      // add custom fields to ajax submission
      if ( $form.find('.custom-fields').length ) {
        $form.find('.custom-fields').find('input, textarea, select').each(function() {
          var val = jQuery(this).val();
          var name = jQuery(this).attr('name');
          
          if ( val && name && typeof data_obj[name] == 'undefined' ) {
            data_obj[name] = val;
          }
        });
      }
      
      jQuery.ajax({
        type: 'POST',
        url: '?ms=' + time,
        data: data_obj,
        complete: function(data) {
          if (!data || !data.responseText) {
            alert("No response from receiving end, please try again");
            $submitbtn.removeAttr('disabled').removeClass('disabled').val( $submitbtn.data('button_text') );
            return false;
          }
        
          try {
            var result = jQuery.parseJSON(data.responseText);
          } catch(e) {
            alert("Server Error: Unexpected result, server responded with:\n\n" + data.responseText);
            $submitbtn.removeAttr('disabled').removeClass('disabled').val( $submitbtn.data('button_text') );
            return false;
          }
          
          // Invalid result?
          if (!result) {
            alert("Error submitting newsletter application.");
            $submitbtn.removeAttr('disabled').removeClass('disabled').val( $submitbtn.data('button_text') );
            return false;
          }
          
          // Display our result text:
          
          var $resultdiv = jQuery("<div></div>").addClass('newsletter-response');
          
          if (result.success == true) {
            jQuery(this).parents('form').first().trigger('subscribe-success');
            $resultdiv.addClass('success');
          }else{
            jQuery(this).parents('form').first().trigger('subscribe-failure');
            $resultdiv.addClass('error');
          }
          
          if (result.title) {
            $title = jQuery('<p></p>').addClass('result-title');
            $title.html(result.title);
            $title.wrapInner( jQuery('<strong></strong>' ) );
            $resultdiv.append( $title );
          }
          
          if (result.message) {
            $p = jQuery('<p></p>').addClass('result-message');
            $p.html(result.message);
            $resultdiv.append( $p );
          }
          
          $form.after($resultdiv);
          $submitbtn.removeAttr('disabled').removeClass('disabled').val( $submitbtn.data('button_text') );
          // End result display
          
          if (result.success === true) {
            $form[0].reset();
            $form.remove();
          }
        }
      });
      
      e.preventDefault();
      return  false;
    });
  });
});