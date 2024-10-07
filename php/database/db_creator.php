<?php
// connect to the database
require "./database/db_connection.php";
$statements = [];
// Add Tables
foreach (glob("./database/Tables/*.php") as $filename) {
    require $filename;
}
// Add Triggers
foreach (glob("./database/Triggers/*.php") as $filename) {
    require $filename;
}
// Add Procedures
foreach (glob("./database/Procedures/*.php") as $filename) {
    require $filename;
}
require "./database/db_defaults.php";
// execute SQL statements
foreach ($statements as $statement) {
    $pdo->exec($statement);
}