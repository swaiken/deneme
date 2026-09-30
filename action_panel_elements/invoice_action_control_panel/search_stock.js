
/*--------------------------- Consol için Dosya Yükelenme Kontorolü ---------------------------*/

console.log("SEARCH STOCK JS LOADED");

/*---------------------------------------------------------------------------------------------*/


/*-------- Bu üç değişken birlikte stok arama sisteminin veri kaynağını, 
işlem yapılan satırı ve API çağrı zamanlamasını kontrol eder ------------------------*/


const STOCK_BASE_URL =
    "/action_panel_elements/invoice_action_control_panel/";

let stockActiveRow = null;
let stockDebounceTimer = null;

/*---------------------------------------------------------------------------------------------*/


/*---- Ürün adı alanına yazıldıkça ilgili satırı bulup kısa bir gecikmeden sonra stok araması yapan işlem bloğu -------*/

document.addEventListener("input", function (e) {

    if (!e.target.classList.contains("product_name")) {
        return;
    }

    stockActiveRow = e.target.closest("tr");

    console.log("INPUT TRIGGERED");

    if (!stockActiveRow) {
        console.log("ACTIVE ROW NULL");
        return;
    }

    const value = e.target.value.trim();

    // reset stock selection
    stockActiveRow.dataset.stockId = "";

    if (value.length < 2) {
        closeStockDropdown();
        return;
    }

    clearTimeout(stockDebounceTimer);

    stockDebounceTimer = setTimeout(() => {
        fetchStock(value);
    }, 250);
});

/*---------------------------------------------------------------------------------------------*/

/*-------- Kullanıcının yazdığı kelimeye göre stok verisini sunucudan alıp sonucu ekranda gösterilecek hale getiren işlem ----------*/

async function fetchStock(query) {

    try {

        const url =
            STOCK_BASE_URL +
            "search_stock.php?q=" +
            encodeURIComponent(query);

        console.log("FETCH URL:", url);

        const res = await fetch(url, {
            credentials: "include"
        });

        const text = await res.text();

        console.log("RAW RESPONSE:", text);

        let data;

        try {

            data = JSON.parse(text);

        } catch (e) {

            console.error("JSON PARSE ERROR", e);
            return;
        }

        console.log("PARSED DATA:", data);

        renderStockDropdown(data);

    } catch (err) {

        console.error("FETCH ERROR:", err);
    }
}

/*---------------------------------------------------------------------------------------------*/


/*---- Gelen stok listesini ekranda küçük bir seçim kutusu olarak oluşturup, 
inputun altına yerleştiren ve tıklanabilir hale getiren işlem -------*/

function renderStockDropdown(items) {

    console.log("RENDER CALLED", items);

    let dropdown = document.getElementById("stock_dropdown");

    if (!dropdown) {

        console.log("CREATING DROPDOWN");

        dropdown = document.createElement("div");

        dropdown.id = "stock_dropdown";

        document.body.appendChild(dropdown);
    }

    dropdown.innerHTML = "";

    if (!Array.isArray(items) || items.length === 0) {

        closeStockDropdown();
        return;
    }

    items.forEach(item => {

        const div = document.createElement("div");

        div.textContent =
            item.stock_name +
            " - " +
            item.sale_price +
            " ₺";

        div.style.padding = "8px 10px";
        div.style.cursor = "pointer";
        div.style.borderBottom = "1px solid #eee";

        div.addEventListener("click", function () {
            selectStock(item);
        });

        dropdown.appendChild(div);
    });

    const input =
        stockActiveRow?.querySelector(".product_name");

    if (!input) {
        return;
    }

    const rect = input.getBoundingClientRect();

    dropdown.style.position = "fixed";
    dropdown.style.left = rect.left + "px";
    dropdown.style.top = (rect.bottom + 4) + "px";
    dropdown.style.width = Math.max(rect.width, 250) + "px";
    dropdown.style.background = "#fff";
    dropdown.style.border = "1px solid #ccc";
    dropdown.style.zIndex = "999999";
    dropdown.style.display = "block";
    dropdown.style.maxHeight = "220px";
    dropdown.style.overflowY = "auto";
}

/*---------------------------------------------------------------------------------------------*/

/*---- Seçilen ürünü ilgili satırdaki alanlara yazan, toplamları güncelleyen ve seçim kutusunu kapatan işlem -------*/


function selectStock(item) {

    if (!stockActiveRow) {
        return;
    }

    const nameInput =
        stockActiveRow.querySelector(".product_name");

    const priceInput =
        stockActiveRow.querySelector(".product_invoice");

    const kdvInput =
        stockActiveRow.querySelector(".invoice_kdv_value");

    if (nameInput) {
        nameInput.value = item.stock_name || "";
    }

    if (priceInput) {
        priceInput.value = Number(item.sale_price || 0);
    }

    if (kdvInput) {
        kdvInput.value = Number(item.kdv_rate || 0);
    }

    stockActiveRow.dataset.stockId =
        item.stock_id || "";

    console.log("STOCK SELECTED:", item);

    closeStockDropdown();

    if (typeof calculateInvoiceTotal === "function") {
        calculateInvoiceTotal();
    }

    if (typeof updateSaveButtons === "function") {
        updateSaveButtons();
    }
}

/*---------------------------------------------------------------------------------------------*/


/*---- Açık olan stok seçim kutusunu ekrandan gizleyen işlem -------------*/

function closeStockDropdown() {

    const dropdown =
        document.getElementById("stock_dropdown");

    if (dropdown) {
        dropdown.style.display = "none";
    }
}

/*---------------------------------------------------------------------------------------------*/



/*----- Sayfada ürün adı alanı dışında bir yere tıklanınca stok seçim kutusunu kapatan kontrol ---------------*/

document.addEventListener("click", function (e) {

    if (!e.target.classList.contains("product_name")) {
        closeStockDropdown();
    }
});

/*---------------------------------------------------------------------------------------------*/