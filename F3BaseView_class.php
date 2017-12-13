<?php

/**
 * The Fat Free Framework Base View class to use along the View Generator class.
 * This library can be imported and initialized using the following command (with $f3 being the Fat Free Framework variable):
 * 
 * 
 *
 * This class is only meant to be extended by child view classes (although it can be instantiated solely. The View Generator class will automatically do that, and issue the respective get and post views using the framework variable for each child class extending this one.
*/

class F3BaseView {

	public $view_content_varname = 'content'; // Default name for framework content variable. Useful if templating with f3.
	
	public $view_templates_prefix = 'f3vg'; // Prefix for template file names. With default value it would be f3vg_templatename.htm

	public $f3; // The Fat Free Framework variable pointer.

	public function __construct($view_content_varname = null, $view_templates_prefix = null) {

		// Check if the view content variable name is given as argument, in wich case should replace the default.
		if(!empty($view_content_varname)) {
			$this -> view_content_varname = $view_content_varname;
		}

		if(!empty($view_templates_prefix)) {
			$this -> view_templates_prefix = $view_templates_prefix;
		}

		$this -> f3 = \Base::instance();

	}

	public function get_view($view_name = null, $return_html = false) {

		if($view_name == null){
			// Issue a notice on PHP Error log.
			return $this -> with_notice(false, 'The view name given in post_view method is not valid.');	
		}

		if($return_html) {
			// TODO: Open file; Extract html, replace variables. Return content in variable.
		} else {
			$this -> f3 -> set($this -> view_content_varname, $this -> view_templates_prefix . '_' $view_name);
		}

		return true; // Could be a good idea to return useful developer information in array context.

	}

	public function post_view($view_name = null, $post_content = null) {

		if($view_name == null) {
			return $this -> with_notice(false, 'The view name given in post_view method is not valid.');
		}

		$this -> f3 -> reroute($view_name);

		return true;

	}

	public function with_notice($return_value, $message) {

		if(!empty($message) && is_string($message)) {
		// Issue a notice on PHP Error log.
			$message = __CLASS__ . ': ' . $message . PHP_EOL;
		}

		return $return_value;

	}
}