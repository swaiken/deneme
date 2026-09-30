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

/* ----------------- Cari Listesinin Çekildiği Kısım ---------------------------------------------------------------------------------------------------- */

$cari_list = [];

$stmt = $conn->prepare("
    SELECT cari_id, cari_name, cari_card_type
    FROM all_cari
    WHERE firm_id = ? AND user_id = ?
    ORDER BY cari_name ASC
");
$stmt->bind_param("ii", $firm_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $cari_list[] = $row;
}

/* --------------------------------------------------------------------------------------------------------------------- */


/* ------------------------   Bakiylerin Hesaplandığı Kısım ---------------------------------------------------------- */

foreach ($cari_list as &$cari) {

    $cari_id = $cari['cari_id'];

    // Toplam Gelir ve Satış Tutarlarının Hesaplandığı Kısım
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(grand_total),0) AS total
        FROM all_invoices
        WHERE cari_id = ?
        AND firm_id = ?
        AND invoice_type IN ('satis','gelir')
    ");
    $stmt->bind_param("ii", $cari_id, $firm_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $income = $row['total'];

    // Toplam Alış ve Gider Tutarlarının Hesaplandığı Kısım
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(grand_total),0) AS total
        FROM all_invoices
        WHERE cari_id = ?
        AND firm_id = ?
        AND invoice_type IN ('alis','gider')
    ");
    $stmt->bind_param("ii", $cari_id, $firm_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $expense = $row['total'];



    // Bakiyelerin Sonuçlarının Hesaplandığı Kısım
    $cari['balance'] = $income - $expense;
    
}
unset($cari);


/* --------------------------------------------------------------------------------------------------------------------- */


?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Modülleri</title>
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

/*<---------------------------------------- Navigasyon CSS Kısmı Bitiş ---------------------------------------------------------------->*/

/*<---------------------------------------- Menü CSS Kısmı Başlangıç ---------------------------------------------------------------->*/

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
    flex-direction: column;
    

}

.filter_tools {
   
    display: flex;
    justify-content: flex-start;
    align-items: center;
    font-size: clamp(0.5rem, 1.2vw, 2rem);
    flex: 1;
    gap: 1rem;
    border-radius: 0.5rem;
    border: 3px solid #ccc;
    
 
}


.filter_title{

    font-size: clamp(0.9rem, 1vw, 1.1rem);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #7f8c8d;
    font-weight: 600;
    margin-left: 1rem;

}



.cari_search {

    width: 20%;
    padding-left: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    border: 1px solid #ccc;
    font-size: clamp(0.9rem, 1vw, 1.2rem);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23999' viewBox='0 0 24 24'%3E%3Cpath d='M10 2a8 8 0 105.293 14.293l4.707 4.707 1.414-1.414-4.707-4.707A8 8 0 0010 2zm0 2a6 6 0 110 12 6 6 0 010-12z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 0.75rem center;
    background-size: 1rem;
    

}

.cari_card_type {

    width: 20%;
    padding-left: 1rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    font-size: clamp(0.9rem, 1vw, 1.2rem);
    border: 1px solid #ccc;

}


.cari_lists {
    flex: 8;
    border: 2px solid #555; /* ön planda olsun diye koyu gri */
    border-radius: 0.5rem;
   
}

.cari_table_container {
    
    max-height: 60vh;   /* tablo alanı için scroll yüksekliği */
    overflow-y: auto;   /* sadece tablo scroll */
    overflow-x: auto;
}

.cari_table thead {
    position: sticky;
    top: 0;
    background-color: #ecf0f1;
    z-index: 1;
}

.cari_table tbody tr:hover {
    cursor: pointer;
    background-color: #eef6ff;
}

.cari_buttons {

    display: flex;
    flex: 1;
    justify-content: space-around;
    align-items: center;
    background-color: #F5F5F5;
    border-radius: 0.5rem;
    border: 3px solid #ccc;
    
}

