<?php
/**
 * This project is licensed under the terms of the MIT license,
 * you can read more in the LICENSE file.
 * 
 * LogrDB
 * LogrDB.php
 *
 * @version	0.0.0
 * @author	Zack Urben
 * @contact zackurben@gmail.com
 * @github	https://github.com/zackurben/LogrDB
 */

/**
 * TODO:
 *
 * Create insert function, for data insertion.
 * Create count function, to count results returned.
 * Create order function, to order results returned.
 */

/**
 * LogrDB object definition.
 */
class LogrDB {
	
	/**
	 * @var $db
	 *
	 * String representation of the loaded file.
	 */
	protected $db;
	
	/**
	 * @var $data
	 *
	 * The contents of the file parsed into memory (JSON decoded array).
	 */
	protected $data;
	
	/**
	 * LogrDB object initilization.
	 *
	 * @param $file
	 *   String representation of the file name.
	 *
	 * @param $create
	 *   Boolean flag to determine if the LogrDB file should be created.
	 */
	public function __construct($file, $create = false) {
		if(is_file($file) && $create === false) {
			$this->db = $file;
			
			$temp = file_get_contents($this->db);
			$temp = explode("\n", $temp);
			
			$data_array = array();
			foreach($temp as $row) {
				array_push($data_array, json_decode($row, true));
			}
			
			$this->data = $data_array;
		} elseif($create === true) {			
			if(is_file($file)) {
				throw new Exception('A file already exists with the name, ' . $file . 
					', but the $create flag was set to true. Please specify' . 
					' a new name or turn the $create flag to false.');
			} else {
				file_put_contents($file, '');
				$this->db = $file;
				$this->data = array();
			}
		} else {
			throw new Exception('LogrDB file not found!');
		}
	}
	
	/**
	 * Select data rows from the LogrDB object.
	 *
	 * @param $param
	 *   The search terms in key => value order; where key is the row name, and value is the search term.
	 *   (The wildcard, %,  may be used in the search term)
	 *
	 * @return $result_rows
	 *   An array, with associative keys, of the resulting search.
	 */
	public function select(array $param = array()) {
		// if param list exists, continue with select
		if(count($param) > 0) {
			
			// check if each param exists as an array key
			$valid = true;
			foreach($param as $key => $val) {
				if(!array_key_exists($key, $this->data[0])) {
					// at-least one search key did not exist, throw an error.		
					$invalid_key = $key;
					$valid = false;
				}
			}

			if($valid) {			
				// read each row to determine result_rows
				$result_rows = array();
				foreach($this->data as $row) {
					// by default each row is a result, until proven otherwise
					$result = true;
					foreach($param as $key => $val) {
						if(($row[$key] != $val) && (stripos($val, "%") === false)) {
							// (rows[key] != val) AND (wildcard is not present)
							$result = false;
							break;
						} elseif(stripos($val, "%") !== false) {
							// wildcard is present
							$temp = str_replace("%", "", $val);
							if(stripos($row[$key], $temp) === false) {
								$result = false;
								break;
							}
						}
					}
					
					// if valid result, add to result list
					if($result) {
						array_push($result_rows, $row);
					}
				}
			} else {
				throw new Exception('The key, ' . $invalid_key . ', does not exist in the data set.');
			}
		} else {
			throw new Exception('The parameter list for select(), must not be empty.');
		}
		
		return $result_rows;
	}
}

?>
