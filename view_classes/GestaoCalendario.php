<?php

/**
 * The Fat Free Framework Base View class to use along the View Generator class.
 * This library can be imported and initialized using the following command (with $f3 being the Fat Free Framework variable):
 * 
 * 
 *
 * This class is only meant to be extended by child view classes (although it can be instantiated solely. The View Generator class will automatically do that, and issue the respective get and post views using the framework variable for each child class extending this one.
*/

class GestaoCalendario extends BragaCupBaseView {

	public $view_name = 'GestaoCalendario';

	public function __construct($view_content_varname = null, $view_templates_prefix = null) {

		// $view_content_varname = 'content'; // Default name for framework content variable. Useful if templating with f3.
		// $view_templates_prefix = 'f3vg'; // Override view template prefixes if needed.
		include('classes/Calendar.php');
		include('classes/GameField.php');
		include('classes/GameTime.php');

		parent::__construct($view_content_varname, $view_templates_prefix);

	}

	public function get_view($view_name = 'GestaoCalendario', $return_html = false) {

		// (...) Do view logic here (...)

		// Load the associated calendar
		$calendar_obj = new Calendar($this -> f3 -> get('GET.id'));
		if( $calendar_obj -> is_defined() ) {
			
			$this -> f3 -> set('valid_calendar', true);
			$this -> f3 -> set('calendar_obj', $calendar_obj);

			$team_list = $calendar_obj -> get_team_list();
			$this -> f3 -> set('team_list', $team_list);

			$number_of_series = count($team_list) / $calendar_obj -> categorias_por_serie;
			if($number_of_series < 1) $this -> f3 -> set('not_enough_teams', true);

			//var_dump('Numero de equipas neste escalao: ' . count($team_list) . PHP_EOL);
			//var_dump('Numero de series neste calendario: ' . $number_of_series);

			if($calendar_obj -> is_generated()) {
				// Load current information about series and populate variables.
				$series_info = $calendar_obj -> get_series_info();
			} else {
				$series_info = $calendar_obj -> generate_serie_placements();
			}

			$this -> f3 -> set('series_info', $series_info);

		} else {
			$this -> f3 -> set('valid_calendar', false);
		}

		if(!$this -> f3 -> get('is_authorized') || !$this -> f3 -> get('is_admin')) {
			// Informações do calendário geral para equipas inscritas no torneio.
			error_log($this -> with_notice(true, "The user is not authorized to see this content. Showing not authorized page.", false));
			return parent::get_view('NotAuthorized');
		}


		

		return parent::get_view($this -> view_name);

	}

	public function post_view($view_name = 'GestaoCalendario', $post_content = null) {

		if(!$this -> f3 -> get('is_authorized') || !$this -> f3 -> get('is_admin')) {
			// Informações do calendário geral para equipas inscritas no torneio.
			error_log($this -> with_notice(true, "The user is not authorized to post here. Showing not authorized page.", false));
			return parent::get_view('NotAuthorized');
		}

		switch ($this -> f3 -> get('POST.action')) {
			case 'modificarSerie':
				
				$team_id_to_replaced = $this -> f3 -> get('POST.equipa_a_substituir');
				$team_id_to_replace = $this -> f3 -> get('POST.nova_equipa_na_serie');

				$calendar_id = $this -> f3 -> get('POST.id_calendario');

				$calendar_obj = new Calendar($calendar_id);

				if(!$calendar_obj -> is_defined() || !$calendar_obj -> is_generated()) {
					$this -> f3 -> set('message_code', 101);
				} else {
					$calendar_obj -> switch_teams($team_id_to_replaced, $team_id_to_replace);
					$this -> f3 -> set('message_code', 102);
				}

				$this -> f3 -> set('GET.id', $calendar_obj -> ID);

				break;
			
			default:
				$this -> f3 -> set('message_code', 19);
				break;
		}


		// (...) Do view post logic here (...)

		// Tip: To route to a different GET route than the original, change $this -> view_name with the wanted view destination in the return.
		return parent::post_view($this -> view_name);

	}

	
}