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
    header("Location: /main_pages/control_panel.php");
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

/* --------------------------------------------------------------------------------------------------------------------- */

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura Ekleme Ekranı</title>
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

/*<----------------------------------------  Tam Sayfa CSS Kısmı Başlangıç --------------------------------------------------------------->*/


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

/*<---------------------------------------- Navigasyon CSS Kısmı Bitiş ------------------------------------------->*/

/*< ---------- Başlık Ve Ana Kapsayıcı CSS Başlangıç -------------->*/

h2 {
 
    font-weight: 500;
    font-size: clamp(1.5rem, 2.5vw, 3.5rem);
    margin-top: clamp(0.8rem, 1.5vw, 2.5rem);
    margin-bottom: clamp(0.8rem, 1.5vw, 2.5rem);
    text-align: center;
    
    
}

.main_menu {

    display: flex;
    flex: 1;
    min-height: 0;
    flex-direction: column;
    
}

/*< ------------------------------------------------------->*/


/*------------------------------------------ Seçenekler Giriş CSS Kısmı Başlangıç  --------------------------------------------*/


.total_form_group{

   display: flex;
   flex: 1;
   flex-shrink: 0;
   flex-direction: row;

}


.left_form_group {

    display: flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(0.5rem, 1.2vw, 2rem);
    flex: 1.5;
    gap: 0.5rem;
    border-radius: 0.5rem;
    border: 3px solid #ccc;

}

.cari_search {

    width: 100%;
    padding-left: 1.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    border: 1px solid #ccc;
    font-size: clamp(0.9rem, 1vw, 1.2rem);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23999' viewBox='0 0 24 24'%3E%3Cpath d='M10 2a8 8 0 105.293 14.293l4.707 4.707 1.414-1.414-4.707-4.707A8 8 0 0010 2zm0 2a6 6 0 110 12 6 6 0 010-12z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 0.75rem center;
    background-size: 1rem;
    

}

.cari_search_wrapper {
    width: 20%;
    position: relative;
    display: flex;
    align-items: center;
}

.cari_search_input {
    width: 100%;
    padding-left: 1.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    border: 1px solid #ccc;
    font-size: clamp(0.9rem, 1vw, 1.2rem);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23999' viewBox='0 0 24 24'%3E%3Cpath d='M10 2a8 8 0 105.293 14.293l4.707 4.707 1.414-1.414-4.707-4.707A8 8 0 0010 2zm0 2a6 6 0 110 12 6 6 0 010-12z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 0.75rem center;
    background-size: 1rem;
}


.invoice_card_type {

    width: 20%;
    padding-left: 0.3rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    font-size: clamp(0.9rem, 1vw, 1.2rem);
    border: 1px solid #ccc;

}

.invoice_date {
    
    width: 20%;
    height: 2.5rem;
    padding: 0 1rem;
    border-radius: 0.5rem;
    border: 1px solid #ccc;
    font-size: clamp(0.9rem, 1vw, 1.2rem);
    background-color: #fff;
    color: #333;
    transition: all 0.2s ease;

}

.right_form_group {

    display: flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(0.5rem, 1.2vw, 2rem);
    flex: 1;
    gap: 0.5rem;
    border-radius: 0.5rem;
    border: 3px solid #ccc;

}

.invoice_payment_type,
.payment_from {

    width: 30%;
    padding-left: 0.2rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    font-size: clamp(0.9rem, 1vw, 1.2rem);
    border: 1px solid #ccc;

}

select:disabled {
    background-color: #eee;
    cursor: not-allowed;
    opacity: 0.7;
}

/*------------------------------------------ Seçenekler Giriş CSS Kısmı Bitiş  --------------------------------------------*/


/*------------------------------------------ Fatura Giriş CSS Kısmı Başlangıç  --------------------------------------------*/


.invoice_items{
    
   border-radius: 0.5rem;
   border: 3px solid #555;
   flex: 5;
   display: flex;
   flex-direction: column;
   min-height: 0;
   gap: 0.5rem;

}

.invoice_table {

    width: 100%;
    min-width: 600px;
    border-collapse: collapse;
    background-color: white;
    font-size: clamp(0.9rem, 1vw, 1.2rem);

}

.invoice_table_wrapper {
    
    flex: 1;              
    min-height: 0;        
    overflow-y: auto;
    overflow-x: auto;

}

.invoice_table thead {
    
    position: sticky;
    top: 0;
    background-color: #ecf0f1;
    z-index: 1;

}

