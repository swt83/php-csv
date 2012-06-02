<?php

/**
 * A LaravelPHP package for working w/ CSV files.
 *
 * @package    CSV
 * @author     Scott Travis <scott.w.travis@gmail.com>
 * @link       http://github.com/swt83/laravel-csv
 * @license    MIT License
 */

class CSV
{
	public $columns = array();
	public $rows = array();
	
	public $newline = "\n";

	public static function forge()
	{
		$class = __CLASS__;
		return new $class;
	}

	public function columns($array)
	{
		$this->columns = $array;
	}
	
	public function rows($array)
	{
		$this->rows = $array;
	}
	
	public function row($array)
	{
		$this->rows[] = $array;
	}
	
	public function build()
	{
		$csv = '';
		
		// labels
		if (!empty($this->columns))
		{
			foreach ($this->columns as $label)
			{
				$csv .= '"'.addslashes($label).'",';
			}
			$csv .= $this->newline;
		}
		
		// rows
		foreach($this->rows as $row)
		{
			foreach ($row as $field)
			{
				$csv .= '"'.addslashes($field).'",';
			}
			$csv .= $this->newline;
		}
		
		// return
		return $csv;
	}
	
	public function to_file($path)
	{
	
	}
	
	public function to_download($name)
	{
		// response
		return Response::make($this->build(), 200, array(
			'content-type' => 'application/octet-stream',
			'content-disposition' => 'attachment; filename="'.$name.'"',
		));
	}
}