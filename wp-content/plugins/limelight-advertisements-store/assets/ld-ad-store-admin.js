jQuery(function() {

	// Find time slot names that already have a value and make them readonly.
	jQuery('.acf-field-573b4964612f5')
		.find('td.acf-field-573b5378bc968 input')
		.filter(function() { return !(jQuery(this).val()  == ""); })
		.prop('readonly', true);

});