.invoice_table th {

    background-color: #ecf0f1;
    padding: 0.8rem;
    text-align: center;
    border-bottom: 2px solid #ccc;

}

.invoice_table td {

    padding: 0.8rem;
    text-align: center;
    border-bottom: 1px solid #ddd;

}

.invoice_table td {
    padding: 0.4rem;
}

input,
select {

    width: 100%;
    height: 2.2rem;
    border-radius: 0.5rem;
    padding-left: 0.05rem;
    text-align: center;
    font-size: clamp(0.9rem, 1vw, 1.2rem);
    border: 1px solid #ccc;

}

.total_value {
    
    width: 10%;
    height: 2rem;
    border-radius: 0.5rem;
    padding-left: 0.8rem;
    font-size: clamp(0.9rem, 1vw, 1.2rem);
    color: #3498db;
    border: 1px solid #ccc;
    text-align: center;

}

.add_row_container {
    display: flex;
    justify-content: flex-end;
    padding: 0.5rem;
}

#add_row_btn {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid #3498db;
    background-color: #fff;
    color: #3498db;
    cursor: pointer;
    transition: 0.2s;
}

#add_row_btn:hover {
    background-color: #3498db;
    color: #fff;
}

.invoice_table td:last-child {
    text-align: center;
    vertical-align: middle;
}


.cari_dropdown,
#stock_dropdown {

    position: absolute;
    top: calc(100% + 0.3rem);
    left: 0;

    width: 100%;

    background: #fff;

    border: 1px solid #dcdfe4;
    border-radius: 0.6rem;

    box-shadow:
        0 10px 25px rgba(0,0,0,0.08),
        0 2px 8px rgba(0,0,0,0.05);

    overflow: hidden;

    max-height: 260px;
    overflow-y: auto;

    z-index: 999999;

    display: none;
}



.cari_dropdown div,
#stock_dropdown div {

    padding: 0.75rem 1rem;

    font-size: 0.95rem;

    cursor: pointer;

    transition:
        background-color 0.15s ease,
        color 0.15s ease;

    border-bottom: 1px solid #f1f1f1;

    background: #fff;
    color: #2c3e50;
}



.cari_dropdown div:last-child,
#stock_dropdown div:last-child {

    border-bottom: none;
}



.cari_dropdown div:hover,
#stock_dropdown div:hover {

    background-color: #3498db;
    color: #fff;
}



.cari_dropdown::-webkit-scrollbar,
#stock_dropdown::-webkit-scrollbar {

    width: 8px;
}

.cari_dropdown::-webkit-scrollbar-thumb,
#stock_dropdown::-webkit-scrollbar-thumb {

    background: #cfd6dd;
    border-radius: 10px;
}

.cari_dropdown::-webkit-scrollbar-thumb:hover,
#stock_dropdown::-webkit-scrollbar-thumb:hover {

    background: #aeb8c2;
}



.invoice_table td {

    position: relative;
}



#stock_dropdown {

    position: fixed;
    width: 250px;
}




/* Chrome, Safari, Edge */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox + modern standart */
input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}
/*----------------------------------------------------------------*/

/*------------------------------------------ Fatura Giriş CSS Kısmı Bitiş  --------------------------------------------*/


/*<------------------------------------------- Toplam Kısmı CSS Kısmı Başlangıç ------------------------------------------------>*/


.summary_card {
    background: #fff;
    border-radius: 10px;
    padding: 1rem;
    border: 2px solid #ddd;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    gap: 0.5rem;
}

.summary_row {
    display: flex;
    justify-content: space-between;
    font-size: 1rem;
    color: #555;
}

.summary_total {
    display: flex;
    justify-content: space-between;
    font-size: 1.2rem;
    font-weight: bold;
    border-top: 2px solid #ccc;
    padding-top: 0.5rem;
}

/*<------------------------------------------- Toplam Kısmı CSS Kısmı Bitiş ------------------------------------------------>*/



/*<-------------------------------------- Butonlar Kısmı CSS Başlangıç ----------------------------------------------------------->*/

.invoice_add_buttons {

   display: flex;
   flex: 0.8;
   justify-content: space-around;
   align-items: center;
   background-color: #F5F5F5;
   border-radius: 0.5rem;
   flex-shrink: 0;
   border: 3px solid #ccc;
    
}

