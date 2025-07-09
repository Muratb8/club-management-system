<?php
// Oturum başlat
session_start();

// Oturumu sonlandır
session_unset();
session_destroy();

// Kullanıcıyı login sayfasına yönlendir
header("Location: ../public/giris.php");
exit();
?>
