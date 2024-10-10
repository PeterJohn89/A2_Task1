<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the requested URL path
$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Debugging: Log the requested URL path
error_log("Requested URI: " . $request_uri);

// Switch case for routing based on the URL
switch ($request_uri) {
    case "/":
        $page = "login.php"; 
        break;
    case "/index":
        $page = "login.php"; 
        break;
    case "/login":
        $page = "login.php";
        break;
    case "/register":
        $page = "register.php";
        break;
    case "/main":
        $page = "main.php";
        break;
    default:
        $pageTitle = "404 Page Not Found";
        $page = "404.php";
        break;
}

// Debugging: Log the selected page
error_log("Selected Page: " . $page);

// Include the selected page
include $page;
