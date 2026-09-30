<?php

session_start(); // session başlangıcı 

/* -------- Bu sayfa cache'lenmesini önlemek amaçlı kısım (login sonrası özel içerik)  ----------- */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

/* -------------------------------------------------------------------------------------------------- */


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


/* -------------------- Firma Adını Çekmek için Kullanılan Kısım  -------------------------------------------------- */

$stmt = $conn->prepare("
    SELECT firm_name
    FROM all_firms
    WHERE firm_id = ?
    AND user_id = ?
    LIMIT 1 
");

$stmt->bind_param("ii", $firm_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$firm_name = "Bilinmeyen Firma";

if ($row = $result->fetch_assoc()) {
    $firm_name = htmlspecialchars($row['firm_name']);
}

/*---------------------------------------------------------------------------------------------------------------------*/

/*---------------------- Seçilen Faturaya Özel ID'nin Ele alındığı Kısım ----------------------------------------------*/

$invoice_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($invoice_id <= 0) {
    die("Geçersiz fatura ID");
}

/*---------------------------------------------------------------------------------------------------------------------*/

/* -------------------- Temel  Fatura  Verilerini Sorgulama (Prepared Statement) ---------------------------------------- */

$stmt = $conn->prepare("
    SELECT 
        invoice_id,
        cari_name,
        invoice_type,
        payment_type,
        payment_from,
        invoice_date,
        subtotal,
        total_kdv,
        total_discount,
        grand_total
    FROM all_invoices
    WHERE invoice_id = ?
      AND user_id = ?
      AND firm_id = ?
    LIMIT 1
");

$stmt->bind_param("iii", $invoice_id, $user_id, $firm_id);
$stmt->execute();

$result = $stmt->get_result();
$invoice = $result->fetch_assoc();

if (!$invoice) {
    die("Fatura bulunamadı veya yetkisiz erişim");
}

/* ------------------------ Fatura Detaylarının Verilerini Sorgulama (Prepared Statement) -------------------------------------- */


$stmt = $conn->prepare("
    SELECT 
        description,
        quantity,
        unit_price,
        kdv_rate,
        discount_rate,
        line_subtotal,
        line_kdv,
        line_discount,
        line_total
    FROM invoice_items
    WHERE invoice_id = ?
");

$stmt->bind_param("i", $invoice_id);
$stmt->execute();

$result = $stmt->get_result();

$invoice_items = [];

while ($row = $result->fetch_assoc()) {
    $invoice_items[] = $row;
}


/*---------------------------------------------------------------------------------------------------------------------*/

/*------------------- Veri Tabanından Gelen Verilerin Kullanıcıya Gösteriminde Düzeltmeler ----------------------------*/

$types = [
    'alis'  => 'Alış  Faturası',
    'satis' => 'Satış Faturası',
    'gider' => 'Gider Faturası',
    'gelir' => 'Gelir Faturası',
    'open'  => 'Veresiye Hesap',
    'close' => 'Kapalı Hesap'
    
];

/*---------------------------------------------------------------------------------------------------------------------*/

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura Detay Sayfası</title>
</head>

<style>

/*<---------------------------------------- Temel Sayfa CSS Kısmı Başlangıç --------------------------------------------------------->*/

* {
  
   margin: 0;
   padding: 0;
   box-sizing: border-box;
  
}

body {
 
   font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
   background-color: #f2f6fb;

}



a {

   text-decoration: none;

}

li {

   list-style: none;

}


/*<---------------------------------------- Temel Sayfa CSS Kısmı Bitiş ----------------------------------------------------------------->*/

/*<----------------------------------------  Tam Sayfa CSS Kısmı Başlangıç ---------------------------------------------->*/


.page {
   
   height: 100vh;
   display: flex;
   flex-direction: column;


}

/*<----------------------------------------  Tam Sayfa CSS Kısmı Bitiş -------------------------------------------------------------->*/

/*<---------------------------------------- Navigasyon CSS Kısmı Başlangıç ------------------------------------------------------------>*/


.nav_menu {
    padding: clamp(1rem, 1vw, 3rem); 
    display: flex;
    align-items: center;
    background-color: #F5F5F5;

}

.active_firm {
 
    color: #34495e;
    border: 0.2rem solid #3498db;
    padding: 0.5rem;
    border-radius: 0.5rem;
    font-size: clamp(1rem, 2vw, 1.5rem);

}

.nav_tools{
   
    font-size: clamp(1rem, 2vw, 1.5rem);
    display: flex;
    gap: clamp(1rem, 2vw, 2rem);
    margin-left: auto;

}

.nav_menu a{

    padding: 0.5rem;
    border-radius: 0.5rem;
    border: 0.2rem solid #3498db;
    color: #34495e;
    transition: background-color 0.3s ease, color 0.3s ease;

}

.nav_menu a:hover {
  
    color: #F5F5F5;
    background-color: #3498db; 

}

/*<---------------------------------------- Navigasyon CSS Kısmı Bitiş ---------------------------------------------------------------->*/

.page_title {
 
    font-weight: 500;
    font-size: clamp(1.5rem, 2.5vw, 3.5rem);
    margin-top: clamp(0.8rem, 1.5vw, 2.5rem);
    margin-bottom: clamp(0.8rem, 1.5vw, 2.5rem);
    text-align: center;
    
    
}


.card_headers {

    display: flex;
    flex: 1;
    gap: 1rem;
    padding: 0 4rem;  
    margin-bottom: 1rem;

}

.card_infos {

    display: flex;
    flex-direction: column;
    flex: 1;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    background: #fff;
    border-radius: 0.5rem;
    padding: 1rem;


}

.card_infos h2 {

    color: #34495e;
    font-size: 1.2rem;

}

.card_infos p {

    color: #3498db;
    font-weight: 700;
    font-size: 1.5rem;

}

.main_infos_table {

    display: flex;
    flex: 4;
    background: #fff;
    border-radius: 0.5rem;
    margin: 1rem 2rem;

}


.stock_table_container { 
    
    flex: 4;
    margin: 1rem 2rem;
    padding: 1rem;
    background: #fff;
    border-radius: 0.5rem;
    max-height: 60vh;   
    overflow-y: auto;   
    overflow-x: auto;
}


.stock_table thead {
    
    position: sticky;
    top: 0;
    color: #fff;  
    z-index: 1;

}

.stock_table tbody tr:hover {
    
    
    background-color: #eef6ff;

}

.stock_table {

    width: 100%;
    min-width: 600px;
    border-collapse: collapse;
    background-color: white;
    font-size: clamp(0.9rem, 1vw, 1.2rem);

}

.stock_table th {

    background-color: #3498db;
    padding: 0.8rem;
    text-align: left;
    

}

.stock_table td {

    padding: 0.8rem;
    border-bottom: 1px solid #ddd;

}

</style>

<body>

<div class="page"> <!-- page -->



    <nav class="nav_menu">
            
    <div class="active_firm">
        Firma: <?= $firm_name; ?>
    </div>



            <ul class="nav_tools">
                
                <li><a href="/control_panel_elements/control_panel_page.php">Kontrol Paneli</a></li>
                <li><a href="/action_panel_elements/invoice_action_control_panel/invoice_action_page.php">İşlem Menüleri</a></li>
                
            </ul>
</nav>

<h2 class="page_title">Stok Hereketleri ve Detay Sayfası</h2> <!-- Sayfa Başlığı -->




<div class="card_headers">

<div class="card_infos">
    <h2>Cari Adı</h2>
    <p><?= htmlspecialchars($invoice['cari_name']); ?></p>
</div>

<div class="card_infos">
    <h2>Fatura Tipi</h2>
    <p><?= htmlspecialchars($types[$invoice['invoice_type']] ?? $invoice['invoice_type']); ?></p>
</div>

<div class="card_infos">
    <h2>Ödeme Tipi</h2>
    <p><?= htmlspecialchars($types[$invoice['payment_type']] ?? $invoice['payment_type']); ?></p>
</div>

<div class="card_infos">
    <h2>Tarih</h2>
    <p><?= date('d.m.Y', strtotime($invoice['invoice_date'])); ?></p>
</div>



</div>
    



<div class="stock_table_container">

<table class="stock_table">
        <thead>
            <tr>
                <th>Ürün Adı</th>
                <th>Ürün Miktarı</th>
                <th>Birim Başı Fiyatı</th>
                <th>KDV Oranı</th>               
                <th>İndirim Oranı</th>
                <th>Satır Ara Toplamı</th>
                <th>KDV Tutarı</th>
                <th>İndirim Tutarı</th>
                <th>Satır Genel Toplamı</th>
            </tr>
        </thead>


<tbody>

<?php foreach ($invoice_items as $item): ?>

<tr>
    <td><?= htmlspecialchars($item['description']); ?></td>
    <td><?= $item['quantity']; ?></td>
    <td><?= $item['unit_price']; ?></td>
    <td><?= $item['kdv_rate']; ?></td>
    <td><?= $item['discount_rate']; ?></td>
    <td><?= $item['line_subtotal']; ?></td>
    <td><?= $item['line_kdv']; ?></td>
    <td><?= $item['line_discount']; ?></td>
    <td><?= $item['line_total']; ?></td>
</tr>

<?php endforeach; ?>



</tbody>
    </table>

</div>


<div class="card_headers">



    <div class="card_infos">
        <h2>Ara Toplam</h2>
        <p><?= $invoice['subtotal']; ?></p>
    </div>

    <div class="card_infos">
        <h2>Toplam KDV Tutarı</h2>
        <p><?= $invoice['total_kdv']; ?></p>
    </div>

    <div class="card_infos">
        <h2>Toplam İndirim Tutarı</h2>
        <p><?= $invoice['total_discount']; ?></p>
    </div>

    <div class="card_infos">
        <h2>Genel Toplam</h2>
        <p><?= $invoice['grand_total']; ?></p>
    </div>
    

</div>

</body>
</html>