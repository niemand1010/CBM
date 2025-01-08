<?php
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = 'root'; // Passe das Passwort entsprechend an
$DATABASE_NAME = 'Site'; // Geändert von 'Site'

$con = new mysqli($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if ($con->connect_errno) {
    error_log('Failed to connect to MySQL: ' . $con->connect_error);
    exit('Datenbankverbindung fehlgeschlagen.');
}
?>
