<?php

require_once __DIR__ . '/../baseoops/database.php';
$db->connect();

session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_username = trim($_POST['user_username'] ?? '');
    $user_password = $_POST['user_password'] ?? '';

    if (empty($user_username) || empty($user_password)) {
        $error = 'Kullanıcı adı ve şifre zorunludur';
    } else {

        $stmt = $db->conn->prepare(
            "SELECT user_id, user_username, user_pass 
             FROM all_users 
             WHERE user_username = ? 
             LIMIT 1"
        );

        if (!$stmt) {
            $error = 'Sistem hatası oluştu';
        } else {

            $stmt->bind_param("s", $user_username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows !== 1) {
                $error = 'Kullanıcı adı veya şifre hatalı';
            } else {

                $user = $result->fetch_assoc();

                if (!password_verify($user_password, $user['user_pass'])) {
                    $error = 'Kullanıcı adı veya şifre hatalı';
                }
            }

            $stmt->close();
        }
    }

    if (empty($error)) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_username'] = $user['user_username'];

        header("Location: /control_panel_elements/control_panel_page.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş İşlemleri</title>

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

a { text-decoration: none; }
li { list-style: none; }



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


/*<---------------------------------------- Entry CSS Kısmı Başlangıç --------------------------------------------------------------------------------->*/

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


.form_title {
    color: #2c3e50;
    text-align: center;
    font-size: clamp(1.5rem, 2.5vw, 3.5rem);
    margin: 3rem 0;
}

.main_content {
    flex: 1;
    display: flex;
}

form {
    flex: 1;
    display: flex;
    justify-content: center;
}

.entry_infos {
    width: 100%;
    max-width: 500px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.form_error {
    width: 100%;
    padding: 0.8rem 1rem;
    margin-bottom: 1.5rem;
    background-color: #fdecea;
    color: #b00020;
    border-radius: 0.5rem;
    text-align: center;
    font-size: 0.95rem;
}

.entry_infos label {
    margin-top: 1rem;
}

.entry_infos input {
    width: 100%;
    padding: 0.8rem;
    border-radius: 0.5rem;
    border: 1px solid #ccc;
    margin-top: 0.3rem;
}

.entry_infos input:focus {
    outline: none;
    border-color: #3498db;
}

button {
    width: 100%;
    padding: 0.8rem;
    margin-top: 3rem;
    border-radius: 0.5rem;
    border: 0.1rem solid #3498db;
    cursor: pointer;
}

button:hover {
    background-color: #3498db;
    color: #fff;
}

/*<---------------------------------------- Entry CSS Kısmı Bitiş --------------------------------------------------------------------------------->*/



/*<---------------------------------------- Footer CSS Kısmı Başlangıç --------------------------------------------------------------------------------->*/

footer{
    padding: clamp(1rem, 1vw, 3rem); 
    text-align: center;
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
                <li><a href="/main_page_elements/sign_up_page.php">Kayıt Ol</a></li>
                
            </ul>
</nav>



<h1 class="form_title">Giriş İşlemleri</h1>

<div class="main_content"> <!-- main_content -->
   
<form action="" method="post" autocomplete="off">




<div class="entry_infos"> <!-- entry_infos -->

<?php if (!empty($error)): ?>
    <div class="form_error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>


 
    <label for="user_username">Kullanıcı Adı</label>
    <input type="text" id="user_username" name="user_username" maxlength="30" required>



    <label for="user_password">Şifre</label>
    <input type="password" id="user_password" name="user_password" minlength="8" required>





    <button type="submit">Giriş Yap</button>

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