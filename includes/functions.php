<?php
require 'config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function registerUser($username, $password) {
    global $con;
    $stmt = $con->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    if (!$stmt) {
        error_log('Prepare failed: ' . $con->error);
        return false;
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param('ss', $username, $hashed_password);
    return $stmt->execute();
}

function loginUser($username, $password) {
    global $con;
    if ($stmt = $con->prepare('SELECT UserID, password FROM users WHERE username = ?')) { // Geändert von 'UserID' zu 'id'
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                session_regenerate_id();
                $_SESSION['loggedin'] = TRUE;
                $_SESSION['username'] = $username;
                $_SESSION['UserID'] = $id;
                return true;
            }
        }
        $stmt->close();
    }
    error_log('Login failed for user: ' . $username);
    return false;
}
?>
