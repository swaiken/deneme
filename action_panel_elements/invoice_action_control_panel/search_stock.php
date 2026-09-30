<?php

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

// JS’ten gelen arama değeri SQL’e güvenli şekilde bağlanır, veritabanından en fazla 10 eşleşen stok çekilir, 
// sonuçlar PHP array’e dönüştürülür ve JSON olarak JS’e gönderilir.

$stmt = $conn->prepare("
    SELECT 
        stock_id,
        stock_name,
        stock_price,
        stock_kdv_value
    FROM all_stocks
    WHERE firm_id = ?
    AND stock_name LIKE ?
    ORDER BY stock_name ASC
    LIMIT 10
");

if (!$stmt) {
    echo json_encode([]);
    exit;
}

$stmt->bind_param("is", $firm_id, $search);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {

    $data[] = [
        "stock_id"    => (int) $row["stock_id"],
        "stock_name"  => $row["stock_name"],
        "sale_price"  => (float) $row["stock_price"],
        "kdv_rate"    => (float) $row["stock_kdv_value"]
    ];
}



echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit;

/*--------------------------------------------------------------------------------------------------*/