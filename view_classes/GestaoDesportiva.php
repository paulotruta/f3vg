<?php

/**
 * The Fat Free Framework Base View class to use along the View Generator class.
 * This library can be imported and initialized using the following command (with $f3 being the Fat Free Framework variable):
 * 
 * 
 *
 * This class is only meant to be extended by child view classes (although it can be instantiated solely. The View Generator class will automatically do that, and issue the respective get and post views using the framework variable for each child class extending this one.
*/

class GestaoDesportiva extends BragaCupBaseView {

	public $view_name = 'GestaoDesportiva';

	public function __construct($view_content_varname = null, $view_templates_prefix = null) {

		// $view_content_varname = 'content'; // Default name for framework content variable. Useful if templating with f3.
		// $view_templates_prefix = 'f3vg'; // Override view template prefixes if needed.
		include('classes/Calendar.php');
		include('classes/GameField.php');
		include('classes/GameTime.php');

		parent::__construct($view_content_varname, $view_templates_prefix);

	}

	public function get_view($view_name = 'GestaoDesportiva', $return_html = false) {

		// (...) Do view logic here (...)

		if(!$this -> f3 -> get('is_authorized') || !$this -> f3 -> get('is_admin')) {
			// Informações do calendário geral para equipas inscritas no torneio.
			error_log($this -> with_notice(true, "The user is not authorized to see this content. Showing not authorized page.", false));
			return parent::get_view('NotAuthorized');
		}


		$calendar_obj = new Calendar();
		$calendar_list = $calendar_obj -> get_list();

		$calendars = array();
		foreach ($calendar_list as $calendar_key => $calendar_info) {
			$calendars[] = new Calendar($calendar_info['ID']);
		}

		$gamefield_obj = new GameField();
		$gamefield_list = $gamefield_obj -> get_list();

		$game_fields = array();
		foreach ($gamefield_list as $gamefield_key => $gamefield_info) {
			$game_fields[] = new GameField($gamefield_info['ID']);
		}

		$gametime_obj = new GameTime();
		$gametimes_list = $gametime_obj -> get_list();

		$game_times = array();
		foreach ($gametimes_list as $gametime_key => $gametime_info) {
			$game_times[] = new GameTime($gametime_info['ID']);
		}

		// Load list of calendars created
		$this -> f3 -> set('calendarios', $calendars);
		$this -> f3 -> set('campos', $game_fields);
		$this -> f3 -> set('horarios', $game_times);

		// Put available categories as frontend variable.
		$this -> f3 -> set('escaloes_disponiveis', get_escaloes_disponiveis());

		return parent::get_view($this -> view_name);

	}

	public function post_view($view_name = 'GestaoDesportiva', $post_content = null) {

		if(!$this -> f3 -> get('is_authorized') || !$this -> f3 -> get('is_admin')) {
			// Informações do calendário geral para equipas inscritas no torneio.
			error_log($this -> with_notice(true, "The user is not authorized to post here. Showing not authorized page.", false));
			return parent::get_view('NotAuthorized');
		}

		switch ($this -> f3 -> get('POST.action')) {
			case 'adicionarCalendario':
				
				$this -> add_calendar();

				break;

			case 'apagarCalendario':

				$this -> delete_calendar();

				break;

			case 'adicionarCampo':
				
				$this -> add_gameField();

				break;

			case 'apagarCampo':

				$this -> delete_gameField();

				break;

			case 'adicionarHorario':

				$this -> add_gameTime();

				break;

			case 'apagarHorario':

				$this -> delete_gameTime();

				break;


			
			default:
				$this -> f3 -> set('message_code', 19);
				break;
		}


		// (...) Do view post logic here (...)

		// Tip: To route to a different GET route than the original, change $this -> view_name with the wanted view destination in the return.
		return parent::post_view($this -> view_name);

	}

	protected function add_calendar() {
		// Time to create a new calendar!
		// Create an instance of calendar in order to check if it already exists, and only if not add the new one
		$calendar_obj = new Calendar();

		// Check if calendar was correctly created.
		if(!$calendar_obj -> exists($this -> f3 -> get('POST.categoria'))) {

			$calendar_obj = new Calendar(array('categoria' => $this -> f3 -> get('POST.categoria')));
			$this -> f3 -> set('message_code', 92);
		} else {
			$this -> f3 -> set('message_code', 93);
		}
	}

	protected function delete_calendar(){

		$calendar_obj = new Calendar($this -> f3 -> get('POST.id_calendario'));
		$calendar_obj -> delete();
		$this -> f3 -> set('message_code', 98);

	}

	protected function add_gameField() {

		// Time to create a new game field!
		$field_obj = new GameField();

		// Check if game field already exists before adding another...
		if(!$field_obj -> exists($this -> f3 -> get('POST.nome_campo'))) {

			$picture_url = '';
					// Extract picture url for the upload in order to correctly populate the GameField object.

			$filename = "uploads/campos/";

			if (!file_exists($filename)) {
				mkdir($filename, 0777);
			}

			$this -> f3 ->set('UPLOADS', $filename); // don't forget to set an Upload directory, and make it writable!

			$overwrite = false; // set to true, to overwrite an existing file; Default: false
			$slug = true; // rename file to filesystem-friendly version
			$web = \Web::instance();
			$files = $web->receive(
				function($file,$formFieldName){

			        if($file['size'] > (10 * 1024 * 1024)){ // if bigger than 2 MB
			         	return false; // this file is not valid, return false will skip moving it
			         }

			        // everything went fine, hurray!
			        return true; // allows the file to be moved from php tmp dir to your defined upload dir

			    },
			    $overwrite,
			    $slug
			);

			$not_uploaded = 0;

			foreach ($files as $key => $value) {
				if(!$value){
					$not_uploaded++;
				}
				else{
					$picture_url = $key;
				}
			}

			$field_obj = new GameField(array(
				'nome' => $this -> f3 -> get('POST.nome_campo'),
				'arenas' => $this -> f3 -> get('POST.numero_campos'),
				'imagem' => $picture_url
			));

			$this -> f3 -> set('message_code', 94);

		} else {
			$this -> f3 -> set('message_code', 95);
		}

	}

	protected function delete_gameField(){
		$gamefield_obj = new GameField($this -> f3 -> get('POST.id_campo'));
		$gamefield_obj -> delete();
		$this -> f3 -> set('message_code', 99);
	}

	protected function add_gameTime() {

		// Time to create a new schedule!
		// Create an instance of GameTime in order to check if it already exists, and only if not add the new one
		$time_obj = new GameTime();

		// Check if calendar was correctly created.
		if(!$time_obj -> exists($this -> f3 -> get('POST.horario'))) {
			$time_obj = new GameTime(array('horario' => $this -> f3 -> get('POST.horario')));
			$this -> f3 -> set('message_code', 96);
		} else {
			$this -> f3 -> set('message_code', 97);
		}

	}

	protected function delete_gameTime() {
		$gametime_obj = new GameTime($this -> f3 -> get('POST.id_horario'));
		$gametime_obj -> delete();
		$this -> f3 -> set('message_code', 100);
	}
}