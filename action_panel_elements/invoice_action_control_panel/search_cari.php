<?php

/*---------------- Geliştirme sırasında PHP hatalarını görmek için hata raporlamayı ve ekrana yazdırmayı açar ----------*/


error_reporting(E_ALL); // Bütün hata tipleri yaz
ini_set('display_errors', 1); // Hataları ekranda göster

/*------------------------------------------------------------------------------------------------------------*/

/*------------- Session Başlatma ------------------------------------------------------------------------*/

session_start();

/*------------------------------------------------------------------------------------------------------------*/


/*------------ Sunucunun gönderdiği verinin JSON formatında olduğunu tarayıcıya bildirir -----------------------------------*/

header("Content-Type: application/json; charset=UTF-8");

/*------------------------------------------------------------------------------------------------------------*/

/* ------------------------- Veri Tabanı Bağlantısı Kısmı ------------------------------------------- */

require_once __DIR__ . '/../../baseoops/database.php';

$db->connect();
$conn = $db->conn;

/* -------------------------------------------------------------------------------------------------- */

/* -------------------------------------------------------------------------------------------------- */

// Kullanıcı session bilgisi yoksa SQL çalıştırmadan boş JSON döndürerek isteği sonlandırır
// Session bilgileri kontrol edilip varsa kullanılmak üzere değişkenlere aktarılır

if (!isset($_SESSION['user_id']) || !isset($_SESSION['firm_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$firm_id = (int) $_SESSION['firm_id'];

/*--------------------------------------------------------------------------------------------------*/


/*------------- JS’ten gelen arama değerini alır, boşsa işlemi durdurur ve SQL LIKE araması için hazırlar --------------------------*/

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}


$search = "%" . $q . "%";

/*--------------------------------------------------------------------------------------------------*/


/*--------------------------------------------------------------------------------------------------*/


// JS’ten gelen arama değeri SQL’e güvenli şekilde bağlanır, veritabanından en fazla 10 eşleşen cari çekilir, 
// sonuçlar PHP array’e dönüştürülür ve JSON olarak JS’e gönderilir.

$stmt = $conn->prepare("
    SELECT cari_id, firm_id, cari_name
    FROM all_cari
    WHERE firm_id = ?
    AND cari_name LIKE ?
    ORDER BY cari_name ASC
    LIMIT 10
");

$stmt->bind_param("is", $firm_id, $search);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "cari_id" => (int)$row["cari_id"],
        "firm_id" => (int)$row["firm_id"],
        "cari_name" => $row["cari_name"]
    ];
}


echo json_encode($data);
exit;


/*--------------------------------------------------------------------------------------------------*/