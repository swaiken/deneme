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

/* -------------------- Temel  Fatura  Verilerini Sorgulama (Prepared Statement) ---------------------------------------- */

$invoices = [];

$stmt = $conn->prepare("
    SELECT 
        cari_name,
        invoice_type,
        invoice_date,
        grand_total
    FROM all_invoices
    WHERE payment_from = 'banka'
      AND user_id = ?
      AND firm_id = ?

");

$stmt->bind_param("ii", $user_id, $firm_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $invoices[] = $row;
}
if (empty($invoices)) {
    die("Fatura bulunamadı veya yetkisiz erişim");
}

/*---------------------------------------------------------------------------------------------------------------------*/


/*---------------- Toplam Giriş Hesaplama Kısmı --------------------------------------------------------------*/

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(grand_total), 0) AS total_income
    FROM all_invoices
    WHERE payment_from = 'banka'
      AND invoice_type IN ('gelir', 'satis')
      AND firm_id = ?
      AND user_id = ?
");

$stmt->bind_param("ii", $firm_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_income = $result->fetch_assoc()['total_income'];



/*---------------------------------------------------------------------------------------------------------------------*/


/*---------------- Toplam Çıkış Hesaplama Kısmı --------------------------------------------------*/

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(grand_total), 0) AS total_expense
    FROM all_invoices
    WHERE payment_from = 'banka'
      AND invoice_type IN ('gider', 'alis')
      AND firm_id = ?
      AND user_id = ?
");

$stmt->bind_param("ii", $firm_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_expense = $result->fetch_assoc()['total_expense'];


/*---------------------------------------------------------------------------------------------------------------------*/

/*------------ Sorgular Sonrası Bakiye Hesaplama ---------------------------------------------------------------------------------------------------------*/


$balance = $total_income - $total_expense;


/*---------------------------------------------------------------------------------------------------------------------*/
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banka Detay Sayfası</title>
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
   
   min-height: 100vh;
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


.balance_table_container { 
    
    flex: 4;
    margin: 1rem 2rem;
    padding: 1rem;
    background: #fff;
    border-radius: 0.5rem;
    max-height: 60vh;   
    overflow-y: auto;   
    overflow-x: auto;
}


.balance_table thead {
    
    position: sticky;
    top: 0;
    color: #fff;  
    z-index: 1;

}

.balance_table tbody tr:hover {
    
    
    background-color: #eef6ff;

}

.balance_table {

    width: 100%;
    min-width: 600px;
    border-collapse: collapse;
    background-color: white;
    font-size: clamp(0.9rem, 1vw, 1.2rem);

}

.balance_table th {

    background-color: #3498db;
    padding: 0.8rem;
    text-align: left;
    

}

.balance_table td {

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
                <li><a href="/action_panel_elements/action_menus_panel/action_menus_page.php">İşlem Menüleri</a></li>
                
            </ul>
</nav>

<h2 class="page_title">Banka Hereketleri ve Detay Sayfası</h2> <!-- Sayfa Başlığı -->




<div class="card_headers">

<div class="card_infos">
    <h2>Banka Adı</h2>
    <p>Merkez Banka</p>
</div>

<div class="card_infos">
    <h2>Toplam Giriş</h2>
    <p><?= number_format($total_income, 2, ',', '.') . ' ₺' ?> </p>
</div>

<div class="card_infos">
    <h2>Toplam Çıkış</h2>
    <p><?= number_format($total_expense, 2, ',', '.') . ' ₺' ?> </p>
</div>

<div class="card_infos">
    <h2>Güncel Bakiye</h2>
    <p><?= number_format($balance, 2, ',', '.') . ' ₺' ?> </p>
</div>


</div>
    




<div class="balance_table_container">

<table class="balance_table">
        <thead>
            <tr>
               
                <th>Cari Adı</th>
                <th>Fatura Tarihi</th>
                <th>Fatura Tipi</th>                
                <th>Toplam Tutar</th>               

            </tr>
        </thead>





<tbody>
<?php foreach ($invoices as $invoice): ?>
<tr>
    <td><?= htmlspecialchars($invoice['cari_name']) ?></td>
    <td><?= date('d.m.Y', strtotime($invoice['invoice_date'])) ?></td>
    <td><?= htmlspecialchars($invoice['invoice_type']) ?></td>
    <td><?= number_format((float)$invoice['grand_total'], 2, ',', '.') ?> ₺ </td>
</tr>
<?php endforeach; ?>
</tbody>


    </table>

</div>




</body>
</html>