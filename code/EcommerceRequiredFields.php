<?php
/**
 * This class is a duplicate of sapphire's CustomRequiredFields.
 * Sadly, there were bugs in 2.0.2's release of this file.  Until we've launched 2.0.3, the ecommerce module will have to make use
 * of this.
 */
class EcommerceRequiredFields extends RequiredFields{
	protected $required;
		
	/**
	 * Pass each field to be validated as a seperate argument
	 * __construct()'s arguments needs to be an array
	 * @mpeel Unfortunately the line 'function __construct(array $required)' breaks older versions of PHP 5, so remove the forcing of array
	 */
	function __construct($required) {
		$this->required = $required;
	}
	
	/**
	 * Creates the client side validation from form fields
	 * which is generated at the header of each page 
	 */
	function javascript() {
		$fields = $this->form->Fields();
		$code = '';
		foreach($fields as $field){
			//if the field type has some special specific specification for validation of itself
			$valid = $field->jsValidation();
			if($valid){
				$code .= $valid;
			}
		}
		if(is_array($this->required)){

			foreach($this->required as $field) {
				if(is_array($field)) {
				 	if($field['js']) $code .= $field['js'] . "\n";

				} else if($fields->dataFieldByName($field)) {
					//Extra tabs for output tabbing :-)
					$code .= "						require('$field');\n";
				}
			}
		}else{
			USER_ERROR("CustomRequiredFields::requiredfields is not set / not an array",E_USER_WARNING);
		}
		return $code;
	}
	
	/**
	 * Creates the server side validation from form fields
	 * which is executed on form submission
	 */
	function php($data) {
		$fields = $this->form->Fields();
		$valid = true;
		foreach($fields as $field) {
			$valid = ($field->validate($this) && $valid);
		}
		if($this->required){
			foreach($this->required as $key => $field) {
				if(is_array($field)) {
					if($field['php']) eval($field['php']);

				} else if($fields->dataFieldByName($field)) {
					// if an error is found, the form is returned.
					if(!$data[$field]) {
						$this->validationError($field,"$field is required","required");
						return false;
					}
				}
			}	
		}
		return $valid;
	}
	
	/**
	 * allows you too add more required fields to this object after construction.
	 */
	function appendRequiredFields($requiredFields){
		$this->required = array_merge($this->required,$requiredFields->getRequired());
	}
}

?>
