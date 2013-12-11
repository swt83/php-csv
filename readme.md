# CSV

A PHP library for working w/ CSV files.

## Install

Some methods are designed for use in Laravel.

### Provider

Register your service provider in ``app/config/app.php``:

```php
'Travis\CSV\Provider'
```

You may also wish to add an alias to remove the namespace:

```php
'CSV' => 'Travis\CSV'
```

## Usage

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
$csv = CSV::from_file($path_to_file);
```

You can do several things w/ a ``CSV`` object:

```
// to array
$array = $csv->to_array();

// to string
$string = $csv->to_string();

// to download (sends headers)
$csv->to_download();

// to file
$csv->to_file($path_to_file);

// to database
$csv->to_database($name_of_table, $table_already_exists = false, $clear_existing_records = false);
```