<?php

session_start(); // Session Başlangıcı 

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

/* --------------------------------------------------------------------------------------------------------------------- */

/* -------------------- Fatura  Verilerini Sorgulama (Prepared Statement) ve Listeleme ---------------------------------------- */

$invoice_list = [];

$stmt = $conn->prepare("
    SELECT invoice_id, cari_name, invoice_type, grand_total
    FROM all_invoices
    WHERE firm_id = ? AND user_id = ?
    ORDER BY cari_name ASC
");
$stmt->bind_param("ii", $firm_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $invoice_list[] = $row;
}


/* --------------------------------------------------------------------------------------------------------------------- */


?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura İşlem Modülleri</title>
<style>

/*<---------------------------------------- Temel Sayfa CSS Kısmı Başlangıç ---------------------------------------->*/

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


/*<---------------------------------------- Temel Sayfa CSS Kısmı Bitiş -------------------------------------------------->*/

/*<----------------------------------------  Tam Sayfa CSS Kısmı Başlangıç ----------------------------------------------------->*/


.page {
   
   height: 100vh;
   display: flex;
   flex-direction: column;

}

/*<----------------------------------------  Tam Sayfa CSS Kısmı Bitiş -------------------------------------------------------------->*/

/*<---------------------------------------- Navigasyon CSS Kısmı Başlangıç -------------------------------------------------->*/


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
    flex-direction: column;
    
}

/*< ------------------------------------------------------->*/


/*< ---------- Filtreler Kısmı CSS Başlangıç -------------->*/


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


