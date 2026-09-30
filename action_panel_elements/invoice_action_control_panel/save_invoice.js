/*-------- Faturanın kaydetme modunu tutan değişken (varsayılan: tek fatura kaydı) -----------------------------*/

let invoiceSaveMode = "single";

/*--------------------------------------------------------------------------------------------------*/

/*--------- Sayfa tamamen yüklendiğinde başlangıç kurulumlarını yapar (event bağlama, hesaplama ve buton durumları) ---------------*/

let paymentTypeEl = null;
let paymentFromEl = null;

function updatePaymentUI() {

    if (!paymentTypeEl || !paymentFromEl) return;

    const isOpen = paymentTypeEl.value === "open";

    paymentFromEl.disabled = isOpen;

    if (isOpen) {
        paymentFromEl.selectedIndex = 0;
    }

    paymentFromEl.style.opacity = paymentFromEl.disabled ? "0.6" : "1";
}

function bindPaymentUI() {  

    paymentTypeEl = document.getElementById("invoice_payment_type");
    paymentFromEl = document.getElementById("payment_from");

    if (!paymentTypeEl || !paymentFromEl) return;

    const handler = () => updatePaymentUI();

    paymentTypeEl.addEventListener("change", handler);
    paymentTypeEl.addEventListener("input", handler);

    setTimeout(() => {
        updatePaymentUI();
    }, 0);
}

/*--------------------------------------------------------------------------------------------------*/

document.addEventListener("DOMContentLoaded", () => {

    bindPaymentUI();      
    bindEvents();         
    calculateInvoiceTotal();
    updateSaveButtons();

});

/*--------------------------------------------------------------------------------------------------*/


/*------- Sayfadaki buton ve input etkileşimlerini ilgili fonksiyonlara bağlar --------------------*/

function bindEvents() {

    document.addEventListener("input", handleInvoiceChange);
    document.addEventListener("change", handleInvoiceChange);

    const addBtn = document.getElementById("add_row_btn");

    if (addBtn) {
        addBtn.addEventListener("click", addNewRow);
    }

    const saveBtn = document.querySelector(".save_invoice");
    const saveNewBtn = document.querySelector(".save_and_new_invoice");

    if (saveBtn) {
        saveBtn.addEventListener("click", () => {
            invoiceSaveMode = "single";
            saveInvoice();
        });
    }

    if (saveNewBtn) {
        saveNewBtn.addEventListener("click", () => {
            invoiceSaveMode = "new";
            saveInvoice();
        });
    }
}

/*--------------------------------------------------------------------------------------------------*/


/*-------------- Kaydetme öncesi form kontrolü ----------------------------*/

function validateInvoiceBeforeSubmit() {

    const rows = document.querySelectorAll("#invoice_body tr");

    if (!rows.length) {
        return { valid: false, message: "Fatura satırı yok" };
    }

    let hasValid = false;

    rows.forEach(row => {

        const q = parseFloat(row.querySelector(".product_quantity")?.value || 0);
        const p = parseFloat(row.querySelector(".product_invoice")?.value || 0);

        if (q > 0 && p > 0) {
            hasValid = true;
        }
    });

    if (!hasValid) {
        return { valid: false, message: "Geçerli ürün satırı yok" };
    }

    const cari = document.getElementById("cari_id")?.value;

    if (!cari) {
        return { valid: false, message: "Cari seçilmedi" };
    }

    return { valid: true, message: "OK" };
}

/*--------------------------------------------------------------------------------------------------*/


/*-------- Fatura içindeki ilgili inputlar değiştiğinde toplamları ve buton durumunu günceller -------------------------*/

function handleInvoiceChange() {

    calculateInvoiceTotal();
    updateSaveButtons();
}

/*--------------------------------------------------------------------------------------------------*/


/*--------- Faturada geçerli ürün satırı varsa kaydetme butonlarını aktif eder --------------------------*/

