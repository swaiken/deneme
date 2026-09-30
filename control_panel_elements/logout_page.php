<?php
session_start();

// Session verilerini temizle
$_SESSION = [];

// Cookieleri de yok et
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, 
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Sessionları  tamamen bitir
session_destroy();

// Login sayfasına yönlendir
header("Location: https://www.visitblackstone.com");
exit;