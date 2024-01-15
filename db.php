<?php

function conncect()
{
    $host = 'localhost';
    $db = 'top_dom';
    $user = 'root';
    $pass = 'ytrewq12345';
    $charset = 'utf8';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    // $pdo = new PDO($dsn, $user, $pass, $opt);

    try {
        return $dbh = new PDO($dsn, $user, $pass);
        // echo "Успех";
    } catch (PDOException $e) {
        die('Подключение не удалось: ' . $e->getMessage());
    }
}

?>