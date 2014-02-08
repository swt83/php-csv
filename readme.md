# CSV

A PHP library for working w/ CSV files.

The ``to_database()`` method requires Laravel, while all other methods use native PHP.

## Install

Normal install via Composer.

### Provider

Register your service provider in ``app/config/app.php``:

```php
'Travis\CSV\Provider'
```

## Usage

You basically are building an object that contains all the data, and then doing something w/ the object:

```php
// build from scratch
$csv = new Travis\CSV;
$csv->columns(array('Header1', 'Header2'));
$csv->row(array('foo', 'bar'));
$csv->row(array('foo', 'bar'));
$csv->row(array('foo', 'bar'));

// build from scratch en mass
$rows = array(
    array('foo', 'bar'),
    array('foo', 'bar'),
);
$csv = new Travis\CSV;
$csv->columns(array('Header1', 'Header2'));
$csv->rows($rows);

// build from file
$csv = Travis\CSV::from_file($path_to_file);
```

You can do several things w/ a ``CSV`` object:

```php
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