<?php

/**
 * A LaravelPHP package for working w/ CSV files.
 *
 * @package    CSV
 * @author     Scott Travis <scott.w.travis@gmail.com>
 * @link       http://github.com/swt83/laravel-csv
 * @license    MIT License
 */

class CSV {

    /**
     * Store column headers.
     *
     * @var $columns    array
     */
    public $columns = array();

    /**
     * Store row values.
     *
     * @var $row    array
     */
    public $rows = array();
    
    /**
     * Newline character.
     *
     * @var $newline    string
     */
    public static $newline = "\n";

    /**
     * Static constructor method.
     *
     * @return  object
     */
    public static function forge()
    {
        $class = __CLASS__;
        return new $class;
    }
    
    /**
     * Static constructor based on file.
     *
     * @param   string  $path
     * @return  object
     */
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

    /**
     * Set column headers (first row values).
     *
     * @param   array    $array
     * @return  void
     */
    public function columns($array)
    {
        $this->columns = $array;
    }
    
    /**
     * Set all row values.
     *
     * @param   array    $array
     * @return  void
     */
    public function rows($array)
    {
        $this->rows = $array;
    }
    
    /**
     * Set individual row values.
     *
     * @param   array    $array
     * @return  void
     */
    public function row($array)
    {
        $this->rows[] = $array;
    }
    
    /**
     * Convert CSV to string.
     *
     * @return  string
     */
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
            $csv .= static::$newline;
        }
        
        // rows
        foreach($this->rows as $row)
        {
            foreach ($row as $field)
            {
                $csv .= '"'.addslashes($field).'",';
            }
            $csv .= static::$newline;
        }
        
        // return
        return $csv;
    }
    
    /**
     * Convert CSV to file.
     *
     * @param   string  $path
     * @return  boolean
     */
    public function to_file($path)
    {
        // return
        return File::put($path, $this->to_string());
    }
    
    /**
     * Convert CSV to download (send headers and stream).
     *
     * @param   string  $name
     * @return  object
     */
    public function to_download($name)
    {
        // response
        return Response::make($this->to_string(), 200, array(
            'content-type' => 'application/octet-stream',
            'content-disposition' => 'attachment; filename="'.$name.'"',
        ));
    }
    
    /**
     * Convert CSV to database table.
     *
     * @param   string  $table
     * @return  void
     */
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

}