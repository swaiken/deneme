<?php

session_start();

/* -------- Bu sayfa cache'lenmesini önlemek amaçlı kısım (login sonrası özel içerik)  ----------- */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

/* -------------------------------------------------------------------------------------------------- */

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlackStone Muhasebe Sistemi</title>

<style>

* {
  
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  list-style: none;
  text-decoration: none;
  

}


body {
   
   font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
   background-color: #f2f6fb;
   box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}


/*<---------------------------------------- Navigasyon CSS Kısmı Başlangıç --------------------------------------------------------------------------------->*/

.nav_menu {

    width: 100vw;
    min-height: 7vh;
    padding-right: 2vw;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    background-color: #F5F5F5;
    
}

.nav_tools{
   
    font-size: clamp(1rem, 2vw, 1.5rem);
    display: flex;
    column-gap:2vw; 


}

.nav_menu a{

    padding: 0.5rem;
    border-radius: 0.5rem;
    border: 0.2rem solid #3498db;
    color: #34495e;
}

.nav_menu a:hover {
  
    padding: 0.5rem;
    border-radius: 0.5rem;
    border: 0.2rem solid #3498db;
    color: #F5F5F5;
    background-color: #3498db;
    transition: background-color 0.3s ease, color 0.3s ease;
    

}

/*<---------------------------------------- Navigasyon CSS Kısmı Bitiş --------------------------------------------------------------------------------->*/


/*<---------------------------------------- Başlık CSS Kısmı Başlangıç --------------------------------------------------------------------------------->*/

.header-text {
    text-align: center;
    width: 100vw;
    min-height: 83vh;
    color: #2c3e50;

    
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    background-image: 
        radial-gradient(circle at 20% 30%, rgba(0,0,0,0.01) 0%, transparent 40%),
        radial-gradient(circle at 50% 60%, rgba(0,0,0,0.008) 0%, transparent 45%),
        radial-gradient(circle at 75% 20%, rgba(0,0,0,0.012) 0%, transparent 35%);
    background-blend-mode: overlay;
}

.header-text h1 {
   padding-top: 8%;
   font-size: clamp(2rem, 5vw, 5rem);
   
}

.header-text p {
   padding-top: 8%;
   font-size: clamp(1rem, 2vw, 2.5rem);
   

}

/*<---------------------------------------- Başlık CSS Kısmı Bitiş --------------------------------------------------------------------------------->*/


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
 
    

    
    
    <nav class="nav_menu">
        <ul class="nav_tools">
            <li><a href="/main_page_elements/about_us_page.php">Hakkımızda</a></li>
            <li><a href="/main_page_elements/sign_up_page.php">Kayıt Ol</a></li>
            <li><a href="/main_page_elements/entry_page.php">Giriş Yap</a></li>
        </ul>
    </nav>

<header>

    <div class="header-text">
      <h1>BlackStone Muhasebe</h1>
      <p>BlackStone Muhasebe sistemine hoş geldiniz. Finansal süreçlerinizi güvenle yönetin.</p>
    </div>

</header>



<footer>
    <p>İletişim: info@blackstone.com | Tel: +90 555 555 55 55</p>
    <p>
      <a href="#">Facebook</a> |
      <a href="#">Instagram</a> |
      <a href="#">X (Twitter)</a>
    </p>
</footer>


</body>
</html>