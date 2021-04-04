<?php defined('SYSPATH') or die('No direct script access.');

ini_set('auto_detect_line_endings', TRUE);

class Parser_CSV {

	protected $_result = array();
	protected $_config = array();

	public function __construct($file=null, array $config=array()) {
		$this->_config = $config;
		$file and $this->load($file);
	}

	public function load($file) {
		$file = fopen($file, 'r');
		$header = array();
		$row = 0;
		while (($line = fgetcsv($file)) !== FALSE) {
			if( ! $header)
				$header = $line;
			else {
				$this->_result[$row] = array_combine($header, array_pad($line, count($header), null));
			}
			$row++;
		}
		fclose($file);
		return $this;
	}

	public function result() {
		return $this->_result;
	}

	public function unique($file=null) {
		$unique = array();
		foreach($file ? $file : $this->_result as $i => $row) {
			foreach($row as $column => $value) {
				$val = trim($value);
				if( ! empty($val))
					$unique[$column][] = $val;
			}
		}
		foreach($unique as $column => $values) {
			$unique[$column] = array_unique($values);
			sort($unique[$column]);
		}
		return $unique;
	}
}