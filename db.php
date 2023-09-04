<?php

    define('DB_HOST', 'localhost');
    define('DB_NAME', 'app_transactions');
    define('DB_USER', 'nihshank');
    define('DB_PASSWORD', '9TGfruZtPF0d]jeb');

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;

    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);

?>