console.log("updateSaveButtons çalıştı");

function updateSaveButtons() {

    const rows = document.querySelectorAll("#invoice_body tr");

    let hasValidRow = false;
    let allFilledRowsValid = true;

    rows.forEach(row => {

        const name = row.querySelector(".product_name")?.value?.trim();
        const q = parseFloat(row.querySelector(".product_quantity")?.value || 0);
        const p = parseFloat(row.querySelector(".product_invoice")?.value || 0);
        const kdv = row.querySelector(".invoice_kdv_value")?.value;

        // satır “kullanılmış mı?”
        const isTouched =
            name !== "" ||
            q > 0 ||
            p > 0;

        // satır dolu mu (indirim hariç zorunlular)
        const isValid =
            name &&
            q > 0 &&
            p > 0 &&
            kdv !== "" && kdv != null;

        if (isValid) {
            hasValidRow = true;
        }

        // kullanıcı bir şey yazdı ama eksik bıraktıysa blokla
        if (isTouched && !isValid) {
            allFilledRowsValid = false;
        }
    });

    const cari = document.getElementById("cari_id")?.value;
    const date = document.getElementById("invoice_date")?.value;

const formValid =
    hasValidRow &&
    allFilledRowsValid &&
    cari &&
    date;

console.log({
    hasValidRow,
    allFilledRowsValid,
    cari,
    date,
    formValid
});

    document.querySelectorAll(".save_invoice, .save_and_new_invoice")
        .forEach(btn => btn.disabled = !formValid);
}
/*--------------------------------------------------------------------------------------------------*/


/*--------- Kullanıcıdan gelen para formatını hesaplama için sayıya çevirir -------------------------------*/

function parseMoney(value) {
    if (!value) return 0;

    return parseFloat(
        value.toString()
            .replace(/\./g, "")
            .replace(",", ".")
            .replace(/[^\d.-]/g, "")
    ) || 0;
}

