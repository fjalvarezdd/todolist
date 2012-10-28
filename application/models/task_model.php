<?php

/**
 * Tasklist Model Class
 *
 * Extends {@link http://codeigniter.com/user_guide/general/models.html Codeigniter Model}.
 * Create method for add, get, edit and remove
 * @see CI_Model
 *
 * @author Francisco Jose Alvarez de Diego [www.devaddiction.com]
 */
class Task_model extends CI_Model {
	
	/**
	 * @inheritdoc
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	 * Returns an array of tasks to be displayed
	 * @access public
	 */
	function getCurrentTasks() {
		$data = array();
		$sql = "SELECT * FROM tasks";
		$query = $this->db->query($sql);
		foreach($query->result() as $row) {
			$data[] = array(
				'id'        => $row->id,
				'task_name' => $row->task_name,
				'task_due'  => $row->task_due,
				'complete'  => $row->complete
			);
		}
		return $data;
	}


	/**
	 * Insert a task into the database
	 * @access public
	 */
	public function insertTask($data) {
		$this->db->insert('tasks', $data);
	}


	/**
	 * Edit a task. Replace the task_name with a new one
	 * @param integer $id
	 * @param string $newValue
	 * @access public
	 */
	public function editTask($id, $newValue) {
		$data = array('task_name' => $newValue);
		$this->db->update('tasks', $data, array('id' => $id));
	}


	/**
	 * Update a task to mark it as complete
	 * @param integer $id
	 * @access public
	 */
	public function markTaskComplete($id) {
		$data = array('complete' => date('Y-m-d h:i:s'));
		$this->db->update('tasks', $data, array('id' => $id));
	}

}