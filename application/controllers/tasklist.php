<?php

/**
 * Tasklist Controller class
 *
 * Extends {@link http://codeigniter.com/user_guide/general/controllers.html Codeigniter Controller}.
 * Create next URI: index, add, edit, complete
 * @see CI_Controller
 *
 * @author Francisco Jose Alvarez de Diego [www.devaddiction.com]
 */
class Tasklist extends CI_Controller {

	protected $days = NULL;
	protected $months = NULL;
	protected $years = NULL;
	
	/**
	 * @const DONE_CLASS done
	 */
	const DONE_CLASS = 'done';

	/**
	 * @const EXPIRED_CLASS expired
	 */
	const EXPIRED_CLASS = 'expired';

	/**
	 * @const DEFAULT_CLASS item
	 */
	const DEFAULT_CLASS = 'item';

	/**
	 * @const MAX_YEARS 5
	 */
	const MAX_YEARS = 5;
	
	/**
	 * True if there are errors
	 * @param boolean
	 * @access protected
	 */
	protected $_errors = FALSE;
	
	
	/**
	 * Initialize Tasklist dependencies
	 * @inheritdoc
	 */
	function __construct() {
		parent::__construct();
		$this -> _initializeDependencies();
	}


	/**
	 * Get the data from the model and load the view
	 * @access public
	 */
	public function index() {
		$data['errors'] = $this -> _getErrors();
		$data['tasks'] = $this -> _getTasks();
		$data['days'] = $this -> _getDays();
		$data['months'] = $this -> _getMonths();
		$data['years'] = $this -> _getYears();

		$this -> load -> view('index', $data);
	}


	/**
	 * Validate and add a new task. Redirect to index after creation
	 * @access public
	 */
	public function add() {
		// Validation rules
		$this -> form_validation -> set_rules('task_name', 'Task Name', 'required');
		$this -> form_validation -> set_rules('day', 'Date', 'callback_check_date');

		if ($this -> form_validation -> run() == FALSE) {
			$this -> _errors = TRUE;
		} else {
			// Construct date from POST fragments
			$due_date = implode('-', array(
				$this -> input -> post('year'), 
				$this -> input -> post('month'), 
				$this -> input -> post('day')
			));
			
			$due_date .= ' 23:59:59';

			// Create data for database insert
			$data = array('task_name' => $this -> input -> post('task_name'), 
						  'task_due' => $due_date);

			// Use the insert method of the tasks model
			$this -> tasks -> insertTask($data);
		}
		$this -> index();
	}
	

	/**
	 * Validate and add a new task. Redirect to index after creation
	 * @access public
	 */
	public function edit($id, $newValue) {
		$newValue = $this->_escapeHtml($newValue);
		$this -> tasks -> editTask($id, $newValue);
	}


	/**
	 * Mark a task as complete
	 * @param integer $id
	 */
	public function complete($id) {
		$this -> tasks -> markTaskComplete($id);
		$this -> index();
	}
	
	
	/**
	 * Initialize model, helper and library
	 * @access protected
	 */
	protected function _initializeDependencies() {
		$this -> load -> model('Task_model', 'tasks');
		$this -> load -> helper('form', 'url');
		$this -> load -> library('form_validation');
	}
	
	
	/**
	 * Return if the form has error
	 * @return boolean
	 * @access protected
	 */
	protected function _getErrors() {
		return $this -> _errors;
	}


	/**
	 * Return a range of days
	 * @return integer
	 * @access protected
	 */
	protected function _getDays() {
		return range(1, 31);
	}

	/**
	 * Return a range of months
	 * @return integer
	 * @access protected
	 */
	protected function _getMonths() {
		return range(1, 12);
	}


	/**
	 * Return a range of years, from this year until MAX_YEARS
	 * @return integer
	 * @access protected
	 */
	protected function _getYears() {
		return range(date('Y'), date('Y') + self::MAX_YEARS);
	}


	/**
	 * Get tasks from DB and process it
	 * @return array
	 * @access protected
	 */
	protected function _getTasks() {
		$tasks = $this -> tasks -> getCurrentTasks();

		foreach ($tasks as $index => $task) {
			$tasks[$index]['task_name'] = $this -> _escapeHtml($task['task_name']);
			$tasks[$index]['task_due'] = $this -> _friendlyDate($task['task_due']);
			$tasks[$index]['class'] = $this -> _updateClasses($task);
		}
		return $tasks;
	}


	/**
	 * Format a date
	 * @param string $date 
	 * @return string
	 * @access protected
	 */
	protected function _friendlyDate($date = FALSE) {
		if (!$date) {
			return '';
		}
		$timestamp = strtotime($date);
		$friendly_date = date('jS M, Y', $timestamp);
		return $friendly_date;
	}


	/**
	 * Escape a string to be displayed in the view
	 * @param string $string 
	 * @return string
	 * @access protected
	 */
	protected function _escapeHtml($string = FALSE) {
		if (!$string)
			return '';
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}


	/**
	 * Return a string with the proper classes
	 * @param array $task
	 * @return string
	 * @access protected
	 */
	protected function _updateClasses($task) {
		$class = $this -> _expiredClass($task['task_due']);
		if ($this -> _completedTask($task['complete'])) {
			$class .= ' ' . self::DONE_CLASS;
		}
		if (empty($class)) {
			$class = self::DEFAULT_CLASS;
		}
		return $class;
	}

	/**
	 * Check if a task needs to be marked as overdue based on date
	 * @param string $date 
	 * @return string
	 * @access protected
	 */
	protected function _expiredClass($date) {
		$class = '';
		if (strtotime($date) < time()) {
			$class = self::EXPIRED_CLASS;
		}
		return $class;
	}

	/**
	 * Check if a task needs to be marked as completed based
	 * @param string $dateTask 
	 * @return boolean
	 * @access protected
	 */
	 protected function _completedTask($dateTask) {
		return !is_null($dateTask);
	}

	/**
	 * Callback function to validate date
	 * @return boolean
	 * @access protected
	 */
	 function check_date() {
		$day = $this -> input -> post('day');
		$month = $this -> input -> post('month');
		$year = $this -> input -> post('year');
		$error = false;

		// Check if date entries are numbers
		if (!ctype_digit($day) || !ctype_digit($month) || !ctype_digit($year)) {
			$error = true;
			$this -> form_validation -> set_message('check_date', 
				'Sorry, something went wrong with the date information.');
		}
		// Check date is a real date ie not 30 Feb
		if (!checkdate($month, $day, $year)) {
			$error = true;
			$this -> form_validation -> set_message('check_date', 
				'That date is not valid.');
		}
		// Check date is in the future
		// If the date is today that is OK so I'll compare current timestamp with timestamp 
		// for the end of the day
		if (mktime(23, 59, 59, $month, $day, $year) < time()) {
			$this -> form_validation -> set_message('check_date', 
				"The date you've given is in the past.");
			$error = true;
		}

		return !$error;
	}

}
