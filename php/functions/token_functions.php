<?php
// require __DIR__ . '/vendor/autoload.php'; // Include Composer's autoloader
require_once './vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function createToken($user_id, $is_super)
{
    $key = 'Dabo2100@IPACO';
    $payload = [
        "iss" => gethostbyaddr($_SERVER['REMOTE_ADDR']),
        "iss_ip" => $_SERVER['REMOTE_ADDR'],
        "iat" => time(), // issued at time
        "user_id" => $user_id,
        "is_super" => $is_super
    ];
    $token = JWT::encode($payload, $key, 'HS256');
    return $token;
}

function checkToken($token)
{
    $key = 'Dabo2100@IPACO';
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return json_encode($decoded);
    } catch (Exception $e) {
        return $e;
    }
}
