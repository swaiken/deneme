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

/* --------- Delete işleminin sadece POST ile ve geçerli invoice_id ile çalışmasını garanti eder -------------------- */

if (!isset($_POST['invoice_id'])) {
    header("Location: invoice_action_page.php");
    exit;
}

$invoice_id = (int) $_POST['invoice_id'];

/* -------------------------------------------------------------------------------------------------- */


/* --------------- Silme İşleminin Yapıldığı Kısım (Prepared Statement Kullanımı) ----------------------------- */

$stmt = $conn->prepare("
    
    DELETE FROM all_invoices
    WHERE invoice_id = ? AND user_id = ? AND firm_id = ?

");

$stmt->bind_param("iii", $invoice_id, $user_id, $firm_id);
$stmt->execute();

// İşlem Sonrası Yönlendirme
header("Location: /action_panel_elements/invoice_action_control_panel/invoice_action_page.php");
exit;

/* -------------------------------------------------------------------------------------------------- */

?>