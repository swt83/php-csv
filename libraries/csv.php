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
     * @param   string  $delimiter
     * @param   string  $enclosure
     * @return  object
     */
    public static function from_file($path, $delimiter = ',', $enclosure = '"')
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
            while ($fields = fgetcsv($input, 0, $delimiter, $enclosure))
            {
                // if first row...
                if ($row === 1)
                {
                    // spin headers...
                    $count = 0;
                    foreach ($fields as $field)
                    {
                        // get column name
                        $name = Str::slug($field ? $field : uniqid(), '_');

                        // check exists...
                        if (in_array($name, $columns))
                        {
                            $count++;
                            $name .= '_'.$count;
                        }

                        // save column name
                        $columns[] = $name;
                    }
                }
                
                // if NOT first row...
                else
                {
                    // combine
                    $temp = array_combine($columns, $fields);

                    // if no error...
                    if ($temp)
                    {
                        // add to rows
                        $rows[] = $temp;
                    }
                    else
                    {
                        // do not add row
                        #die(var_dump($fields));
                    }
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
     * Legacy method.
     *
     * @param   string  $path
     * @param   string  $delimiter
     * @param   string  $enclosure
     * @return  object
     */
    public static function open($path, $delimiter = ',', $enclosure = '"')
    {
        // alias
        return static::from_file($path, $delimiter = ',', $enclosure = '"');
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
        // open file
        $fp = fopen($path, 'w');

        // write columns
        if (sizeof($this->columns) > 0)
        {
            fputcsv($fp, $this->columns, ',', '"');
        }
        
        // write rows
        foreach ($this->rows as $fields)
        {
            fputcsv($fp, $fields, ',', '"');
        }

        // close file
        fclose($fp);

        // return exists
        return file_exists($path);
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
     * @param   boolean  $table_already_exists
     * @param   boolean  $clear_existing_records
     * @return  void
     */
    public function to_database($table = null, $table_already_exists = false, $clear_existing_records = false)
    {
        // This method requires the use of an additional bundle
        // called "DBUtil", found at: http://github.com/swt83/laravel-dbutil

        // if no pre-existing table defined...
        if (!$table_already_exists)
        {
            // make columns for table
            $columns = array();
            foreach ($this->columns as $c)
            {
                $columns[$c] = array(
                    'type' => 'string',
                    'length' => 200,
                );
            }

            // if table already exists...
            if (DBUtil::exists($table))
            {
                // delete
                DBUtil::drop($table);
            }

            // make table
            DBUtil::make($table, $columns);
        }
        else
        {
            // if clear existing records...
            DBUtil::truncate($table);
        }
        
        // foreach row...
        foreach ($this->rows as $value)
        {
            // add to table
            DB::table($table)->insert($value);
        }
    }

}