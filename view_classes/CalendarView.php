<?php

/**
 * The Fat Free Framework Base View class to use along the View Generator class.
 * This library can be imported and initialized using the following command (with $f3 being the Fat Free Framework variable):
 * 
 * 
 *
 * This class is only meant to be extended by child view classes (although it can be instantiated solely. The View Generator class will automatically do that, and issue the respective get and post views using the framework variable for each child class extending this one.
*/

class CalendarView extends BragaCupBaseView {

	public $view_name = 'CalendarView';

	public function __construct($view_content_varname = null, $view_templates_prefix = null) {

		// $view_content_varname = 'content'; // Default name for framework content variable. Useful if templating with f3.
		// $view_templates_prefix = 'f3vg'; // Override view template prefixes if needed.

		parent::__construct($view_content_varname, $view_templates_prefix);

	}

	public function get_view($view_name = 'CalendarView', $return_html = false) {

		// (...) Do view logic here (...)

		if(!$this -> f3 -> get('is_authorized')) {
			// Informações do calendário geral para equipas inscritas no torneio.
		}

		if(!$this -> f3 -> get('is_admin')) {

		}

		if(false){
			error_log($this -> with_notice(true, "The user is not authorized to see this content. Showing not authorized page.", false));
			return parent::get_view('NotAuthorized');
		}


		return parent::get_view($this -> view_name);

	}

	public function post_view($view_name = 'CalendarView', $post_content = null) {

		// (...) Do view post logic here (...)
	


		// Tip: To route to a different GET route than the original, change $this -> view_name with the wanted view destination in the return.
		return parent::post_view($this -> view_name);

	}
}