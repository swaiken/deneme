<?php

session_start();

/* -------- Bu sayfa cache'lenmesini önlemek amaçlı kısım (login sonrası özel içerik)  ----------- */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

/* -------------------------------------------------------------------------------------------------- */

require_once __DIR__ . '/../baseoops/database.php';
$db->connect();
$conn = $db->conn;



/* Login kontrolü */
if (!isset($_SESSION['user_id'])) {
    header("Location: https://www.visitblackstone.com");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* Kullanıcıya ait firmaları çek */
$stmt = $conn->prepare("
    SELECT firm_id, firm_name, firm_folder_name
    FROM all_firms
    WHERE user_id = ?
    ORDER BY firm_name ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol Paneli</title>

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
   box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
   overflow: hidden;

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
    justify-content: flex-end;
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

.logout_buton{

    font-size: clamp(1rem, 2vw, 1.5rem);
    padding: 0.5rem;
    border-radius: 0.5rem;
    border: 0.2rem solid #e74c3c;
    color: #34495e;
    transition: background-color 0.3s ease, color 0.3s ease;
    cursor: pointer;

}

.logout_buton:hover {
  
    color: #F5F5F5;
    background-color: #e74c3c; 

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
   flex-direction: column;
   min-height: 0;
       
}

.inner_content {
   flex: 1;
   display: flex;
   gap: 3vw;
   min-height: 0;
}

.folder_entry{

   
   border-radius: 12px;
   display: flex;
   flex: 1;
   flex-direction: column;

}


.updates_panel{

   border: 5px solid #ECECEC;
   border-radius: 12px;
   flex: 1;
   overflow-y: auto;

}

.update_notes {

    color: #2c3e50;
    text-align: center;
    font-size: clamp(1rem, 2vw, 2.5rem);
    margin-top: clamp(2rem, 2vw, 4rem); 
    margin-bottom: clamp(2rem, 2vw, 4rem);
    border-bottom: 1px solid #ddd;
    padding-bottom: 0.75rem;

}

.update_item{

    border-bottom: 1px solid #ddd;
    padding-top: 1.25rem;
    padding-bottom: 1.25rem;
    padding-left: 1.25rem;

}

.update_header{

    padding-top: 1.25rem;
    padding-bottom: 1.25rem;
    
}

.update_version{

    padding-right: 3rem;

}


.search_tool{
  
    padding: 1rem;
    display: flex;
    align-items: center;
    
}


.all_firms{

    border: 5px solid #ECECEC;
    border-radius: 12px;
    display: flex;
    flex: 6;
    padding: 1rem;
    flex-direction: column;
    overflow-y: auto;
    gap: 1rem;

    

}

.buttons{

    
    display: flex;
    flex: 1;
    flex-direction: row;
    justify-content: space-between;
    align-items: center; 
    gap: 2rem;
    
}

.entry_button,
.new_firm_button {
    
    display: flex;
    flex: 1;
    cursor: pointer;
    padding: 0.5rem;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: clamp(0.5rem, 1.5vw, 2rem);
    background-color: #F5F5F5;
    color: #34495e;
    border: 0.2rem solid #3498db;
    transition: background-color 0.3s ease, color 0.3s ease;
    border-radius: 0.5rem;
    align-items: center;        
    justify-content: center;    
    text-align: center;

}

.entry_button:hover,
.new_firm_button:hover {

    color: #F5F5F5;
    background-color: #3498db; 

}



.search_tool{
    height: 3rem;
    font-size: 1rem;
    border-radius: 12px;
    margin-bottom: 1rem;
}

.search_input{
    
    width: 100%;
    padding-left: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    border: 1px solid #ccc;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23999' viewBox='0 0 24 24'%3E%3Cpath d='M10 2a8 8 0 105.293 14.293l4.707 4.707 1.414-1.414-4.707-4.707A8 8 0 0010 2zm0 2a6 6 0 110 12 6 6 0 010-12z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 0.75rem center;
    background-size: 1rem;

}

.search_input:focus {
    
    border-color: #3498db;
    outline: none;

}




.firms_list {

    
    padding-top: 1.25rem;
    padding-bottom: 1.25rem;
    padding-left: 1.25rem;
    border: 0.2rem solid #3498db;
    background-color: #F5F5F5;
    border-radius: 0.5rem;
    transition: background-color 0.3s ease, color 0.3s ease;
    font-size: clamp(0.5rem, 1.5vw, 2rem);
    cursor: pointer;
    display: block;

}



.firms_list:hover {

    
    border: 0.2rem solid #3498db;

}


.firms_list.active {
    background-color: #3498db;
    color: #F5F5F5;
}
@media (max-width: 768px) {
  
  .inner_content {
    flex-direction: column;
  }
}


/*<---------------------------------------- Content CSS Kısmı Bitiş -------------------------------------------------------------->*/

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
            
    <div class="active_firm">Hesap: <?php echo htmlspecialchars($_SESSION['user_username'] ?? 'Bilinmiyor'); ?></div>

            <ul class="nav_tools">
                
        <li>            
            <form action="/control_panel_elements/logout_page.php" method="POST">
                <button type="submit" class="logout_buton">Çıkış Yap</button>
            </form>
        </li>
                
                
            </ul>
</nav>





<div class="main_content"> <!-- main_content -->



   <h1 class="form_title">Kontrol Paneli</h1>

<div class="inner_content" > <!-- inner_content -->

<div class="updates_panel">


    <h3 class="update_notes" >Güncelleme Notları</h3>

<div class="update_item">
        <div class="update_header">
            <span class="update_version">v0.0.1</span>
            <span class="update_date">12.01.2026</span>
        </div>

        <p class="update_text">
            2026 KDV oranları güncellendi.
            <a href="#" class="update_link">Detaylar →</a>
        </p>
    </div>

    <div class="update_item">
        <div class="update_header">
            <span class="update_version">v0.0.0</span>
            <span class="update_date">05.01.2026</span>
        </div>

        <p class="update_text">
            E-Fatura alanında performans iyileştirmeleri yapıldı.
            <a href="#" class="update_link">Detaylar →</a>
        </p>
    </div>

    <div class="update_item">
        <div class="update_header">
            <span class="update_version">v0.0.0</span>
            <span class="update_date">28.12.2025</span>
        </div>

        <p class="update_text">
            Muhasebe kayıt ekranında küçük hata düzeltmeleri.
            <a href="#" class="update_link">Detaylar →</a>
        </p>
    </div>
    
    
</div>



<div class="folder_entry">


<div class="search_tool">
    <input type="text" class="search_input" placeholder="Firma adı">
</div>


 <?php
/*
 Aşağıdaki blok:
 - Giriş yapan kullanıcıya ait firmaları veritabanından alır
 - Her firmayı tıklanabilir bir liste elemanı olarak ekrana basar
 - Firma ID bilgisini data-attribute ile HTML içine gömer
 - Böylece JavaScript tarafında firma seçimi, filtreleme ve form gönderimi mümkün olur
 - Firma adı güvenlik amacıyla htmlspecialchars ile ekrana yazdırılır (XSS koruması)
 - Eğer kullanıcıya ait hiç firma yoksa bilgilendirici mesaj gösterilir
*/
?>


<div class="all_firms" id="firmList">

<?php if ($result->num_rows > 0): ?>

    <?php 
        
        while ($firm = $result->fetch_assoc()): 
        $firmId   = (int) $firm['firm_id'];
        $firmName = htmlspecialchars($firm['firm_folder_name']);
    
    ?>

        <div class="firms_list" data-firm-id="<?= $firmId; ?>" tabindex="0">
            <?= $firmName; ?>
        </div>


    <?php endwhile; ?>

<?php else: ?>

    <p>Henüz eklenmiş firma bulunmuyor.</p>

<?php endif; ?>

</div>


<form action="/action_panel_elements/action_menus_panel/action_menus_page.php" method="POST" id="firmForm" class="buttons">
    
    <input type="hidden" name="firm_id" id="selectedFirmId">

    <button type="submit" class="entry_button">
        Giriş
    </button>

    <a href="/control_panel_elements/add_firm_page.php" class="new_firm_button">
        Yeni Firma Ekle
    </a>
</form>

</div>
</div>
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


<script>

const firms = document.querySelectorAll('.firms_list'); // Sayfadaki tüm firma satırlarını seçerek kullanıcı etkileşimlerini 
                                                        // yönetmek için alıyoruz

const hidden = document.getElementById('selectedFirmId'); // Seçilen firmanın ID bilgisini form ile PHP'ye göndermek 
                                                          // için kullanılan gizli input'u seçiyoruz

const form = document.getElementById('firmForm');         // Seçilen firma bilgisi ile formu otomatik göndermek için form elementini seçiyoruz
     
const search = document.querySelector('.search_input');  // Kullanıcının firma araması yapabilmesi için arama input alanını seçiyoruz


let active = null; // Şu anda seçili olan firma elementini tutar (başlangıçta yok)


/* Firma seçimi 

// Firma listesi üzerinde tıklama olayını dinler:
// - Önceki aktif firmayı pasif yapar
// - Tıklanan firmayı aktif yapar
// - Seçilen firmanın ID'sini gizli input'a yazar */

firms.forEach(f => {
    f.addEventListener('click', () => {
        if (active) active.classList.remove('active');
        f.classList.add('active');
        active = f;
        hidden.value = f.dataset.firmId;
    });
});

/* Arama 

// Arama input'una yazıldıkça firma listesini canlı olarak filtreler
 */

search.addEventListener('keyup', () => {
    const val = search.value.toLowerCase();
    firms.forEach(f => {
        f.style.display = f.textContent.toLowerCase().includes(val)
            ? 'block'
            : 'none';
    });
});

/* Form güvenliği

// Kullanıcı firma seçmeden formu göndermeye çalışırsa işlemi durdurur ve uyarı verir
*/

form.addEventListener('submit', e => {
    if (!hidden.value) {
        e.preventDefault();
        alert('Lütfen bir firma seçiniz.');
    }
});

</script>


</body>
</html>