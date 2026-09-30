<?php


/*------------- Session Başlatma ------------------------------------------------------------------------*/



error_reporting(0);
ini_set('display_errors', 0);

session_start();

header("Content-Type: application/json; charset=UTF-8");
header("X-Content-Type-Options: nosniff");

/*------------------------------------------------------------------------------------------------------------*/

/*------------ Sunucunun gönderdiği verinin JSON formatında olduğunu tarayıcıya bildirir -----------------------------------*/

header("Content-Type: application/json; charset=UTF-8");

/*------------------------------------------------------------------------------------------------------------*/

/* ------------------------- Veri Tabanı Bağlantısı Kısmı ------------------------------------------- */

require_once __DIR__ . '/../../baseoops/database.php';

$db->connect();
$conn = $db->conn;

/* -------------------------------------------------------------------------------------------------- */


/*-----  Request body’den gelen JSON verisini çözer, geçersizse işlemi sonlandırır ---------------------*/


$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {

    echo json_encode([
        "success" => false,
        "message" => "Geçersiz JSON verisi"
    ]);
    exit;
}

/*--------------------------------------------------------------------------------------------------*/


/*--------- Session’dan kullanıcı ve firma ID bilgilerini alır, yoksa null atar ------------------------*/


$user_id = $_SESSION["user_id"] ?? null;
$firm_id = $_SESSION["firm_id"] ?? null;


/*--------------------------------------------------------------------------------------------------*/


/*----- JSON verisini PHP değişkenlerine parçalamak ve tip güvenli hale getirmek -----------------*/


$cari_id = $data["cari_id"] ?? null;

$cari_name = trim($data["cari_name"] ?? "");

$invoice_type = $data["invoice_type"] ?? null;

$payment_type = $data["payment_type"] ?? "open";

$payment_from = $data["payment_from"] ?? null;

$invoice_date = $data["invoice_date"] ?? null;

$subtotal = (float) ($data["subtotal"] ?? 0);

$total_kdv = (float) ($data["kdv_total"] ?? 0);

$total_discount = (float) ($data["discount_total"] ?? 0);

$grand_total = (float) ($data["grand_total"] ?? 0);

$items = $data["items"] ?? [];

error_log("ITEMS RAW:");
error_log(print_r($items, true));

/*--------------------------------------------------------------------------------------------------*/


/*------ Zorunlu fatura alanlarını kontrol eder, eksikse işlemi durdurur ----------------*/


if (
    !$user_id ||
    !$firm_id ||
    !$cari_id ||
    !$cari_name ||
    !$invoice_type ||
    !$invoice_date ||
    empty($items)
) {

    echo json_encode([
        "success" => false,
        "debug" => [
            "user_id" => $user_id,
            "firm_id" => $firm_id,
            "cari_id" => $cari_id,
            "cari_name" => $cari_name,
            "invoice_type" => $invoice_type,
            "invoice_date" => $invoice_date,
            "items_count" => count($items)
        ]
    ]);
    exit;
}

/*--------------------------------------------------------------------------------------------------*/


/*-------- Transaction başlatır ve fatura ana kaydını all_invoices tablosuna ekler -----------------*/


$conn->begin_transaction();

try {



    $stmt = $conn->prepare("
        INSERT INTO all_invoices
        (
            user_id,
            firm_id,
            cari_id,
            cari_name,
            invoice_type,
            payment_type,
            payment_from,
            invoice_date,
            subtotal,
            total_kdv,
            total_discount,
            grand_total
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        "iiisssssdddd",
        $user_id,
        $firm_id,
        $cari_id,
        $cari_name,
        $invoice_type,
        $payment_type,
        $payment_from,
        $invoice_date,
        $subtotal,
        $total_kdv,
        $total_discount,
        $grand_total
    );

    $stmt->execute();

    $invoice_id = $conn->insert_id;

    $stmt->close();

/*--------------------------------------------------------------------------------------------------*/


/*----- Fatura kalemlerini invoice_items tablosuna yazmak için prepared statement hazırlar ---------------*/


    $stmt_item = $conn->prepare("
        INSERT INTO invoice_items
        (
            invoice_id,
            product_id,
            description,
            quantity,
            unit_price,
            kdv_rate,
            discount_rate,
            line_subtotal,
            line_kdv,
            line_discount,
            line_total
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt_item) {
        throw new Exception($conn->error);
    }


/*--------------------------------------------------------------------------------------------------*/


/*----- Fatura kalemlerini döngü ile hesaplar ve invoice_items tablosuna yazar ------------------*/


  foreach ($items as $item) {

    error_log("ITEM: " . json_encode($item));

    $product_id = isset($item["stock_id"]) ? (int)$item["stock_id"] : null;

    $description = trim($item["name"] ?? "");

    $quantity = isset($item["quantity"]) ? (float)$item["quantity"] : 0;
    $unit_price = isset($item["unit_price"]) ? (float)$item["unit_price"] : 0;

    $kdv_rate = isset($item["kdv_rate"]) ? (float)$item["kdv_rate"] : 0;
    $discount_rate = isset($item["discount_rate"]) ? (float)$item["discount_rate"] : 0;


    if ($quantity <= 0 || $unit_price <= 0) {
        continue;
    }

    $line_subtotal = $quantity * $unit_price;
    $line_kdv = $line_subtotal * ($kdv_rate / 100);
    $line_discount = $line_subtotal * ($discount_rate / 100);
    $line_total = $line_subtotal + $line_kdv - $line_discount;


        $stmt_item->bind_param(
            "iisdddddddd",
            $invoice_id,
            $product_id,
            $description,
            $quantity,
            $unit_price,
            $kdv_rate,
            $discount_rate,
            $line_subtotal,
            $line_kdv,
            $line_discount,
            $line_total
        );

        $stmt_item->execute();
    }

    $stmt_item->close();


/*--------------------------------------------------------------------------------------------------*/


/*------- Her şey doğruysa işlemi tamamlar, hata varsa yapılan değişiklikleri geri alır ve sonucu bildirir -----------------*/

    $conn->commit();

    echo json_encode([
        "success" => true,
        "invoice_id" => $invoice_id
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

/*--------------------------------------------------------------------------------------------------*/
