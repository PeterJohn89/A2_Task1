<?php
// Get the requested URL path
$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Switch case for routing based on the URL
switch ($request_uri) {
    case "A2_Task1/":
        $page = "login.php"; 
        break;
    case "A2_Task1/index":
        $page = "login.php"; 
        break;
    case "A2_Task1/login":
        $page = "login.php";
        break;
    case "A2_Task1/register":
        $page = "register.php";
        break;
    case "A2_Task1/main":
        $page = "main.php";
        break;
    default:
        $pageTitle = "404 Page Not Found";
        $page = "404.php";
        break;
}

include $page;