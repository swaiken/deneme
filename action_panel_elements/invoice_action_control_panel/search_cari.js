/*--------------------------- Consol için Dosya Yükelenme Kontorolü ---------------------------*/

console.log("SEARCH_CARI.JS LOADED");

/*---------------------------------------------------------------------------------------------*/

/*--------------------------- HTML Kısımların DOM'dan Seçilmesi ---------------------------*/


const cariInput = document.getElementById("cari_search");
const cariDropdown = document.getElementById("cari_dropdown");
const cariIdInput = document.getElementById("cari_id");


/*---------------------------------------------------------------------------------------------*/


/*--------------------------- Kullanıcı yazmayı durdurunca arama yapmayı sağlar ---------------------------*/


let debounceTimer = null;

/*---------------------------------------------------------------------------------------------*/


/*--------------------------- Cari input değiştikçe arama yapan (debounce’lu) event bloğu ------------------------------*/

cariInput.addEventListener("input", function () {

    const value = this.value.trim();

    cariIdInput.value = "";

    if (value.length < 2) {
        hideDropdown(); 
        return;
    }

    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        fetchCari(value);
    }, 300);
});


/*---------------------------------------------------------------------------------------------*/


/*--------------- Backend'e cari arama isteği gönderip sonuçları alarak dropdown’a aktaran async fonksiyon ----------------------*/

async function fetchCari(query) {

    try {

        const url =
            "/action_panel_elements/invoice_action_control_panel/search_cari.php?q="
            + encodeURIComponent(query);

const res = await fetch(url);
const data = await res.json();

console.log("=== CARİ RAW RESPONSE ===");
console.log(data);
console.log("FIRST ITEM:", data?.[0]);

renderDropdown(data);

    } catch (err) {

        console.error("Cari search error:", err);
    }
}

/*---------------------------------------------------------------------------------------------*/


/*---------- Gelen cari listesini dropdown içinde ekrana basan ve seçim event’lerini bağlayan fonksiyon ------------------------------*/

function renderDropdown(items) {

    cariDropdown.innerHTML = "";

    if (!Array.isArray(items) || items.length === 0) {

        hideDropdown();
        return;
    }

    items.forEach(item => {

        const div = document.createElement("div");

        div.textContent = item.cari_name;

        div.addEventListener("click", function () {

            selectCari(item);
        });

        cariDropdown.appendChild(div);
    });

    cariDropdown.style.display = "block";
}

/*---------------------------------------------------------------------------------------------*/


/*---------- Dropdown’dan seçilen cariyi inputlara yazan ve dropdown kontrolünü yöneten fonksiyonlar -----------------------------------------------------------------------------------*/

function selectCari(item) {

    cariInput.value = item.cari_name;
    cariIdInput.value = item.cari_id;

    if (typeof updateSaveButtons === "function") {
        updateSaveButtons();
    }

    hideDropdown();
}

function hideDropdown() {

    cariDropdown.style.display = "none";
}

document.addEventListener("click", function (e) {

    if (
        !cariInput.contains(e.target) &&
        !cariDropdown.contains(e.target)
    ) {
        hideDropdown();
    }
});

/*---------------------------------------------------------------------------------------------*/