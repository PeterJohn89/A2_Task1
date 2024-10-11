<?php
if (!isset($_SESSION['user_name'])) {
    header('Location: login.php');
    exit();
}else{
    header('Location: main.php');
    exit();
}