.save_invoice {

   text-align: center;
   padding: 0.5rem;
   font-size: clamp(1rem, 2vw, 1.5rem);
   width: clamp(10rem, 15vw, 15rem);
   border-radius: 0.5rem;
   border: 0.2rem solid #3498db;
   color: #34495e;
   cursor: pointer;
   transition: background-color 0.3s ease, color 0.3s ease;

}

.save_invoice:hover {
  
   color: #F5F5F5;
   background-color: #3498db; 

}

.save_and_new_invoice {

   text-align: center;
   white-space: nowrap;
   padding: 0.5rem;
   font-size: clamp(1rem, 2vw, 1.5rem);
   width: clamp(15rem, 30vw, 20rem);
   border-radius: 0.5rem;
   border: 0.2rem solid #27ae60;
   color: #34495e;
   cursor: pointer;
   transition: background-color 0.3s ease, color 0.3s ease;    

}

.save_and_new_invoice:hover {
  
   color: #F5F5F5;
   background-color: #27ae60; 

}


.cancel_invoice {

   text-align: center;
   padding: 0.5rem;
   font-size: clamp(1rem, 2vw, 1.5rem);
   width: clamp(10rem, 15vw, 15rem);
   border-radius: 0.5rem;
   border: 0.2rem solid #e74c3c;
   color: #34495e;
   cursor: pointer;
   transition: background-color 0.3s ease, color 0.3s ease;    

}

.cancel_invoice:hover {
  
   color: #F5F5F5;
   background-color: #e74c3c; 

}


.invoice_buttons button:disabled {
    
   opacity: 0.5;
   cursor: not-allowed;
   border-color: #ccc;
   color: #999;
}

.save_invoice:disabled,
.save_and_new_invoice:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    border-color: #ccc;
    color: #999;
}

/*<------------------------------------------------------------------------------------------------------>*/



</style>


</head>
<body>

<div class="page"> <!-- page -->



<nav class="nav_menu">
            
    <div class="active_firm">
        Firma: <?= $firm_name; ?>
    </div>



            <ul class="nav_tools">
                
                <li><a href="/control_panel_elements/control_panel_page.php">Kontrol Paneli</a></li>
                <li><a href="/action_panel_elements/action_menus_panel/action_menus_page.php">İşlem Menüleri</a></li>
                <li><a href="/action_panel_elements/invoice_action_control_panel/invoice_action_page.php">Fatura İşlem Modülleri</a></li>
            
            </ul>

</nav> <!-- nav_menu -->

<h2>Fatura Ekleme Ekranı</h2> <!-- Sayfa Başlığı -->

<div class="main_menu"> <!-- main_menu -->


<!------------------------------------------ Temel Bilgiler HTML Kısmı Başlangıç  -------------------------------------------->


<div class="total_form_group">

    <div class="left_form_group">     
   
        
        
        <label for="invoice_card_type">Fatura Tipi :</label>
        <select id="invoice_card_type" name="invoice_card_type" class="invoice_card_type" required>
        
        <option value="alis"> Alış  Faturası</option>
        <option value="satis">Satış Faturası</option>
        <option value="gider">Gider Faturası</option>
        <option value="gelir">Gelir Faturası</option>
        
        </select>
             
        
<label for="cari_search">Firma (Cari)</label>

<div class="cari_search_wrapper">

    <input 
        type="text" 
        id="cari_search"
        class="cari_search_input"
        placeholder="Lütfen Cari Seçiniz"   
        autocomplete="off"
        required
    >

    <input type="hidden" id="cari_id">

    <div id="cari_dropdown" class="cari_dropdown"></div>

</div>

<label for="invoice_date">Fatura Tarihi :</label>
<input type="date" id="invoice_date" name="invoice_date" class="invoice_date" required>
   
        

        
      
    
    </div> <!-- left_form_group -->

 

    <div class="right_form_group">
        
        <label for="invoice_payment_type">Ödeme Tipi :</label>
        <select id="invoice_payment_type" name="invoice_payment_type" class="invoice_payment_type" required>
        
        
        <option value="open"> Açık Hesap (Veresiye)</option>
        <option value="close">Kapalı Hesap (Ödendi)</option>
        </select>
    

        <label for="payment_from">Ödeme Şekli :</label>
        <select id="payment_from" name="payment_from" class="payment_from" required>
        
        <option value="">Seçiniz</option>
        <option value="kasa"> Kasa Ödemeli</option>
        <option value="banka">Banka Ödemeli</option>
        </select>

    </div> <!-- right_form_group -->

</div> <!-- total_form_group -->


<!------------------------------------------ Temel Bilgiler HTML Kısmı Bitiş  -------------------------------------------->

