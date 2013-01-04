# CSV for LaravelPHP #

I work w/ CSV files all the time.  Built this class to help me out.

## Install ##

Normal bundle install.

## Usage ##

You basically are building an object that contains all the data, and then doing something w/ the object:

```
// build from scratch
$csv = new CSV;
$csv->columns(array('Header1', 'Header2'));
$csv->row(array('foo', 'bar'));
$csv->row(array('foo', 'bar'));
$csv->row(array('foo', 'bar'));

// build from scratch en mass
$rows = array(
    array('foo', 'bar'),
    array('foo', 'bar'),
);
$csv = new CSV;
$csv->columns(array('Header1', 'Header2'));
$csv->rows($rows);

// build from file
$csv = CSV::open($path_to_file);
```

You can do several things w/ a ``CSV`` object:

```
// to string
$string = $csv->to_string();

// to download (sends headers)
$csv->to_download();

// to file
$csv->to_file($path_to_file);

// to database
$csv->to_database($name_of_table);
```

Note that the ``to_database()`` method will automatically construct the table based on the column names.