.new_cari_button {

    text-align: center;
    padding: 0.5rem;
    font-size: clamp(1rem, 2vw, 1.5rem);
    width: clamp(10rem, 15vw, 15rem);
    border-radius: 0.5rem;
    border: 0.2rem solid #3498db;
    color: #34495e;
    transition: background-color 0.3s ease, color 0.3s ease;

}

.new_cari_button:hover {
  
    color: #F5F5F5;
    background-color: #3498db; 

}

.cari_update {

    text-align: center;
    padding: 0.5rem;
    font-size: clamp(1rem, 2vw, 1.5rem);
    width: clamp(10rem, 15vw, 15rem);
    border-radius: 0.5rem;
    border: 0.2rem solid #27ae60;
    color: #34495e;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;    

}

.cari_update:hover {
  
    color: #F5F5F5;
    background-color: #27ae60; 

}

.cari_delete {

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

.cari_delete:hover {
  
    color: #F5F5F5;
    background-color: #e74c3c; 

}

.cari_inside {

    text-align: center;
    padding: 0.5rem;
    font-size: clamp(1rem, 2vw, 1.5rem);
    width: clamp(10rem, 15vw, 15rem);
    border-radius: 0.5rem;
    border: 0.2rem solid #5dade2;
    color: #34495e;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease; 

}

.cari_inside:hover {
  
    color: #F5F5F5;
    background-color: #5dade2; 

}

.cari_table {

width: 100%;
min-width: 600px;
border-collapse: collapse;
background-color: white;
font-size: clamp(0.9rem, 1vw, 1.2rem);

}

.cari_table th {

background-color: #ecf0f1;
padding: 0.8rem;
text-align: left;
border-bottom: 2px solid #ccc;

}

.cari_table td {

padding: 0.8rem;
border-bottom: 1px solid #ddd;

}

.cari_table tr:hover {

background-color: #f9fbfd;

}

.balance_positive {

color: #27ae60;
font-weight: 600;
text-align: right;

}

.balance_negative {

color: #e74c3c;
font-weight: 600;
text-align: right;

}

.cari_table tr.active {
    background-color: #2c3e50 !important;
    color: #fff !important;
}


.cari_buttons button:disabled {
    
    opacity: 0.5;
    cursor: not-allowed;
    border-color: #ccc;
    color: #999;
}
/*<---------------------------------------- Menü CSS Kısmı Bitiş ---------------------------------------------------------------------->*/


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
                
            </ul>
</nav>

<h2>Cari İşlem Modülleri</h2> <!-- Sayfa Başlığı -->

<div class="main_menu"> <!-- main_menu -->

<!------------------------------------------ Filitre HTML Kısmı Başlangıç  ------------------------------------------------------------------->
  
<div class="filter_tools"> <!-- filter_tools -->

<p class="filter_title">Filtre Seçenekleri :</p>

<label for="cari_search">Cari Adı :</label>
<input type="text" id="cari_search" class="cari_search" placeholder="Cari Adını Yazınız">


  <label for="cari_card_type">Cari Tipi :</label>
  <select id="cari_card_type" name="cari_card_type" class="cari_card_type" required>
  <option value="">Tümü</option>
  <option value="alici">Alıcı</option>
  <option value="satici">Satıcı</option>
  <option value="alici_ve_satici">Alıcı ve Satıcı</option>
  <option value="personel">Personel</option>
  </select>


</div> <!-- filter_tools -->

<!------------------------------------------ Filitre HTML Kısmı Bitiş  ------------------------------------------------------------------->
  

<!------------------------------------------ Cari Listeleri HTML Kısmı Başlangıç  ------------------------------------------------------------->
  
<div class="cari_lists"> 

<div class="cari_table_container">

<table class="cari_table">

<thead>

<tr>

<th>Cari Adı</th>
<th>Cari Tipi</th>
<th>Bakiye</th>

</tr>

</thead>


<?php 
$types = [
    'alici' => 'Alıcı',
    'satici' => 'Satıcı',
    'alici_ve_satici' => 'Alıcı ve Satıcı',
    'personel' => 'Personel'
];
?>

<tbody>
<?php foreach ($cari_list as &$cari): ?>

    <tr data-id="<?= $cari['cari_id'] ?>">
    <td><?= htmlspecialchars($cari['cari_name']) ?></td>
    <td><?= htmlspecialchars($types[$cari['cari_card_type']] ?? $cari['cari_card_type']) ?></td>
    <td class="<?= $cari['balance'] >= 0 ? 'balance_positive' : 'balance_negative' ?>">
    <?= number_format(abs($cari['balance']), 2, ',', '.') ?> ₺
    <?= $cari['balance'] >= 0 ? 'Alacak' : 'Borç' ?>
</td>

</tr>
<?php endforeach; ?>
</tbody>

</table>    
  
</div>

  </div> 

<!------------------------------------------ Cari Listeleri HTML Kısmı Bitiş  ------------------------------------------------------------->
  

<!------------------------------------------ Buton  HTML Kısmı Başlangıç  ------------------------------------------------------------->


  <div class="cari_buttons"> 
   
    <input type="hidden" id="selected_cari_id">

    <a href="/action_panel_elements/cari_action_control_panel/add_cari_page.php" class="new_cari_button">Yeni Cari Ekle</a>

    <!--<button type="button" class="cari_inside" disabled>Cari Detay</button>-->

    <button type="button" class="cari_update" disabled>Cari Güncelle</button>

    <button type="button" class="cari_delete" disabled>Cari Sil</button>
   
  

  </div> 

<!------------------------------------------ Buton  HTML Kısmı Bitiş  ------------------------------------------------------------->


</div> <!-- main_menu -->

</div> <!-- page -->




</body>
</html>
<script>

/* -------------------- DOM Elemanlarını Seçme (HTML'den veri alma) -------------------- */

const cariSearch = document.getElementById('cari_search');
const cariType = document.getElementById('cari_card_type');
const cariRows = document.querySelectorAll('.cari_table tbody tr');
const selectedInput = document.getElementById('selected_cari_id');



/*----------------------------------------------------------------------------------------*/

/* -------------------- Aktif Satır (Seçili Fatura Takibi) -------------------- */

let activeRow = null;

// Kullanıcının seçtiği satırı tutar
// Başlangıçta null → yani hiçbir satır seçili değil
// Tıklama sonrası seçilen satır buraya atanır

/*----------------------------------------------------------------------------------------*/

/* -------------------- Cari Tipi Eşlemesi (JS tarafı) -------------------- */

const types = {
    'alici': 'Alıcı',
    'satici': 'Satıcı',
    'alici_ve_satici': 'Alıcı ve Satıcı',
    'personel': 'Personel'
};

/*----------------------------------------------------------------------------------------*/


/* -------------------- Filtreleme Fonksiyonu (Arama + Tip) -------------------- */

// Kullanıcı input ve select değerlerine göre tablo satırlarını
// anlık olarak filtreleyen (göster/gizle yapan) fonksiyon


function filterCari() {
    const nameVal = cariSearch.value.toLowerCase();
    const typeVal = cariType.value;

    cariRows.forEach(row => {
        const rowName = row.cells[0].textContent.toLowerCase();
        const rowTypeText = row.cells[1].textContent;
        const rowTypeVal = Object.keys(types).find(key => types[key] === rowTypeText) || '';

        const matchesName = rowName
        .split(/\s+/)
        .some(word => word.startsWith(nameVal));
        const matchesType = typeVal ? rowTypeVal === typeVal : true;

        row.style.display = (matchesName && matchesType) ? '' : 'none';

        if (row.style.display === 'none' && row.classList.contains('active')) {
        row.classList.remove('active');
        activeRow = null;
        selectedInput.value = '';
        toggleButtons(false);
       }
    });
}

/*----------------------------------------------------------------------------------------*/

/* -------------------- Filtre Eventleri (input ve select değişimi) -------------------- */


cariSearch.addEventListener('input', filterCari);
cariType.addEventListener('change', filterCari);

// Satır seçimi ve aktif etme


/*----------------------------------------------------------------------------------------*/

/* -------------------- Butonları Seçme -------------------- */

// HTML’deki buton elemanlarını JS değişkenlerine bağlar (DOM üzerinden erişim sağlar)i,üğüi    

/* const cariDetailBtn = document.querySelector('.cari_inside'); */
const cariUpdateBtn = document.querySelector('.cari_update');
const cariDeleteBtn = document.querySelector('.cari_delete');

const actionButtons = [/*cariDetailBtn*/ , cariUpdateBtn , cariDeleteBtn];


/*----------------------------------------------------------------------------------------*/


/* -------------------- Butonları Aktif / Pasif Yapma -------------------- */

// state=false → butonları kapat, state=true → butonları aç (ters mantık: !state)
function toggleButtons(state) {
    actionButtons.forEach(btn => {
        btn.disabled = !state;
    });
}

// Sayfa açıldığında butonlar pasif
toggleButtons(false);


/*----------------------------------------------------------------------------------------*/


/* -------------------- Seçim Kontrolü (ID var mı?) -------------------- */

// UI dışında manuel tetiklemelere karşı seçim kontrolü yapar
function checkSelected() {
    if (!selectedInput.value) {
        alert("Lütfen bir Cari seçiniz");
        return false;
    }
    return true;
}

/*----------------------------------------------------------------------------------------*/

/* -------------------- Cari Detay Sayfasına Git -------------------- */

/*

// Seçilen id ile ile detay sayfasına yönlendirir

cariDetailBtn.addEventListener('click', () => {
    if (!checkSelected()) return;

    window.location.href =
        `/action_panel_elements/cari_action_control_panel/cari_detail_page.php?id=${selectedInput.value}`;
});

*/


/*----------------------------------------------------------------------------------------*/

/* -------------------- Fatura Düzenleme Sayfasına Git -------------------- */

// Seçilen id ile ile edit sayfasına yönlendirir



cariUpdateBtn.addEventListener('click', () => {
    if (!checkSelected()) return;

    window.location.href =
        `/action_panel_elements/cari_action_control_panel/edit_cari_page.php?id=${selectedInput.value}`;
});



/*----------------------------------------------------------------------------------------*/

/* -------------------- Cari Silme İşlemi (POST ile) -------------------- */

// Silme butonuna tıklanınca çalışır
cariDeleteBtn.addEventListener('click', () => {

    // Seçim yoksa işlemi durdur
    if (!checkSelected()) return;

    // Kullanıcıdan onay al
    if (!confirm("Bu Cari kalıcı olarak silinecek. Emin misiniz?")) return;

    // Dinamik form oluştur (POST göndermek için)
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/action_panel_elements/cari_action_control_panel/delete_cari_page.php';

    // Gönderilecek cari_id'yi hidden input olarak ekle
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'cari_id';
    input.value = selectedInput.value;

    // Forma input ekle
    form.appendChild(input);    

    // Formu DOM'a ekle
    document.body.appendChild(form);

    // Formu gönder (POST request)
    form.submit();
});


/*----------------------------------------------------------------------------------------*/

/* -------------------- Tablo Satırına Tıklama (Seçim Mekanizması) -------------------- */

// Tablo satırlarını tıklanabilir yapar, tekli seçim yönetir ve seçime göre sistemi günceller

// Tablodaki tüm satırları tek tek dolaşır
cariRows.forEach(row => {

    row.addEventListener('click', () => {

        // Aynı satıra tekrar tıklanırsa seçimi kaldır ve sistemi sıfırla
        if (activeRow === row) {
            row.classList.remove('active');
            activeRow = null;
            selectedInput.value = '';
            toggleButtons(false);
            return;
        }

        // Daha önce seçilmiş satır varsa seçimini kaldır (tekli seçim için)
        if (activeRow) activeRow.classList.remove('active');

        // Seçilen satırı görsel ve mantıksal olarak aktif hale getirir
        row.classList.add('active');
        activeRow = row;

        // Seçilen satırın id değerini alır ve sistemde saklar
        const cariId = row.dataset.id;
        selectedInput.value = cariId;

        // Seçim yapıldıktan sonra işlem butonlarını aktif eder
        toggleButtons(true);
    });

});

/*----------------------------------------------------------------------------------------*/


</script>