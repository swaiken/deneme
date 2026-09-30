<?php

require_once __DIR__ . '/../baseoops/database.php';
$db->connect();



if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $user_name     = trim($_POST['user_name'] ?? '');
    $user_phone    = trim($_POST['user_phone'] ?? '');
    $user_username = trim($_POST['user_username'] ?? '');
    $user_email    = trim($_POST['user_email'] ?? '');
    $user_password = $_POST['user_password'] ?? '';
    $user_confirm_password = $_POST['user_confirm_password'] ?? '';


    if (
        empty($user_name) ||
        empty($user_username) ||
        empty($user_email) ||
        empty($user_password)
    ) {
        echo "<script>alert('Zorunlu alanlar boş bırakılamaz');</script>";
        exit;
    }

    if ($user_password !== $user_confirm_password) {
        echo "<script>alert('Şifreler aynı değil!');</script>";
        exit;
    }

    if (strlen($user_password) < 8) {
        echo "<script>alert('Şifre en az 8 karakter olmalı');</script>";
        exit;
    }

    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

    $checkStmt = $db->conn->prepare(
        "SELECT user_id FROM all_users WHERE user_email = ? LIMIT 1"
    );
    $checkStmt->bind_param("s", $user_email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<script>alert('Bu email adresi zaten kayıtlı');</script>";
        exit;
    }
    $checkStmt->close();

    $stmt = $db->conn->prepare("
        INSERT INTO all_users 
        (user_name, user_phone, user_username, user_email, user_pass)
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo "<script>alert('Sistem hatası');</script>";
        exit;
    }

    $stmt->bind_param(
        "sssss",
        $user_name,
        $user_phone,
        $user_username,
        $user_email,
        $hashed_password
    );

    if ($stmt->execute()) {
        echo "
        <script>
            alert('Kayıt başarılı!');
            window.location.href = 'https://www.visitblackstone.com';
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
    <title>Kayıt Oluşturma Ekranı</title>

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
   box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);

}



a {

   text-decoration: none;

}

li {

   list-style: none;

}

/*<---------------------------------------- Temel Sayfa CSS Kısmı Bitiş --------------------------------------------------------------------------------->*/


/*<----------------------------------------  Tam Sayfa CSS Kısmı Başlangıç --------------------------------------------------------------------------------->*/


.page {
   min-height: 100vh;
   display: flex;
   flex-direction: column;
}

/*<----------------------------------------  Tam Sayfa CSS Kısmı Bitiş --------------------------------------------------------------------------------->*/


/*<---------------------------------------- Navigasyon CSS Kısmı Başlangıç --------------------------------------------------------------------------------->*/


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

/*<---------------------------------------- Navigasyon CSS Kısmı Bitiş --------------------------------------------------------------------------------->*/


/*<---------------------------------------- Content CSS Kısmı Başlangıç --------------------------------------------------------------------------------->*/

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

.user_infos {
    
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    

}

.entry_infos {
    
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;

}

.user_infos h2,
.entry_infos h2 {
  
    color: #2c3e50;
    font-size: clamp(1rem, 2vw, 3rem);

}

.user_infos input,
.entry_infos input {

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
    max-width: clamp(15rem, 30vw, 50rem);
    padding: clamp(0.3rem, 1vw, 1rem);
    border: 0.1rem solid #3498db;
    border-radius: 0.5rem;
    font-size: clamp(0.9rem, 1.5vw, 3rem);
    margin-top: clamp(0.3rem, 1vw, 1rem);
    margin-bottom: clamp(0.3rem, 1vw, 1rem);
    cursor: pointer;

}

button:hover {

    color: #F5F5F5;
    background-color: #3498db; 

} 

input:focus {
  
  outline: none;

}
.user_infos input:focus,
.entry_infos input:focus {
  
   
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

/*<---------------------------------------- Content CSS Kısmı Bitiş --------------------------------------------------------------------------------->*/



/*<---------------------------------------- Footer CSS Kısmı Başlangıç --------------------------------------------------------------------------------->*/

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

/*<---------------------------------------- Footer CSS Kısmı Bitiş --------------------------------------------------------------------------------->*/

</style>


</head>
<body>
    

<div class="page">

<nav class="nav_menu">
            
            <ul class="nav_tools">
                
                <li><a href="/main_page_elements/about_us_page.php">Hakkımızda</a></li>
                <li><a href="https://www.visitblackstone.com">Ana Sayfa</a></li>
                <li><a href="/main_page_elements/entry_page.php">Giriş Yap</a></li>
                
            </ul>
</nav>



<h1 class="form_title">Kayıt İşlemleri</h1>

<div class="main_content"> <!-- main_content -->
   
<form action="" method="post" autocomplete="off">






<div class="user_infos"> <!-- user_infos -->
  
<h2>İşletme veya Kullanıcı Bilgileri</h2>


  
    <label for="user_name">İşletme İsmi</label>
    <input type="text" id="user_name" name="user_name" maxlength="30" required>



    <label for="user_phone">İşletme Telefon Numarası</label>
    <input type="tel" id="user_phone" name="user_phone" pattern="[0-9]{10}" placeholder="5xxxxxxxxx" required>

</div> <!-- user_infos -->


<div class="entry_infos"> <!-- entry_infos -->

<h2>Giriş Bilgileri</h2>


 
    <label for="user_username">Kullanıcı Adı</label>
    <input type="text" id="user_username" name="user_username" maxlength="30" required>



    <label for="user_email">Email</label>
    <input type="email" id="user_email" name="user_email" maxlength="30" required>



    <label for="user_password">Şifre</label>
    <input type="password" id="user_password" name="user_password" minlength="8" required>



    <label for="user_confirm_password">Şifre Tekrar</label>
    <input type="password" id="user_confirm_password" name="user_confirm_password" minlength="8" required>



    <button type="submit">Kayıt Ol</button>

</div> <!-- entry_infos -->


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