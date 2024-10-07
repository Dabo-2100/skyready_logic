<?php
if (array_key_exists('PATH_INFO', $_SERVER)) {
    $requestUri = $_SERVER['PATH_INFO'];
} else {
    echo "Error : 403 | Forbidden";
    http_response_code(403);
    exit();
}

$match = 0;

foreach ($endpoints as $pattern => $function) {
    $regex = preg_replace('~\{(\w+)\}~', '(?P<$1>[^/]+)', $pattern);
    $regex = str_replace('/', '\/', $regex);
    $regex = "/^$regex$/";
    if (preg_match($regex, $requestUri, $matches)) {
        if (function_exists($function)) {
            $match = 1;
            $function($matches);
        }
    }
}

if ($match == 0) {
    echo "Error : 404 | Not Found";
    http_response_code(404);
    exit();
}