.invoice_search {

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

.invoice_card_type {

    width: 20%;
    padding-left: 1rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    font-size: clamp(0.9rem, 1vw, 1.2rem);
    border: 1px solid #ccc;

}

/*< ------------------------------------------------>*/


/*< ---------- Listelenen Verilerin CSS Başlangıcı -------------->*/

.invoice_lists {
    
    flex: 8;
    border: 2px solid #555; 
    border-radius: 0.5rem;
   
}

.invoice_table_container {
    
    max-height: 60vh;   
    overflow-y: auto;   
    overflow-x: auto;
}

.invoice_table {

    width: 100%;
    min-width: 600px;
    border-collapse: collapse;
    background-color: white;
    font-size: clamp(0.9rem, 1vw, 1.2rem);

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
    text-align: left;
    border-bottom: 2px solid #ccc;

}

.invoice_table td {

    padding: 0.8rem;
    border-bottom: 1px solid #ddd;

}


.invoice_table tbody tr:hover {
    
    cursor: pointer;
    background-color: #eef6ff;

}


.invoice_table tr.active {
    
    background-color: #2c3e50 !important;
    color: #fff !important;

}

.invoice_positive {

   color: #27ae60;
   font-weight: 600;
   text-align: right;

}

.invoice_negative {

   color: #e74c3c;
   font-weight: 600;
   text-align: right;

}

/*< ------------------------------------------------>*/


/*< ---------- Butonlar Kısmı CSS Başlangıç -------------->*/

.invoice_buttons {

    display: flex;
    flex: 1;
    justify-content: space-around;
    align-items: center;
    background-color: #F5F5F5;
    border-radius: 0.5rem;
    border: 3px solid #ccc;
    
}

.new_invoice_button {

    text-align: center;
    padding: 0.5rem;
    font-size: clamp(1rem, 2vw, 1.5rem);
    width: clamp(10rem, 15vw, 15rem);
    border-radius: 0.5rem;
    border: 0.2rem solid #3498db;
    color: #34495e;
    transition: background-color 0.3s ease, color 0.3s ease;

}

.new_invoice_button:hover {
  
    color: #F5F5F5;
    background-color: #3498db; 

}

.invoice_inside {

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

.invoice_inside:hover {
  
    color: #F5F5F5;
    background-color: #5dade2; 

}

.invoice_update {

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

.invoice_update:hover {
  
    color: #F5F5F5;
    background-color: #27ae60; 

}

.invoice_delete {

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

.invoice_delete:hover {
  
    color: #F5F5F5;
    background-color: #e74c3c; 

}


.invoice_buttons button:disabled {
    
    opacity: 0.5;
    cursor: not-allowed;
    border-color: #ccc;
    color: #999;
}


/*< ------------------------------------------------>*/




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

<h2>Fatura İşlem Modülleri</h2> <!-- Sayfa Başlığı -->

<div class="main_menu"> <!-- main_menu -->

<!------------------------------------------ Filitre HTML Kısmı Başlangıç  ------------------------------------------------------------------->
  
<div class="filter_tools"> <!-- filter_tools -->

<p class="filter_title">Filtre Seçenekleri :</p>

<label for="invoice_search">Firma (Cari): </label>
<input type="text" id="invoice_search" class="invoice_search" placeholder="Firma (Cari) Adını Yazınız :">


  <label for="invoice_card_type">Fatura Tipi :</label>
  <select id="invoice_card_type" name="invoice_card_type" class="invoice_card_type" required>
  <option value="">Tümü</option>
  <option value="alis"> Alış  Faturaları</option>
  <option value="satis">Satış Faturaları</option>
  <option value="gider">Gider Faturaları</option>
  <option value="gelir">Gelir Faturaları</option>
  
  </select>


</div> <!-- filter_tools -->

<!------------------------------------------ Filitre HTML Kısmı Bitiş  ------------------------------------------------------------------->
  

<!------------------------------------------ İnvoice Listeleri HTML Kısmı Başlangıç  ------------------------------------------------------------->
  
<div class="invoice_lists"> 

<div class="invoice_table_container">

<table class="invoice_table">

<thead>

<tr>

<th>Firma (Cari):</th>
<th>Fatura Tipi</th>
<th>Miktar</th>

</tr>

</thead>

<!----------------------------------------- Fatura Verilerini Döngü ile Tabloya Yazdırma --------------------------------------->

<?php 

$types = [
    'alis'  => 'Alış  Faturaları',
    'satis' => 'Satış Faturaları',
    'gider' => 'Gider Faturaları',
    'gelir' => 'Gelir Faturaları'
    
];

?>

<tbody>
<?php foreach ($invoice_list as $invoice): ?>

    <!-- İnvoice ID veritabanından gelir ve JS tarafından dataset.id ile okunur -->
    <tr data-id="<?= $invoice['invoice_id'] ?>">
    
    <td><?= htmlspecialchars($invoice['cari_name']) ?></td>
    
    <td><?= htmlspecialchars($types[$invoice['invoice_type']] ?? $invoice['invoice_type']) ?></td>
    
    <td class="<?= ($invoice['invoice_type'] === 'satis' || $invoice['invoice_type'] === 'gelir') ? 'invoice_positive' : 'invoice_negative' ?>">
       
       <?= number_format((float)$invoice['grand_total'], 2, ',', '.') . " ₺" ?>
    
    </td>

</tr>
<?php endforeach; ?>

<!----------------------------------------------------------------------------------------------------------------------------------->
</tbody>

</table>    
  
</div>

  </div> 

<!------------------------------------------ İnvoice Listeleri HTML Kısmı Bitiş  ------------------------------------------------------------->
  

<!------------------------------------------ Buton  HTML Kısmı Başlangıç  ------------------------------------------------------------->


  <div class="invoice_buttons"> 
    
    <input type="hidden" id="selected_invoice_id">

    <a href="/action_panel_elements/invoice_action_control_panel/add_invoice_page.php" class="new_invoice_button">Yeni Fatura Ekle</a>

    <button type="button" class="invoice_inside" disabled>Fatura Detay</button>
    <!--<button type="button" class="invoice_update" disabled>Fatura Düzenle</button>-->
    <button type="button" class="invoice_delete" disabled>Fatura Sil</button>

</div>

<!------------------------------------------ Buton  HTML Kısmı Bitiş  ------------------------------------------------------------->


</div> <!-- main_menu -->

</div> <!-- page -->




</body>
</html>
<script>
/* -------------------- DOM Elemanlarını Seçme (HTML'den veri alma) -------------------- */

const invoiceSearch = document.getElementById('invoice_search'); /* Html deki elemanı js değişkenine aktarma */
const invoiceType = document.getElementById('invoice_card_type');
const invoiceRows = document.querySelectorAll('.invoice_table tbody tr'); /* Htmldeki birden fazla elemanınları js değişkenine aktarma */
const selectedInput = document.getElementById('selected_invoice_id');

/*----------------------------------------------------------------------------------------*/

/* -------------------- Aktif Satır (Seçili Fatura Takibi) -------------------- */

let activeRow = null;

// Kullanıcının seçtiği satırı tutar
// Başlangıçta null → yani hiçbir satır seçili değil
// Tıklama sonrası seçilen satır buraya atanır

/*----------------------------------------------------------------------------------------*/


/* -------------------- Fatura Tipi Eşlemesi (JS tarafı) -------------------- */

const types = {
    'alis'  : 'Alış  Faturaları',
    'satis' : 'Satış Faturaları',
    'gider' : 'Gider Faturaları',
    'gelir' : 'Gelir Faturaları'
};

/*----------------------------------------------------------------------------------------*/


/* -------------------- Filtreleme Fonksiyonu (Arama + Tip) -------------------- */

// Kullanıcı input ve select değerlerine göre tablo satırlarını
// anlık olarak filtreleyen (göster/gizle yapan) fonksiyon



// Filtreleme işlemini tek merkezde toplayan fonksiyon
function filterinvoice() {  

    const nameVal = invoiceSearch.value.toLowerCase(); // Arama input değerini alır ve karşılaştırma için küçük harfe çevirir
    const typeVal = invoiceType.value;                 // Select (Dropdown) üzerinden seçilen değeri alır

    
    // PHP'deki foreach mantığıyla tablo satırlarını tek tek dolaşır
    // ve her satır için filtreleme işlemleri yapılır
    invoiceRows.forEach(row => {

        // Hücredeki veriyi tekrar kullanmak için değişkene alır

        const rowName = row.cells[0].textContent.toLowerCase(); // Satırdaki ilk hücreden (isim) veriyi alır ve küçük harfe çevirir

        const rowTypeText = row.cells[1].textContent;           // Satırdaki ikinci hücreden (görünen tip) veriyi alır


        const rowTypeVal = Object.keys(types).find(             // Ekrandaki tip değerini, sistemdeki karşılık gelen anahtara çevirir
            key => types[key].toLowerCase() === rowTypeText.toLowerCase()
        ) || '';

        const matchesName = rowName      // Metni kelimelere bölüp, herhangi bir kelimenin girilen değerle başlayıp başlamadığını kontrol eder
            .split(/\s+/)
            .some(word => word.startsWith(nameVal));

        const matchesType = typeVal ? rowTypeVal === typeVal : true; // Dropdown seçimine göre filtre uygular, boşsa tüm satırları gösterir

        row.style.display = (matchesName && matchesType) ? '' : 'none'; // Filtreye uymayan satırları gizler

        // Filtre sonrası aktif satır gizlenirse seçimi ve buton durumunu sıfırlar
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

// Input veya select değiştiğinde filtre fonksiyonunu çalıştırır
invoiceSearch.addEventListener('input', filterinvoice);
invoiceType.addEventListener('change', filterinvoice);

/*----------------------------------------------------------------------------------------*/

/* -------------------- Butonları Seçme -------------------- */

// HTML’deki buton elemanlarını JS değişkenlerine bağlar (DOM üzerinden erişim sağlar)    

const invoiceDetailBtn = document.querySelector('.invoice_inside');
/*const invoiceUpdateBtn = document.querySelector('.invoice_update');*/
const invoiceDeleteBtn = document.querySelector('.invoice_delete');

const actionButtons = [invoiceDetailBtn /* invoiceUpdateBtn */, invoiceDeleteBtn];


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
        alert("Lütfen bir fatura seçiniz");
        return false;
    }
    return true;
}

/*----------------------------------------------------------------------------------------*/

/* -------------------- Fatura Detay Sayfasına Git -------------------- */

// Seçilen id ile ile detay sayfasına yönlendirir

invoiceDetailBtn.addEventListener('click', () => {
    if (!checkSelected()) return;

    window.location.href =
        `/action_panel_elements/invoice_action_control_panel/invoice_detail_page.php?id=${selectedInput.value}`;
});


/*----------------------------------------------------------------------------------------*/

/* -------------------- Fatura Düzenleme Sayfasına Git -------------------- */

// Seçilen id ile ile edit sayfasına yönlendirir

/*

invoiceUpdateBtn.addEventListener('click', () => {
    if (!checkSelected()) return;

    window.location.href =
        `/action_panel_elements/invoice_action_control_panel/edit_invoice_page.php?id=${selectedInput.value}`;
});

*/

/*----------------------------------------------------------------------------------------*/



/* -------------------- Fatura Silme İşlemi (POST ile) -------------------- */

// Silme butonuna tıklanınca çalışır
invoiceDeleteBtn.addEventListener('click', () => {

    // Seçim yoksa işlemi durdur
    if (!checkSelected()) return;

    // Kullanıcıdan onay al
    if (!confirm("Bu fatura kalıcı olarak silinecek. Emin misiniz?")) return;

    // Dinamik form oluştur (POST göndermek için)
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/action_panel_elements/invoice_action_control_panel/delete_invoice_page.php';

    // Gönderilecek invoice_id'yi hidden input olarak ekle
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'invoice_id';
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
invoiceRows.forEach(row => {

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
        const invoiceId = row.dataset.id;
        selectedInput.value = invoiceId;

        // Seçim yapıldıktan sonra işlem butonlarını aktif eder
        toggleButtons(true);
    });

});
</script>