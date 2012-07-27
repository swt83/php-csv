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
	
	public function __construct()
	{
	
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
	
	public function to_string()
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
		return Response::make($this->to_string(), 200, array(
			'content-type' => 'application/octet-stream',
			'content-disposition' => 'attachment; filename="'.$name.'"',
		));
	}
	
	public function to_database($table = null)
	{
		// if no table, build one
		if (!$table)
		{
			$table = time();
			$db = new Laravel\Database\Schema\Table($table);
		    $db->create();
		    $db->increments('id');
			foreach ($this->columns as $value)
			{
				$db->string($value, 100);
			}
			Schema::execute($db);
		}
		
		// insert rows
		foreach ($this->rows as $key => $value)
		{
			DB::table($table)->insert($value);
		}
	}
	
	public static function open($path)
	{
		// fix mac csv issue
		ini_set("auto_detect_line_endings", true);
		
		// open file...
		if ($input = @fopen($path, 'r'))
		{
			$columns = array();
			$rows = array();
			
			// spin rows...
			$row = 1;
		    while ($fields = fgetcsv($input))
		    {
		    	// if first row...
		    	if ($row === 1)
		    	{
		    		// spin headers...
		    		foreach ($fields as $field)
		    		{
		    			// slug headers, blanks not allowed
		    			$columns[] = Str::slug($field ? $field : uniqid(), '_');
		    		}
				}
				
				// if NOT first row...
				else
				{
					$rows[] = array_combine($columns, $fields);
				}
				$row++;
			}
			
			// close file
			fclose($input);
			
			// build object
			$class = __CLASS__;
			$object = new $class;
			$object->columns = $columns;
			$object->rows = $rows;
			return $object;
		}
		else
		{
			return false;
		}
	}
}