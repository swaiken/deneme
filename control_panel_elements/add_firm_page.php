<?php

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: https://www.visitblackstone.com");
    exit;
}

require_once __DIR__ . '/../baseoops/database.php';
$db->connect();



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firm_folder_name         = trim($_POST['firm_folder_name'] ?? '');
    $firm_name                = trim($_POST['firm_name'] ?? '');
    $firm_surname             = trim($_POST['firm_surname'] ?? '');
    $firm_country             = trim($_POST['firm_country'] ?? '');
    $firm_town                = trim($_POST['firm_town'] ?? '');
    $firm_district            = trim($_POST['firm_district'] ?? '');
    $firm_street              = trim($_POST['firm_street'] ?? '');
    $firm_street2             = trim($_POST['firm_street2'] ?? '');
    $firm_door_out            = trim($_POST['firm_door_out'] ?? '');
    $firm_door_in             = trim($_POST['firm_door_in'] ?? '');
    $firm_phone_number        = trim($_POST['firm_phone_number'] ?? '');

    if (
        empty($firm_folder_name) ||
        empty($firm_name) ||
        empty($firm_surname) ||
        empty($firm_country) ||
        empty($firm_town) ||
        empty($firm_district) ||
        empty($firm_street) ||
        empty($firm_street2) ||
        empty($firm_door_out) ||
        empty($firm_door_in) ||
        empty($firm_phone_number)
    ) {
        echo "<script>alert('Zorunlu alanlar boş bırakılamaz');</script>";
        exit;
    }

    $stmt = $db->conn->prepare("
        INSERT INTO all_firms 
        (user_id, firm_folder_name, firm_name, firm_surname, firm_country, 
         firm_town, firm_district, firm_street, firm_street2, 
         firm_door_out, firm_door_in, firm_phone_number)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo "<script>alert('Sistem hatası');</script>";
        exit;
    }


    if (!preg_match('/^[0-9]{10}$/', $firm_phone_number)) {
    echo "<script>alert('Telefon numarası geçersiz');</script>";
    exit;
}


    $stmt->bind_param(
        "isssssssssss",
        $user_id,
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
        $firm_phone_number
    );

    if ($stmt->execute()) {
        echo "
        <script>
            alert('Kayıt başarılı!');
            window.location.href = 'https://www.visitblackstone.com/main_pages/add_firm.php';
        </script>";
    } else {
        echo "<script>alert('Kayıt sırasında hata oluştu');</script>";
    }

    $stmt->close();
} 



?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Ekleme Ekranı</title>

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
    justify-content: flex-end;
    background-color: #F5F5F5;

}

.nav_tools{
   
    font-size: clamp(1rem, 2vw, 1.5rem);
    display: flex;
    gap: clamp(1rem, 2vw, 2rem);

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

.firm_infos {
    
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    

}

.address_infos {
    
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;

}

.firm_infos h2,
.address_infos h2 {
  
    color: #2c3e50;
    font-size: clamp(1rem, 2vw, 3rem);

}

.firm_infos input,
.address_infos input {

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
    margin-top: clamp(0.3rem, 1vw, 1rem);
    margin-bottom: clamp(0.3rem, 1vw, 1rem);

}

button:hover {

    color: #F5F5F5;
    background-color: #3498db; 

} 

input:focus {
  
  outline: none;

}
.firm_infos input:focus,
.address_infos input:focus {
  
   
   border-color: #3498db;

}

input:focus-visible {
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



/*<---------------------------------------- Footer CSS Kısmı Başlangıç --------------------------------------------------------------->*/

footer{
    
    display: flex;
    padding: clamp(1rem, 1vw, 3rem); 
    flex-direction: column;
    justify-content: center;
    align-items: center;
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

/*<---------------------------------------- Footer CSS Kısmı Bitiş ---------------------------------------------------------------->*/

</style>


</head>
<body>
    

<div class="page">

<nav class="nav_menu">
            
            <ul class="nav_tools">
                
                <li><a href="https://www.visitblackstone.com">Ana Sayfa</a></li>
                <li><a href="/main_pages/control_panel.php">Kontrol Paneli</a></li>
                <li><a href="/main_pages/about_us_page.php">Hakkımızda</a></li>
                
            </ul>
</nav>



<h1 class="form_title">Firma Ekleme Ekranı</h1>

<div class="main_content"> <!-- main_content -->
   
<form action="" method="post" autocomplete="off">






<div class="firm_infos"> <!-- firm_infos -->
  
<h2>Firma Kimlik Bilgileri</h2>


  
    <label for="firm_folder_name">Firma Klasör Adı:</label>
    <input type="text" id="firm_folder_name" name="firm_folder_name" maxlength="30" required>
                        
                       
    <label for="firm_name">Firma Adı:</label>
    <input type="text" id="firm_name" name="firm_name" maxlength="30" required>
                     
                       
    <label for="firm_surname">Firma Soyadı:</label>
    <input type="text" id="firm_surname" name="firm_surname" maxlength="30" required>

</div> <!-- firm_infos -->


<div class="address_infos"> <!-- address_infos -->

<h2>Firma Adres Bilgileri</h2>


 
    <label for="country">İl:</label>
    <input type="text" id="country" name="firm_country" maxlength="30" required>
                       
                        
    <label for="town">İlçe:</label>
    <input type="text" id="town" name="firm_town" maxlength="30" required>
                  
                        
    <label for="district">Mahalle:</label>
    <input type="text" id="district" name="firm_district" maxlength="30" required>
                       
    <label for="street">Cadde:</label>
    <input type="text" id="street" name="firm_street" maxlength="30" required>
                      
                       
    <label for="street2">Sokak:</label>
    <input type="text" id="street2" name="firm_street2" maxlength="30" required>
                       
                       
    <label for="door_out">Dış kapı no:</label>
    <input type="text" id="door_out" name="firm_door_out" inputmode="numeric" maxlength="30" required>
                      
                       
    <label for="door_in">İç kapı no:</label>
    <input type="text" id="door_in" name="firm_door_in" inputmode="numeric" maxlength="30" required>
                        
                        
    <label for="phone_number">İşletme Telefon Numarası:</label>
    <input type="text" id="phone_number" name="firm_phone_number" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" placeholder="5xxxxxxxxx" required>



    <button type="submit">Firmayı Ekle</button>

</div> <!-- address_infos -->


</form>
   


</div> <!-- main_content -->





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