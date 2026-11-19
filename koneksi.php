<?php
// JANGAN TARUH PASSWORD ASLI DI GITHUB
// Isi di InfinityFree aja, atau pakai file config lokal
$host = "sql301.infinityfree.com";
$user = "if0_43047447";
$pass = "alreya123";
$db   = "if0_43047447_marketplace_tradisional";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
