# CSV

A PHP library for working w/ CSV files.

## Install

Normal install via Composer.

## Usage

You basically are building an object that contains all the data, and then doing something w/ the object:

```php
// build from scratch
$csv = new Travis\CSV;
$csv->setColumns(['Header1', 'Header2']);
$csv->addRow(['foo', 'bar']);
$csv->addRow(['foo', 'bar']);
$csv->addRow(['foo', 'bar']);

// build from scratch en mass
$rows = [
    ['foo', 'bar'],
    ['foo', 'bar'],
];
$csv = new Travis\CSV;
$csv->setColumns(['Header1', 'Header2']);
$csv->setRows($rows);

// build from string
$csv = Travis\CSV::fromString($string);

// build from file
$csv = Travis\CSV::fromFile($path);
```

You can do several things w/ a ``CSV`` object:

```php
// get column labels
$labels = $csv->getColumns();

// pull all the values in a column
$values = $csv->getColumn('email');

// to array
$array = $csv->toArray();

// to file
$csv->toFile($path_to_file);

// to string
$string = $csv->toString();

// to download w/ headers (Laravel example)
return \Response::make($csv->toString(), 200, array(
    'content-type' => 'application/octet-stream',
    'content-disposition' => 'attachment; filename="'.$name.'"',
));
```
