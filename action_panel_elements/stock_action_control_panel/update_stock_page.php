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


/* --------- Update işleminin sadece POST ile ve geçerli stock_id ile çalışmasını garanti eder -------------------- */

if (!isset($_POST['stock_id'])) {
    header("Location: stock_list_page.php");
    exit;
}

/* -------------------------------------------------------------------------------------------------- */

/* -------- POST Edilmiş Verilerin UPDATE Edilmesi (Prepared Statement Kullanımı) --------------------------------- */


$stock_id            = (int) $_POST['stock_id'];
$stock_name          = trim($_POST['stock_name']);
$stock_price         = $_POST['stock_price'] !== '' ? (float)$_POST['stock_price'] : null;
$stock_card_type     = trim($_POST['stock_card_type']);
$stock_unit_type     = trim($_POST['stock_unit_type']);
$stock_kdv_value     = (int) $_POST['stock_kdv_value'];


// --- Güncelleme sorgusu ---
$stmt = $conn->prepare("
    UPDATE all_stocks
    SET 
    stock_name = ?, 
    stock_price = ?, 
    stock_card_type = ?, 
    stock_unit_type = ?, 
    stock_kdv_value = ?
    WHERE stock_id = ? AND user_id = ? AND firm_id = ?
");

$stmt->bind_param(
    "sdssiiii",
    $stock_name,
    $stock_price,
    $stock_card_type,
    $stock_unit_type,
    $stock_kdv_value,
    $stock_id,
    $user_id,
    $firm_id
);

/* -------------------------------------------------------------------------------------------------- */


/* ------------ Güncelleme Sonrası Uyarı Mesajı ve Sayfa Yönlendirmesi -------------------------------- */

if ($stmt->execute()) {
    echo "<script>
            alert('Stok başarıyla güncellendi!');
            window.location.href='/action_panel_elements/stock_action_control_panel/stock_action_page.php';
          </script>";
    exit;
} else {
    echo "<script>
            alert('Stok güncelleme sırasında hata oluştu: " . htmlspecialchars($stmt->error) . "');
            window.history.back();
          </script>";
    exit;
}

/* -------------------------------------------------------------------------------------------------- */

?>