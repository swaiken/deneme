<?php

session_start(); // session başlangıcı 

/* ------------------------- Veri Tabanı Bağlantısı Kısmı ------------------------------------------- */

require_once __DIR__ . '/../../baseoops/database.php';

$db->connect();
$conn = $db->conn;

/* -------------------------------------------------------------------------------------------------- */

/* -------------------- Login Kontrol Kısmı (user_id Kontrol) ----------------------------------- */

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
$user_id = (int) $_SESSION['user_id'];

/* -------------------------------------------------------------------------------------------------- */


/* -------------------- Aktif Firma Kontrol Kısmı (firm_id Kontrol) -------------------- */

if (!isset($_SESSION['firm_id'])) {
    header("Location: /main_pages/control_panel.php");
    exit;
}
$firm_id = (int) $_SESSION['firm_id'];

/* -------------------------------------------------------------------------------------------------- */


/* --------- Update işleminin sadece POST ile ve geçerli cari_id ile çalışmasını garanti eder -------------------- */

if (!isset($_POST['cari_id'])) {
    header("Location: cari_action_page.php");
    exit;
}

/* -------------------------------------------------------------------------------------------------- */

/* -------- POST Edilmiş Verilerin UPDATE Edilmesi (Prepared Statement Kullanımı) --------------------------------- */


$cari_id            = (int) $_POST['cari_id'];
$cari_name          = trim($_POST['cari_name']);
$cari_surname       = trim($_POST['cari_surname']);
$cari_type           = trim($_POST['cari_type']);
$cari_card_type      = trim($_POST['cari_card_type']);



// --- Güncelleme sorgusu ---
$stmt = $conn->prepare("
    UPDATE all_cari
    SET 
    cari_name = ?,
    cari_surname = ?, 
    cari_type = ?, 
    cari_card_type = ?
    WHERE cari_id = ? AND user_id = ? AND firm_id = ?
");

$stmt->bind_param(
    "ssssiii",
    $cari_name,
    $cari_surname,
    $cari_type,
    $cari_card_type,
    $cari_id,
    $user_id,
    $firm_id
);

/* -------------------------------------------------------------------------------------------------- */


/* ------------ Güncelleme Sonrası Uyarı Mesajı ve Sayfa Yönlendirmesi -------------------------------- */

if ($stmt->execute()) {
    echo "<script>
            alert('Cari başarıyla güncellendi!');
            window.location.href='/action_panel_elements/cari_action_control_panel/cari_action_page.php';
          </script>";
    exit;
} else {
    echo "<script>
            alert('Cari güncelleme sırasında hata oluştu: " . htmlspecialchars($stmt->error) . "');
            window.history.back();
          </script>";
    exit;
}

/* -------------------------------------------------------------------------------------------------- */

?>