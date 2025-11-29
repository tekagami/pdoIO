<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/*
this class can be used to connect to a SQLite or mySQL database using PDO

*/


/// setup PDO connection, in this case to a SQLite database
$pdo = new PDO('sqlite:database.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// $pdo->exec("CREATE TABLE IF NOT EXISTS data (
//     id INTEGER PRIMARY KEY AUTOINCREMENT,
//     data TEXT NOT NULL,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// )");

include('pdoIO.php');  // include the pdoIO class

$pdoio = new PDOIO($pdo);  // create a new pdoIO object

// let's do some database operations
/*
// insert example
$insertData = [
    'TABLE' => 'data',
    'DATA' => [
        'data' => 'Example data entry'
        ]
        ];
$pdoio->insert($insertData);
echo "<hr>";
// update example
$nowdate = date('Y-m-d H:i:s');
$updateData = [
    'TABLE' => 'data',
    'DATA' => [
        'data' => "$nowdate - Updated data entry"
        , 'created_at' => "$nowdate"
    ],
    'WHERE' => [
            'id' => 1
            ]
        ];
$pdoio->update($updateData);
*/
/*

// delete example
$deleteQuery = [
    'TABLE' => 'data',
    'WHERE' => [
        'id' => 4
    ],
    'LIMIT' => 1
];
$pdoio->delete($deleteQuery);
*/

/*
// simple select example
// SELECT * FROM data WHERE id > 20 AND id < 30 ORDER BY id DESC LIMIT 10 OFFSET 0
$query = [
    'SELECT' => ['*'],
    'FROM' => 'data',
    'WHERE' => [
        [
            'col' => 'id',
            'op' => '>',
            'val' => 0
        ]
        , ['word' => 'AND']
        , [
            'col' => 'id',
            'op' => '<',
            'val' => 30
        ]
    ],
    'ORDER BY' => [
        'col' => 'id',
        'dir' => 'DESC'
    ],
    'LIMIT' => 10,
    'OFFSET' => 0
];

$results = $pdoio->select($query);
echo "<pre>";
print_r($results);
echo "</pre>";
*/

// get one row by ID as array example
$id = 25;
$result = $pdoio->getById('data', $id);
echo "<p><b>getById</b></p><pre>";
print_r($result);
echo "</pre>";

// let's use the same SELECT query to get total rows and pages
$query = [
    'SELECT' => ['*'],
    'FROM' => 'data',
    'ORDER BY' => [
        'col' => 'id',
        'dir' => 'DESC'
    ],
    'LIMIT' => 10,
    'OFFSET' => 0
];


// get total matching rows
$total = $pdoio->getTotal($query);
echo "<p>Total matching rows: " . $total . "</p>\n";

// get results
$results = $pdoio->select($query);
echo "<pre>";
print_r($results);
echo "</pre>";

// get array of pages
$pages = $pdoio->getPages($query, 10);
echo "<p>Pages: " . implode(", ", $pages) . "</p>\n";
