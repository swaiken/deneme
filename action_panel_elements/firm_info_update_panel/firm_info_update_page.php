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

/* -------------------- Giriş Yapılan Firmanın Bilgilerine Ulaşılan Kısım -------------------- */

$stmt = $conn->prepare("
    SELECT *
    FROM all_firms
    WHERE firm_id = ?
    AND user_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $firm_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Geçersiz firma ID veya bu firmaya erişiminiz yok.");
}

$firm = $result->fetch_assoc();

/* -------------------------------------------------------------------------------------------------- */

/* -------------------- Post ile gönderilecek Verileri Update Yapılacak Kısım -------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firm_folder_name   = trim($_POST["firm_folder_name"]);
    $firm_name          = trim($_POST["firm_name"]);
    $firm_surname       = trim($_POST["firm_surname"]);
    $firm_country       = trim($_POST["country"]);
    $firm_town          = trim($_POST["town"]);
    $firm_district      = trim($_POST["district"]);
    $firm_street        = trim($_POST["street"]);
    $firm_street2       = trim($_POST["street2"]);
    $firm_door_out      = trim($_POST["door_out"]);
    $firm_door_in       = trim($_POST["door_in"]);
    $firm_phone_number  = trim($_POST["phone_number"]);

    $update = $conn->prepare("
        UPDATE all_firms SET
            firm_folder_name   = ?,
            firm_name          = ?,
            firm_surname       = ?,
            firm_country       = ?,
            firm_town          = ?,
            firm_district      = ?,
            firm_street        = ?,
            firm_street2       = ?,
            firm_door_out      = ?,
            firm_door_in       = ?,
            firm_phone_number  = ?
        WHERE firm_id = ? AND user_id = ?
    ");

    $update->bind_param(
        "ssssssssssiii",
        $firm_folder_name,
        $firm_name,
        $firm_surname,
        $firm_country,
        $firm_town,
        $firm_district,
        $firm_street,
        $firm_street2,
        $firm_door_out,
        $firm_door_in,
        $firm_phone_number,
        $firm_id,
        $user_id
    );

    if ($update->execute()) {
    echo "
    <script>
        alert('Kayıt başarılı!');
        window.location.href = '/action_panel_elements/firm_info_update_panel/firm_info_update_page.php';
    </script>";
} else {
    echo "<script>alert('Kayıt sırasında hata oluştu');</script>";
}

$update->close();
} 

/* --------------------------------------------------------------------------------------------------------------------- */


/* -------------------- Firma Adını Çekmek için Kullanılan Kısım  -------------------- */

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
    <title>Firma Düzenleme Ekranı</title>

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
    justify-content: center;
}

.main_content h2 {

    text-align: center;
    margin-bottom: clamp(2rem, 2vw, 4rem);
    

}

form {
    
    display: flex;
    flex-wrap: wrap;
    flex: 1;
    gap: clamp(1rem, 2vw, 3rem);

}

.part_1 {
    
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    

}

.part_2 {
    
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;

}



.part_1 input,
.part_2 input {

    width: 100%;
    max-width: clamp(15rem, 30vw, 50rem);
    padding: clamp(0.3rem, 1vw, 1rem);
    border: 0.1rem solid #ccc;
    border-radius: 0.5rem;
    font-size: clamp(0.9rem, 1vw, 1rem);
    margin-top: clamp(0.3rem, 1vw, 1rem);
    margin-bottom: clamp(0.3rem, 1vw, 1rem);

}

button {

    color: #34495e;
    width: 100%;
    cursor: pointer;
    max-width: clamp(15rem, 30vw, 50rem);
    padding: clamp(0.3rem, 1vw, 1rem);
    border: 0.1rem solid #3498db;
    border-radius: 0.5rem;
    font-size: clamp(0.9rem, 1.5vw, 3rem);
    margin-top: clamp(1.5rem, 2.5vw, 3.5rem);
    margin-bottom: clamp(0.3rem, 1vw, 1rem);

}

button:hover {

    color: #F5F5F5;
    background-color: #3498db; 

} 

input:focus {
  
  outline: none;

}
.part_1 input:focus,
.part_2 input:focus {
  
   
   border-color: #3498db;

}

input:focus-visible {
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

/* Chrome, Edge, Safari */
input[type=number]::-webkit-outer-spin-button,
input[type=number]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    appearance: textfield;
    -moz-appearance: textfield;
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
    

<div class="page">

<nav class="nav_menu">
            

    <div class="active_firm">
        Firma: <?= $firm_name; ?>
    </div>


            <ul class="nav_tools">
                
                <li><a href="/control_panel_elements/control_panel_page.php">Kontrol Paneli</a></li>
                <li><a href="/action_panel_elements/action_menus_panel/action_menus_page.php">İşlem Menüleri</a></li>               
                
            </ul>
</nav>



<h1 class="form_title">Firma Düzenleme Ekranı</h1>

<div class="main_content"> <!-- main_content -->
   
<form action="" method="post" autocomplete="off">






<div class="part_1"> <!-- part_1 -->
  
  
    <label for="firm_folder_name">Firma Klasör Adı:</label>
    <input type="text" id="firm_folder_name" name="firm_folder_name" maxlength="30"
    value="<?= htmlspecialchars($firm['firm_folder_name']) ?>" required>
                        
                       
    <label for="firm_name">Firma Adı:</label>
    <input type="text" id="firm_name" name="firm_name" maxlength="30" required
    value="<?= htmlspecialchars($firm['firm_name']) ?>">
                     
                       
    <label for="firm_surname">Firma Soyadı:</label>
    <input type="text" id="firm_surname" name="firm_surname" maxlength="30"
    value="<?= htmlspecialchars($firm['firm_surname']) ?>" required>


    <label for="country">İl:</label>
    <input type="text" id="country" name="country" maxlength="30"
    value="<?= htmlspecialchars($firm['firm_country']) ?>" required>
                       
                        
    <label for="town">İlçe:</label>
    <input type="text" id="town" name="town" maxlength="30"
    value="<?= htmlspecialchars($firm['firm_town']) ?>" required>
                  
                        
    <label for="district">Mahalle:</label>
    <input type="text" id="district" name="district" maxlength="30"
    value="<?= htmlspecialchars($firm['firm_district']) ?>" required>


</div> <!-- part_1 -->


<div class="part_2"> <!-- part_2 -->

 

                       
    <label for="street">Cadde:</label>
    <input type="text" id="street" name="street" maxlength="30"
    value="<?= htmlspecialchars($firm['firm_street']) ?>" required>
                      
                       
    <label for="street2">Sokak:</label>
    <input type="text" id="street2" name="street2" maxlength="30"
    value="<?= htmlspecialchars($firm['firm_street2']) ?>" required>
                       
                       
    <label for="door_out">Dış kapı no:</label>
    <input type="text" id="door_out" name="door_out" inputmode="numeric" maxlength="30"
    value="<?= htmlspecialchars($firm['firm_door_out']) ?>" required>
                      
                       
    <label for="door_in">İç kapı no:</label>
    <input type="text" id="door_in" name="door_in" inputmode="numeric" maxlength="30"
    value="<?= htmlspecialchars($firm['firm_door_in']) ?>" required>
                        
                        
    <label for="phone_number">İşletme Telefon Numarası:</label>
    <input type="number" id="phone_number" name="phone_number" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" placeholder="5xxxxxxxxx"
    value="<?= htmlspecialchars($firm['firm_phone_number']) ?>" required>



    <button type="submit">Firmayı Düzenle</button>

</div> <!-- part_2 -->


</form>
   


</div> <!-- main_content -->


</div> <!-- page -->



</body>
</html>