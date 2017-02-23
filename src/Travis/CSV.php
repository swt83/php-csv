<?php

namespace Travis;

class CSV
{
    /**
     * Store column headers.
     *
     * @var $columns    array
     */
    public $columns = [];

    /**
     * Store row values.
     *
     * @var $row    array
     */
    public $rows = [];

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
    public static function make()
    {
        $class = __CLASS__;
        return new $class;
    }

    /**
     * Build object loaded w/ data from provided string.
     *
     * @param   string  $string
     * @param   boolean $first_row_as_headers
     * @param   string  $delimiter
     * @param   string  $enclosure
     * @return  object
     */
    public static function fromString($string, $fist_row_as_headers = true, $delimiter = ',', $enclosure = '"')
    {
        // path
        $path = storage_path().'/csvfromstring';

        // save
        file_put_contents($path, $string);

        // alias
        $object = static::from_file($path, $fist_row_as_headers, $delimiter, $enclosure);

        // cleanup
        unlink($path);

        // return
        return $object;
    }

    /**
     * Build object loaded w/ data from remote file.
     *
     * @param   string  $path
     * @param   boolean $first_row_as_headers
     * @param   string  $delimiter
     * @param   string  $enclosure
     * @return  object
     */
    public static function fromUrl($path, $fist_row_as_headers = true, $delimiter = ',', $enclosure = '"')
    {
        // looks like fopen() works with URLs!
        return static::from_file($path, $fist_row_as_headers, $delimiter, $enclosure);
    }

    /**
     * Build object loaded w/ data from local file.
     *
     * @param   string  $path
     * @param   boolean $first_row_as_headers
     * @param   string  $delimiter
     * @param   string  $enclosure
     * @return  object
     */
    public static function fromFile($path, $fist_row_as_headers = true, $delimiter = ',', $enclosure = '"')
    {
        // fix mac csv issue
        ini_set('auto_detect_line_endings', true);

        // open file...
        if ($input = @fopen($path, 'r'))
        {
            $columns = array();
            $rows = array();

            // spin rows...
            $row = 1;
            while ($fields = fgetcsv($input, 0, $delimiter, $enclosure))
            {
                if ($fist_row_as_headers)
                {
                    // if first row...
                    if ($row == 1)
                    {
                        // spin headers...
                        $count = 0;
                        foreach ($fields as $field)
                        {
                            // get column name
                            $name = static::slug(trim($field ? $field : uniqid()), '_');

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
                    else
                    {
                        // if columns DO NOT match fields...
                        if (sizeof($columns) !== sizeof($fields))
                        {
                            // die
                            trigger_error('Column and field sizes must match.');
                        }

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
                }
                else
                {
                    // combine
                    $temp = $fields;

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
     * Set column headers (first row values).
     *
     * @param   array    $array
     * @return  void
     */
    public function setColumns($array)
    {
        $this->columns = $array;

        // if rows are set...
        if (sizeof($this->rows) > 0)
        {
            // if no error...
            if (sizeof($this->rows[0]) == sizeof($this->columns))
            {
                // update all rows w/ new columns...
                foreach ($this->rows as $key => $value)
                {
                    // prevent errors...
                    if (sizeof($this->columns) < sizeof($value))
                    {
                        // drop leftovers
                        $value = array_slice($value, 0, sizeof($this->columns));
                    }
                    elseif (sizeof($this->columns) < sizeof($value))
                    {
                        // do nothing
                    }

                    // update columns
                    $this->rows[$key] = array_combine($this->columns, $value);
                }
            }
            else
            {
                trigger_error('Columns array must be proper size.');
            }
        }
    }

    /**
     * Set all row values.
     *
     * @param   array    $array
     * @return  void
     */
    public function setRows($array)
    {
        $this->rows = $array;
    }

    /**
     * Set individual row values.
     *
     * @param   array    $array
     * @return  void
     */
    public function addRow($array)
    {
        $this->rows[] = $array;
    }

    /**
     * Return table columns.
     *
     * @return  array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Return table rows.
     *
     * @return  array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Convert CSV to array.
     *
     * @return  array
     */
    public function toArray()
    {
        // return
        return $this->rows;
    }

    /**
     * Convert CSV to string.
     *
     * @return  string
     */
    public function toString()
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
    public function toFile($path)
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
     * Return a string converted to a slug.
     *
     * @param   string  $str
     * @return  string
     */
    protected static function slug($str)
    {
        return strtolower(preg_replace('/[^A-Za-z0-9-]+/', '_', $str));
    }

    /**
     * Return all values in a column.
     *
     * @param   string  $column
     * @return  array
     */
    public function getColumn($column)
    {
        $records = [];
        foreach ($this->getRows() as $key => $value)
        {
            if (isset($value[$column]))
            {
                if ($value[$column])
                {
                    $records[] = $value[$column];
                }
            }
        }

        return $records;
    }
}