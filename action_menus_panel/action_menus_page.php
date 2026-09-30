<?php

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/../../baseoops/database.php';

$db->connect();
$conn = $db->conn;

/* -------------------- LOGIN KONTROL -------------------- */

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* -------------------- FİRMA SEÇİMİ (POST GELİRSE) -------------------- */

if (isset($_POST['firm_id'])) {

    $firm_id = (int) $_POST['firm_id'];

    $stmt = $conn->prepare("
        SELECT firm_id
        FROM all_firms
        WHERE firm_id = ?
        AND user_id = ?
        LIMIT 1
    ");

    $stmt->bind_param("ii", $firm_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $_SESSION['firm_id'] = $firm_id;

       
        header("Location: /action_panel_elements/action_menus_panel/action_menus_page.php");
        exit;

    } else {

        header("Location: /main_pages/control_panel.php");
        exit;
    }
}

/* -------------------- AKTİF FİRMA SESSION KONTROL -------------------- */

if (!isset($_SESSION['firm_id'])) {
    header("Location: /main_pages/control_panel.php");
    exit;
}

$firm_id = (int) $_SESSION['firm_id'];

/* -------------------- FİRMA ADINI ÇEK -------------------- */

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

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşlem Menüleri</title>

<style>


/*<---------------------------------------- Temel Sayfa CSS Kısmı Başlangıç ---------------------------------------------->*/

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


/*<---------------------------------------- Temel Sayfa CSS Kısmı Bitiş -------------------------------------------->*/

/*<----------------------------------------  Tam Sayfa CSS Kısmı Başlangıç --------------------------------------------------------------->*/


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

/*<---------------------------------------- Menü CSS Kısmı Başlangıç ---------------------------------------------------------------->*/

.menu_all {

    display: grid;
    grid-template-columns: repeat(3, 1fr);
    justify-content: center;
    margin-top: clamp(3rem, 4vw, 5rem);
    gap: 1rem;
    padding: 0 1rem;
    flex: 1;
    align-content: start;

}

h2 {
 
    font-weight: 500;
    font-size: clamp(1.5rem, 2.5vw, 3.5rem);
    margin-top: clamp(3rem, 4vw, 5rem);
    text-align: center;
    

}

.menu_item {

    padding: 2rem;
    border: 1px solid #ccc;
    text-decoration: none;
    text-align: center;
    border-radius: 10px;
    color: #34495e;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
    height: auto;
    align-self: start;

}


.menu_item:hover {
    background-color:  #3498db;
    color: #F5F5F5;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2); /* hover gölgeleme etkisi */
}



.menu_item:last-child {
    grid-column: 2;
}


/*<---------------------------------------- Menü CSS Kısmı Bitiş ---------------------------------------------------------------------->*/


/*<---------------------------------------- Footer CSS Kısmı Başlangıç --------------------------------------------------------------------------------->*/

footer{
    
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 10vh;
    font-size: clamp(0.8rem, 1.5vw, 2rem);
    background-color: #34495e;
    color: #F5F5F5;

}

footer a{

    color: #F5F5F5;

}

footer a:hover {

    color: #3498db;
}

/*<---------------------------------------- Footer CSS Kısmı Bitiş --------------------------------------------------------------------------------->*/
</style>

</head>
<body>
    

<div class="page">



    <nav class="nav_menu">
            
    <div class="active_firm">
        Firma: <?= $firm_name; ?>
    </div>



            <ul class="nav_tools">
                
                <li><a href="/control_panel_elements/control_panel_page.php">Kontrol Paneli</a></li>
                
            </ul>
</nav>

<h2>İşlem Menüleri</h2>

<div class="menu_all">
    
    
    
    <a href="/action_panel_elements/firm_info_update_panel/firm_info_update_page.php" class="menu_item">Firma Bilgileri</a>
    <a href="/action_panel_elements/incomes_action_control_panel/income_detail_page.php" class="menu_item">Genel Gelirler</a>
    <a href="/action_panel_elements/cash_action_control_panel/cash_detail_page.php" class="menu_item">Kasa Bilgileri</a>
    
    <a href="/action_panel_elements/invoice_action_control_panel/invoice_action_page.php" class="menu_item">Fatura Oluşturma Ekranı</a>
    <a href="/action_panel_elements/expenses_action_control_panel/expense_detail_page.php" class="menu_item">Genel Giderler</a>
    <a href="/action_panel_elements/bank_action_control_panel/bank_detail_page.php" class="menu_item">Banka Bilgileri</a>
    
    <a href="/action_panel_elements/cari_action_control_panel/cari_action_page.php" class="menu_item">Cari İşlem Modülleri</a>
 <!--   <a href="/action_panel_elements/balance_sheet_control_panel/balance_sheet_detail_page.php" class="menu_item">Bilanço Tablosu</a>  -->
    <a href="/action_panel_elements/stock_action_control_panel/stock_action_page.php" class="menu_item">Stok Bilgileri</a>
  <!--  <a href="" class="menu_item">Çek-Senet Ekranı</a> -->
 
</div>


<footer>
    <p>İletişim: info@blackstone.com | Tel: +90 555 555 55 55</p>
    <p>
      <a href="#">Facebook</a> |
      <a href="#">Instagram</a> |
      <a href="#">X (Twitter)</a>
    </p>
</footer>


</div> <!-- page -->

</body>
</html>