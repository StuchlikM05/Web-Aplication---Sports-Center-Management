<?php
// config.php

// Definice proměnných pro připojení k databázi
$db_host = 'localhost';
$db_user = 'username';
$db_pass = 'database_password';
$db_name = 'database_name';

// Funkce pro připojení k databázi
function getDBConnection() {
    global $db_host, $db_user, $db_pass, $db_name;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Připojení k databázi selhalo: " . $conn->connect_error);
    }
    return $conn;
}
?>