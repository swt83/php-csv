# CSV

A PHP library for working w/ CSV files.

## Install

Normal install via Composer.

### Tags

- ``1.0`` for Laravel v4
- ``1.1`` is framework agnostic

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

// build from string
$csv = Travis\CSV::from_string($string);

// build from file
$csv = Travis\CSV::from_file($path);
```

You can do several things w/ a ``CSV`` object:

```php
// to array
$array = $csv->to_array();

// to file
$csv->to_file($path_to_file);

// to string
$string = $csv->to_string();

// to download w/ headers (Laravel example)
return \Response::make($csv->to_string(), 200, array(
    'content-type' => 'application/octet-stream',
    'content-disposition' => 'attachment; filename="'.$name.'"',
));
```