function formatMoney(value) {

    return new Intl.NumberFormat("tr-TR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
}

/*--------------------------------------------------------------------------------------------------*/


function calculateRow(row) {

    const quantity = parseFloat(row.querySelector(".product_quantity")?.value || 0);
    const price = parseMoney(row.querySelector(".product_invoice")?.value || 0);
    const kdv = parseFloat(row.querySelector(".invoice_kdv_value")?.value || 0);
    const discount = parseFloat(row.querySelector(".discount_value")?.value || 0);

    const subtotal = quantity * price;
    const kdvAmount = subtotal * (kdv / 100);
    const discountAmount = subtotal * (discount / 100);
    const total = subtotal + kdvAmount - discountAmount;

    const cell = row.querySelector(".total_value");

    if (cell) {
        cell.dataset.value = total;
        cell.textContent = formatMoney(total) + " ₺";
    }

    return { subtotal, kdvAmount, discountAmount, total };
}

/*--------------------------------------------------------------------------------------------------*/

function calculateInvoiceTotal() {

    let subtotal = 0;
    let kdvTotal = 0;
    let discountTotal = 0;
    let grand = 0;

    document.querySelectorAll("#invoice_body tr").forEach(row => {

        const data = calculateRow(row);

        subtotal += data.subtotal;
        kdvTotal += data.kdvAmount;
        discountTotal += data.discountAmount;
        grand += data.total;
    });

    set("summary_subtotal", subtotal);
    set("summary_kdv", kdvTotal);
    set("summary_discount", discountTotal);
    set("summary_total", grand);
}

function set(id, value) {

    const el = document.getElementById(id);
    if (!el) return;

    el.dataset.value = value;
    el.textContent = formatMoney(value) + " ₺";
}

/*--------------------------------------------------------------------------------------------------*/

function addNewRow() {

    const tbody = document.getElementById("invoice_body");
    const template = document.querySelector("#invoice_template .invoice_row_template");

    if (!tbody || !template) return;

    const row = template.cloneNode(true);

    row.classList.remove("invoice_row_template");

    row.querySelectorAll("input").forEach(i => i.value = "");
    row.querySelectorAll("select").forEach(s => s.selectedIndex = 0);

    tbody.appendChild(row);

    calculateInvoiceTotal();
    updateSaveButtons();
}

/*--------------------------------------------------------------------------------------------------*/


async function saveInvoice() {

    const validation = validateInvoiceBeforeSubmit();
    if (!validation.valid) {
        alert(validation.message);
        return;
    }

    const rows = document.querySelectorAll("#invoice_body tr");
    const items = [];

rows.forEach(row => {

    const quantity = parseFloat(row.querySelector(".product_quantity")?.value || 0);
    const price = parseFloat(row.querySelector(".product_invoice")?.value || 0);
    const kdv = parseFloat(row.querySelector(".invoice_kdv_value")?.value || 0);
    const discount = parseFloat(row.querySelector(".discount_value")?.value || 0);
    const name = row.querySelector(".product_name")?.value;

    if (quantity <= 0 || price <= 0) return;

    items.push({
        stock_id: row.dataset.stockId ? parseInt(row.dataset.stockId) : null,
        name,
        quantity,
        unit_price: price,
        kdv_rate: kdv,
        discount_rate: discount
    });
});

    const paymentType = document.getElementById("invoice_payment_type")?.value;
    const paymentFromEl = document.getElementById("payment_from");
    const isOpen = paymentType === "open";

    let paymentFrom = null;

    if (!isOpen) {
        const val = paymentFromEl?.value;
        paymentFrom = (val === "kasa" || val === "banka") ? val : null;
    }

const cariId = document.getElementById("cari_id")?.value;
const cariName = document.getElementById("cari_search")?.value?.trim() || null;

if (!cariId) {
    alert("Cari seçilmedi");
    return;
}

if (!cariName) {
    alert("Cari adı boş olamaz");
    return;
}



const data = {
    cari_id: cariId,
    cari_name: cariName,

    invoice_type: document.getElementById("invoice_card_type")?.value || null,
    payment_type: paymentType,
    payment_from: paymentFrom,
    invoice_date: document.getElementById("invoice_date")?.value || null,

    subtotal: parseFloat(document.getElementById("summary_subtotal")?.dataset.value || 0),
    kdv_total: parseFloat(document.getElementById("summary_kdv")?.dataset.value || 0),
    discount_total: parseFloat(document.getElementById("summary_discount")?.dataset.value || 0),
    grand_total: parseFloat(document.getElementById("summary_total")?.dataset.value || 0),

    items
};
    const btn = document.querySelector(".save_invoice");

    if (btn) {
        btn.disabled = true;
        btn.textContent = "Kaydediliyor...";
    }

    try {
        const res = await fetch(
            "/action_panel_elements/invoice_action_control_panel/save_invoice.php",
            {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            }
        );

        const response = await res.json();




if (!response.success) {
    throw new Error(response.message);
}

alert(response.message || "Fatura başarıyla kaydedildi.");

if (invoiceSaveMode === "single") {
    window.location.href =
        "/action_panel_elements/invoice_action_control_panel/invoice_action_page.php";
    return;
}

resetInvoiceForm();
invoiceSaveMode = "single";

    } catch (err) {
        alert(err.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = "Fatura Kaydet";
        }
    }
}


/*--------------------------------------------------------------------------------------------------*/

function resetInvoiceForm() {

    document.querySelectorAll("#invoice_body tr").forEach((row, i) => {
        if (i === 0) return;
        row.remove();
    });

    document.querySelectorAll("input").forEach(i => i.value = "");

    document.querySelectorAll("select").forEach(s => {
        if (s.id === "payment_from") return;
        s.selectedIndex = 0;
    });

    calculateInvoiceTotal();
    updateSaveButtons();
    updatePaymentUI();
}

window.saveInvoice = saveInvoice;