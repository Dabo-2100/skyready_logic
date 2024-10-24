<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
// ini_set('mysqlnd_ms_config.max_packet_size', '64M');
// SET GLOBAL max_allowed_packet = 1073741824;

// Include Composer autoloader
require 'vendor/autoload.php';
// make .env File Load
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
// Create Database Connection
require './database/db_creator.php';
// Use JWT Token & mailer
require "./functions/sql_functions.php";
require './functions/helper_functions.php';
require './functions/token_functions.php';

// Use Server Method
$method = $_SERVER['REQUEST_METHOD'];
// Make Global $POST_data
if ($method === 'POST') {
    $Post_object = file_get_contents('php://input');
    $POST_data = json_decode($Post_object, true);
}
// Make Global Response
$response = ['err' => true, 'msg' => null, 'data' => null];
$endpoints = [];
// Use Controllers
foreach (glob("./controllers/*.php") as $filename) {
    require $filename;
}
// Use Router
require "./assets/router.php";
