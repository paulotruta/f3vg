<?php

/**
 * A Base view for the BragaCup project, extending f3vg BaseView class.
 * It allows to have into account if the user is or not in the administration screen and specify layout options accordingly.
*/

class BragaCupBaseView extends F3BaseView {

	public $subcontent_view_varname = 'subcontent';

	public function __construct($view_content_varname = null, $view_templates_prefix = null) {

		parent::__construct($view_content_varname, $view_templates_prefix);

	}

	public function get_view($view_name = null, $return_html = false) {

		if($this -> f3 -> get('is_admin')) {

			$this -> f3 -> set($this -> subcontent_view_varname, $this -> view_templates_prefix . '_' . $view_name . '.' . $this -> view_templates_extension);
			$this -> f3 -> set($this -> view_content_varname, 'backoffice_layout.htm');
			echo Template::instance()->render($this -> template_render_filename);

		} else {
			return parent::get_view($view_name, $return_html);
		}

	}


}