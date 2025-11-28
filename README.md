# pdoIO

A simple PHP class for performing database operations using PDO with array-based query building.

## Description

pdoIO is a lightweight PHP wrapper around PDO (PHP Data Objects) that allows you to perform common database operations (SELECT, INSERT, UPDATE, DELETE) using associative arrays instead of writing raw SQL strings. This approach makes it easier to build dynamic queries programmatically and reduces the risk of SQL injection when used properly.

The class supports SQLite and MySQL databases, and provides additional utility methods for pagination and row counting.

## Requirements

- PHP 5.1 or higher
- PDO extension enabled
- A supported database (SQLite or MySQL)

## Installation

Simply include the `pdoIO.php` file in your project:

```php
include('pdoIO.php');
```

## Usage

### Setup

First, establish a PDO connection to your database:

```php
// For SQLite
$pdo = new PDO('sqlite:database.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// For MySQL
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'username', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

Then create a pdoIO instance:

```php
$pdoio = new PDOIO($pdo);
```

### SELECT Queries

Build SELECT queries using an associative array:

```php
$query = [
    'SELECT' => ['id', 'name', 'email'], // Optional: defaults to *
    'FROM' => 'users',
    'WHERE' => [
        ['col' => 'status', 'op' => '=', 'val' => 'active'],
        'word' => 'AND',
        ['col' => 'age', 'op' => '>', 'val' => 18]
    ],
    'ORDER BY' => ['col' => 'name', 'dir' => 'ASC'],
    'LIMIT' => 10,
    'OFFSET' => 0
];

$results = $pdoio->select($query);
```

WHERE conditions support logical operators ('AND', 'OR') using the 'word' key.

### INSERT Queries

```php
$insertData = [
    'TABLE' => 'users',
    'DATA' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'created_at' => date('Y-m-d H:i:s')
    ]
];

$userId = $pdoio->insert($insertData);
```

### UPDATE Queries

```php
$updateData = [
    'TABLE' => 'users',
    'DATA' => [
        'name' => 'Jane Doe',
        'updated_at' => date('Y-m-d H:i:s')
    ],
    'WHERE' => [
        'id' => 1
    ]
];

$pdoio->update($updateData);
```

### DELETE Queries

```php
$deleteQuery = [
    'TABLE' => 'users',
    'WHERE' => [
        'id' => 1
    ],
    'LIMIT' => 1 // Optional: defaults to 1 for safety
];

$pdoio->delete($deleteQuery);
```

### Utility Methods

#### Get a single row by ID

```php
$user = $pdoio->getById('users', 1);
```

#### Get total count of records matching a query

```php
$total = $pdoio->getTotal($query);
```

#### Get number of pages for pagination

```php
$numPages = $pdoio->getnumPages($query, 10); // 10 items per page
```

#### Get page numbers array for pagination

```php
$pages = $pdoio->getPages($query, 10, 5, 1); // per page, max buttons, current page
```
Returns an array of page numbers.

getPages($query, $maxperpage = 10, $maxbuttons = 10, $currentpage = null)

The Parameters are:
* the query array (the same one you are using to get the results)
* records per page
* the max number of buttons in the pagination (so that you avoid having hundreds of buttons)
* the current page

If you use max number of buttons you must also insert the page number

## API Reference

### Constructor

- `__construct(PDO $pdo)`: Initialize with a PDO instance

### Methods

- `select(array $query): array` - Execute SELECT query
- `insert(array $insertQuery): int` - Execute INSERT query, returns last insert ID
- `update(array $updateQuery): bool` - Execute UPDATE query
- `delete(array $deleteQuery): bool` - Execute DELETE query
- `getById(string $table, int $id): array|null` - Get single row by ID
- `getTotal(array $query): int` - Get count of records matching query
- `getnumPages(array $query, int $maxperpage = 10): int` - Get total pages
- `getPages(array $query, int $maxperpage = 10, int $maxbuttons = 10, int $currentpage = null): array` - Get pagination array

## Query Array Format

### SELECT Query Array

```php
[
    'SELECT' => ['column1', 'column2'], // Optional array of columns, defaults to *
    'FROM' => 'table_name', // Required
    'WHERE' => [ // Optional
        ['col' => 'column', 'op' => 'operator', 'val' => 'value'],
        'word' => 'AND/OR', // Logical operator
        // ... more conditions
    ],
    'ORDER BY' => ['col' => 'column', 'dir' => 'ASC/DESC'], // Optional
    'LIMIT' => 10, // Optional
    'OFFSET' => 0 // Optional
]
```

### INSERT Query Array

```php
[
    'TABLE' => 'table_name',
    'DATA' => ['column' => 'value', ...]
]
```

### UPDATE Query Array

```php
[
    'TABLE' => 'table_name',
    'DATA' => ['column' => 'value', ...],
    'WHERE' => ['column' => 'value', ...]
]
```

### DELETE Query Array

```php
[
    'TABLE' => 'table_name',
    'WHERE' => ['column' => 'value', ...],
    'LIMIT' => 1 // Optional, defaults to 1
]
```

## Error Handling

All methods throw `Exception` on database errors. Wrap calls in try-catch blocks:

```php
try {
    $result = $pdoio->select($query);
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
```

## Security

- Always use prepared statements (this class does this automatically)
- Validate and sanitize input data before passing to query arrays
- Use appropriate WHERE conditions to prevent unintended data modification

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.

## Author

Rodrigo Díaz
