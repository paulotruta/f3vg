<?php

/**
 * The Fat Free Framework View Generator.
 * This library can be imported and initialized using the following command (with $f3 being the Fat Free Framework variable):
 * 
 * ""
 * 		$view_generator_obj = new F3ViewGenerator(&$f3);
 * 			
 * ""
 *
 * On construction, it will automatically issue the respective get and post views using the framework variable.
*/

class F3ViewGenerator {

	public $view_content_varname = 'content'; // Default name for framework content variable. Useful if templating with f3.
	public $view_classes_folder_location = 'view_classes'; // TODO: Exception in .gitignore for this folder.
	public $view_templates_prefix = 'f3vg'; // Prefix for template file names. With default value it would be f3vg_templatename.htm
	public $error_404_route = '/404';
	public $generated_views_data;

	public $f3; // Instance of the Fat Free Framework variable.

	public function __construct($error_404_route, $view_content_varname, $view_templates_prefix, $view_classes_folder_location) {

		// Check if the view content variable name is given as argument, in wich case should replace the default.
		if(!empty($error_404_route)) {
			$this -> error_404_route = $error_404_route;
		}

		if(!empty($view_content_varname)) {
			$this -> view_content_varname = $view_content_varname;
		}

		if(!empty($view_templates_prefix)) {
			$this -> view_templates_prefix = $view_templates_prefix;
		}

		if(!empty($view_classes_folder_location)) {
			$this -> view_classes_folder_location = $view_classes_folder_location;
		}


		$this -> f3 = \Base::instance();

		$this -> generate_view_routes();

	}

	/**
	 * Generates routes on the framework based on the generated views data.
	 * ATTENTION: Make sure there are no routes with the same name already declared by the framework, as they will be replaced.
	*/
	public function generate_view_routes() {

		// Extract view names present in the $view_classes_folder_location and loop through importing them, generating the respective get and post routes by calling the view class get_view and post_view functions respectively.
		// Take note that every view that uses this generator will be available through a base url given as construct parameter.

		$this -> generated_view_data = array();
		$view_classes = scandir(dirname(__FILE__) . '/' . $this -> view_classes_folder_location);

		unset($view_classes[0]); // Unset "." folder;
		unset($view_classes[1]); // Unset ".." folder;

		// Import all the classes.
		foreach ($view_classes as $view_class_key => $view_class_filename) {

			include($this -> view_classes_folder_location . '/' . $view_class_filename);

			$view_class_name = str_replace('.php', '', $view_class_filename);
			$this -> generated_views_data[] = array(
				'view_file' => $this -> view_classes_folder_location . '/' . $view_class_filename,
				'view_classname' => $view_class_name,
				'view_template' => 'ui/' . $this -> view_templates_prefix . '_' . $view_class_name . '.htm',
			);
		}

		// Save current information to the framework.
		$this -> f3 -> set('view_classes', $view_classes);
		$this -> f3 -> set('views_data', $this -> generated_views_data);

		// Declare and dynamically get the GET generic view.
		$this -> f3 -> route('GET /' . $this -> view_templates_prefix . '/@view',
			function() {
				// Reget the instance and associated information. Then check for route existance and issue the corresponding view.
				$f3 = \Base::instance();
				$view_classes = $f3 -> get('view_classes');
				$target_class_name = $f3 -> get('PARAMS.view');
				$target_class_filename = $target_class_name . '.php';
				if(in_array($target_class_filename, $view_classes)) {
					// Dynamically issue a new class for the approved view.
					$view_class_instance = new $target_class_name();
					$view_class_instance -> get_view();
				}
				else {


					// 404. Reroute.

					$f3 -> reroute($this -> error_404_route);
				}

			}
		);

		// Declare and dynamically get the POST view.
		$this -> f3 -> route('POST /' . $this -> view_templates_prefix . '/@view',
			function() {

				error_log("ENTERED POST ROUTING SYSTEM");

				// Reget the instance and associated information. Then check for route existance and issue the corresponding view.
				$f3 = \Base::instance();
				$view_classes = $f3 -> get('view_classes');
				$target_class_name = $f3 -> get('PARAMS.view');
				$target_class_filename = $target_class_name . '.php';

				if(in_array($target_class_filename, $view_classes)) {
					error_log("ISSUING CLASS INSTANCE");
					// Dynamically issue a new class for the approved view.
					$view_class_instance = new $target_class_name();
					$view_class_instance -> post_view();
				}
				else {
					// 404. Reroute.
					error_log("NOT FOUND REROUTING");
					error_log(var_dump($target_class_filename));
					$f3 -> reroute($this -> error_404_route);
				}
			}
		);

		return $this -> generated_views_data;

		// Done. Correct view should be printed to the screen by now.
		// TODO... Properly unit test this shiaaaaat ;)

	}
	
	public function view_exists($view_name = null) {

		// TODO: Check if view exists in the generated views data.
		if(!empty($this -> generated_views_data['views'][ $view_name ])) return true;

		return false;

	}

	/**
	 * Provides the generated views data (also returned on construct) if available.
	 * If not available, first run generate_view_rules() func.
	*/ 
	public function get_views_data() {

		return ((!empty($this -> generated_views_data)) ? $this -> generated_views_data : false);

	}

}