<!------------------------------------------ Fatura Giriş HTML Kısmı Başlangıç  -------------------------------------------->


<div class="invoice_items">

  <div class="invoice_table_wrapper">
    
  
  <table class="invoice_table">
  <thead>
    <tr>
      <th>Hizmet Türü</th>
      <th>Ürün</th> 
      <th>Miktar</th>
      <th>Birim Fiyat</th>
      <th>KDV Oranı(%)</th>
      <th>İndirim Oranı(%)</th>
      <th>Toplam</th>
      <th></th>
    </tr>
  </thead>

  <tbody id="invoice_body">
    <tr>
      <td>
        <select name="invoice_type" class="invoice_type" required>
          <option value="hizmet">Hizmet</option>
          <option value="urun">Ürün Satış</option>
          <option value="servis">Teknik Servis</option>
        </select>
      </td>

      <td><input type="text" class="product_name"></td>
      <td><input type="number" class="product_quantity" min="0"></td>
      <td><input type="number" class="product_invoice" min="0"></td>

      <td>
        <select class="invoice_kdv_value">
          <option value="20">%20</option>
          <option value="10">%10</option>
          <option value="1">%1</option>
          <option value="0">%0</option>
        </select>
      </td>

      <td><input type="number" class="discount_value" min="0" max="100" step="0.01"></td>

      <td class="total_value">0.00</td>
      <td>
        <button type="button" class="delete_row_btn">🗑</button>
      </td>
    </tr>
  </tbody>

 
  <tbody id="invoice_template" style="display:none;">
    <tr class="invoice_row_template">
      <td>
        <select class="invoice_type">
          <option value="hizmet">Hizmet</option>
          <option value="urun">Ürün Satış</option>
          <option value="servis">Teknik Servis</option>
        </select>
      </td>

      <td><input type="text" class="product_name"></td>
      <td><input type="number" class="product_quantity" min="0"></td>
      <td><input type="number" class="product_invoice" min="0"></td>

      <td>
        <select class="invoice_kdv_value">
          <option value="20">%20</option>
          <option value="10">%10</option>
          <option value="1">%1</option>
          <option value="0">%0</option>
        </select>
      </td>

      <td><input type="number" class="discount_value"></td>

      <td class="total_value">0.00</td>
      <td>
        <button type="button" class="delete_row_btn">🗑</button>
      </td>
    </tr>
  </tbody>

</table>
  </div>


  <div class="add_row_container">
    <button type="button" id="add_row_btn">+ Satır Ekle</button>
  </div>

</div>

<!------------------------------------------ Fatura Giriş HTML Kısmı Bitiş  -------------------------------------------->


<!------------------------------------------ Toplam Kısmı HTML Kısmı Başlangıç  -------------------------------------------->


<div class="summary_card">

    <div class="summary_row">
        <span>Ara Toplam</span>
        <span id="summary_subtotal">0.00 ₺</span>
    </div>

    <div class="summary_row">
        <span>KDV Toplamı</span>
        <span id="summary_kdv">0.00 ₺</span>
    </div>

    <div class="summary_row">
        <span>İndirim Toplamı</span>
        <span id="summary_discount">0.00 ₺</span>
    </div>

    <div class="summary_total">
        <span>Genel Toplam</span>
        <span id="summary_total">0.00 ₺</span>
    </div>

</div>

<!------------------------------------------ Toplam Kısmı HTML Kısmı Bitiş  -------------------------------------------->


<!------------------------------------------ Buton  HTML Kısmı Başlangıç  ------------------------------------------------------------->


<div class="invoice_add_buttons"> 
    
    <button type="button" class="save_invoice" disabled>Fatura Kaydet</button>
    <button type="button" class="save_and_new_invoice" disabled>Kaydet ve Yeni Fatura</button>
    <a href="/action_panel_elements/invoice_action_control_panel/invoice_action_page.php" class="cancel_invoice">İptal ve Geri Dön</a>

</div>

<!------------------------------------------ Buton  HTML Kısmı Bitiş  ------------------------------------------------------------->    


 </div> <!-- main_menu -->

</div> <!-- page -->

<script src="/action_panel_elements/invoice_action_control_panel/search_stock.js?v=5"></script>

<script src="/action_panel_elements/invoice_action_control_panel/save_invoice.js?v=1"></script>

<script src="/action_panel_elements/invoice_action_control_panel/search_cari.js?v=1"></script>



</body>


</html>

