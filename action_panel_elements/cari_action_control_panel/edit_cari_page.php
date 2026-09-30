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

/* -------------------- Cari ID'nin URL'den alınması ve işlem için doğrulanması -------------------- */


$cari_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$cari_id) {
    header("Location: cari_action_page.php");
    exit;
}

/* -------------------------------------------------------------------------------------------------- */


/* ----------------- Cari Verilerini Çekmek için Kullanılan Kısım (Prepared Statement Kullanımı) ------------------------- */

$stmt = $conn->prepare("
    
    SELECT cari_name, cari_surname, cari_type , cari_card_type
    FROM all_cari
    WHERE cari_id = ? AND user_id = ? AND firm_id = ?

");

$stmt->bind_param("iii", $cari_id, $user_id, $firm_id);
$stmt->execute();
$result = $stmt->get_result();
$cari = $result->fetch_assoc();

if (!$cari) {
    header("Location: cari_action_page.php");
    exit;
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
    <title>Cari Bilgileri Güncelleme Ekranı</title>


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


.cari_infos {
    
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: clamp(0.8rem, 1.5vw, 1.5rem);

}


.cari_infos input,
.cari_infos select {

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
                
                <li><a href="/action_panel_elements/action_menus_panel/action_menus_page.php">İşlem Menüleri</a></li>
                <li><a href="/action_panel_elements/cari_action_control_panel/cari_action_page.php">Cari İşlem Modülleri</a></li>
                
            
            </ul>
</nav> <!-- nav_menu -->



<h1 class="form_title">Cari Bilgileri Güncelleme Ekranı</h1>



<div class="main_content"> <!-- main_content -->


<form action="update_cari_page.php" method="post" autocomplete="off">

<div class="cari_infos">

<input type="hidden" name="cari_id" value="<?= $cari_id ?>">

<div class="form-group">
    <label for="cari_name">Cari Adı:</label>
    <input type="text" id="cari_name" name="cari_name" value="<?= htmlspecialchars($cari['cari_name']) ?>" required>
</div>

<div class="form-group">
    <label for="cari_surname">Cari Soyadı:</label>
    <input type="text" id="cari_surname" name="cari_surname" value="<?= htmlspecialchars($cari['cari_surname']) ?>" required>
</div>

<div class="form-group">
  
  <label for="cari_type">Mükellef Türü:</label>
  <select id="cari_type" name="cari_type" required>
        <option value="gercek_kisi"    <?= $cari['cari_type'] == 'gercek_kisi' ? 'selected' : '' ?>>Gerçek Kişi</option>
        <option value="tuzel_kisi"     <?= $cari['cari_type'] == 'tuzel_kisi' ? 'selected' : '' ?>>Tüzel Kişi</option>
        <option value="sirket"         <?= $cari['cari_type'] == 'sirket' ? 'selected' : '' ?>>Şirket</option>
        <option value="kamu_kurumu"    <?= $cari['cari_type'] == 'kamu_kurumu' ? 'selected' : '' ?>>Kamu Kurumu</option>
    </select>
  
</div>

<div class="form-group">
  
  <label for="cari_card_type">Kart Tipi:</label>
  <select id="cari_card_type" name="cari_card_type" required>
    <option value="alici"           <?= $cari['cari_card_type'] == 'alici' ? 'selected' : '' ?>>Alıcı</option>
    <option value="satici"          <?= $cari['cari_card_type'] == 'satici' ? 'selected' : '' ?>>Satıcı</option>
    <option value="alici_ve_satici" <?= $cari['cari_card_type'] == 'alici_ve_satici' ? 'selected' : '' ?>>Alıcı ve Satıcı</option>
    <option value="personel"        <?= $cari['cari_card_type'] == 'personel' ? 'selected' : '' ?>>Personel</option>


  </select>
  
</div>





<div class="form-group">
<button type="submit">Cari Bilgilerini Güncelle</button>
</div>

</div> <!-- cari_infos -->

</form>

 
</div><!-- main_content -->    

</div> <!-- page -->




</body>
</html>