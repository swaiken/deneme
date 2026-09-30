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

/* -------------------- Post ile Gönderilen Verileri İnsert  Yapıcak Kısım -------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stock_name         = trim($_POST['stock_name'] ?? '');
    $stock_price        = (float) ($_POST['stock_price'] ?? 0);
    $stock_card_type    = trim($_POST['stock_card_type'] ?? '');
    $stock_unit_type    = trim($_POST['stock_unit_type'] ?? '');
    $stock_kdv_value    = trim($_POST['stock_kdv_value'] ?? '');


    if (
    
    $stock_name === '' ||
    $stock_card_type === '' ||
    $stock_unit_type === '' ||
    $stock_kdv_value === ''

) {
    
    echo "<script>alert('Zorunlu alanlar boş bırakılamaz');</script>";
    exit;
}

    $stmt = $db->conn->prepare("
        INSERT INTO all_stocks
        (user_id, firm_id, stock_name, stock_price, stock_card_type, stock_unit_type, stock_kdv_value)
        VALUES (?, ?, ?, ?, ?, ?,?)
    ");

    if (!$stmt) {
        echo "<script>alert('Sistem hatası');</script>";
        exit;
    }


    $stmt->bind_param(
        
        "iisdssi",
        $user_id,
        $firm_id,
        $stock_name,
        $stock_price,
        $stock_card_type,
        $stock_unit_type,
        $stock_kdv_value

  );

    if ($stmt->execute()) {
        echo "
        <script>
            alert('Kayıt başarılı!');
            window.location.href = '/action_panel_elements/stock_action_control_panel/add_stock_page.php';
        </script>";
    } else {
    echo "
    <script>
        alert('Kayıt sırasında hata oluştu');
        window.location.href = '/action_panel_elements/stock_action_control_panel/add_stock_page.php';
    </script>";
}

    $stmt->close();
} 

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
    <title>Stok Ekleme Ekranı</title>


<style>

/*<---------------------------------------- Temel Sayfa CSS Kısmı Başlangıç --------------------------------------------------------------------------------->*/


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

/*<---------------------------------------- Temel Sayfa CSS Kısmı Bitiş --------------------------------------------------------------------------------->*/

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


/*<---------------------------------------- Content CSS Kısmı Başlangıç -------------------------------------------------------------->*/

.form_title {
    
    color: #2c3e50;
    text-align: center;
    font-size: clamp(1.5rem, 2.5vw, 3.5rem);
    margin-top: clamp(2rem, 2vw, 4rem); 
    margin-bottom: clamp(2rem, 2vw, 4rem);

}

.main_content {
  
    flex: 1;
    display: flex;
        
}

form {
    
    display: flex;
    flex: 1;

}

.form-group {
    width: 100%;
    max-width: clamp(15rem, 30vw, 50rem);
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}


.stock_infos {
    
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: clamp(0.8rem, 1.5vw, 1.5rem);

}


.stock_infos input,
.stock_infos select {

    width: 100%;
    padding: clamp(0.3rem, 1vw, 1rem);
    border: 0.1rem solid #ccc;
    border-radius: 0.5rem;
    font-size: clamp(0.9rem, 1vw, 1rem);
}

button {
    
    width: 100%;
    cursor: pointer;
    padding: clamp(0.3rem, 1vw, 1rem);
    border: 0.1rem solid #3498db;
    border-radius: 0.5rem;
    font-size: clamp(0.9rem, 1.5vw, 3rem);

}

button:hover {

    color: #F5F5F5;
    background-color: #3498db; 

} 

input:focus,
select:focus {
  outline: none;
  border-color: #3498db;
}

input:focus-visible,
select:focus-visible {
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

/* Mobile düzen */
@media (max-width: 768px) {

  form {
    flex-direction: column;
  }

}
/*<---------------------------------------- Content CSS Kısmı Bitiş ---------------------------------------------------------------->*/


</style>

</head>
<body>
    


<div class="page"> <!-- page -->

<nav class="nav_menu"> <!-- nav_menu -->
            
<div class="active_firm">
        Aktif Firma: <?= $firm_name; ?>
</div>


            <ul class="nav_tools">
                
                <li><a href="/action_panel_elements/action_menus_page.php">İşlem Menüleri</a></li>
                <li><a href="/action_panel_elements/stock_action_control_panel/stock_action_page.php">Stok İşlem Modülleri</a></li>
                
            
            </ul>
</nav> <!-- nav_menu -->



<h1 class="form_title">Stok Ekleme Ekranı</h1>



<div class="main_content"> <!-- main_content -->


<form action="" method="post" autocomplete="off">

<div class="stock_infos">


<div class="form-group">
    <label for="stock_name">Stok Adı:</label>
    <input type="text" id="stock_name" name="stock_name" maxlength="30" required>
</div>

<div class="form-group">
    <label for="stock_price">Stok Birim Başı Fiyatı (Opsiyonel):</label>
    <input type="number" id="stock_price" name="stock_price" maxlength="30">
</div>

<div class="form-group">
  
  <label for="stock_card_type">Stok Türü:</label>
  <select id="stock_card_type" name="stock_card_type" required>
  <option value="">Seçiniz</option>
  <option value="hammadde">Hammadde</option>
  <option value="urun">Ürün</option>
  <option value="hizmet">Hizmet</option>

  </select>
  
</div>

<div class="form-group">
  
  <label for="stock_unit_type">Birim Tipi:</label>
  <select id="stock_unit_type" name="stock_unit_type" required>
    <option value="adet">Adet (A)</option>
    <option value="kg">Kilogram (KG)</option>
    <option value="g">Gram (G)</option>
    <option value="litre">Litre (L)</option>
    <option value="metre">Metre (M)</option>
    <option value="paket">Paket (P)</option>

  </select>
  
</div>



<div class="form-group">
  
  <label for="stock_kdv_value">KDV Oranı (%):</label>
  <select id="stock_kdv_value" name="stock_kdv_value" required>
    
<option value="20" selected>% 20</option>
<option value="10">% 10</option>
<option value="1">% 1</option>
<option value="0">% 0</option>

  </select>
  
</div>

<div class="form-group">
<button type="submit">Stok Ekle</button>
</div>

</div> <!-- stock_infos -->

</form>

 
</div><!-- main_content -->    

</div> <!-- page -->




</body>
</html>