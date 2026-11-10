const API = "../api/laporan-periode.php";
const awal = document.getElementById("tanggalAwal");
const akhir = document.getElementById("tanggalAkhir");
let reportData = null;

function dateString(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const d = String(date.getDate()).padStart(2, "0");
    return `${y}-${m}-${d}`;
}

function formatDate(value) {
    if (!value) return "-";
    const [y, m, d] = value.split("-");
    return `${d}-${m}-${y}`;
}

function setToday() {
    const today = dateString(new Date());
    awal.value = today;
    akhir.value = today;
}

function setMonth() {
    const now = new Date();
    awal.value = dateString(new Date(now.getFullYear(), now.getMonth(), 1));
    akhir.value = dateString(now);
}

async function loadReport() {
    if (!awal.value || !akhir.value) return;
    if (awal.value > akhir.value) return alert("Tanggal awal tidak boleh melebihi tanggal akhir.");

    const button = document.getElementById("btnLoad");
    button.disabled = true;
    button.textContent = "MEMUAT...";

    try {
        const response = await fetch(`${API}?awal=${encodeURIComponent(awal.value)}&akhir=${encodeURIComponent(akhir.value)}&t=${Date.now()}`, { cache: "no-store" });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const result = await response.json();
        if (!result.success) return alert(result.message || "Laporan gagal dimuat.");
        reportData = result.data;
        renderReport(reportData);
    } catch (error) {
        console.error("Laporan error:", error);
        alert("Tidak dapat mengambil data laporan dari server.");
    } finally {
        button.disabled = false;
        button.textContent = "TAMPILKAN";
    }
}

function renderReport(data) {
    document.getElementById("total").textContent = Number(data.summary?.total || 0);
    document.getElementById("hariAktif").textContent = Number(data.hari_aktif || 0);
    document.getElementById("rataRata").textContent = Number(data.rata_rata || 0);
    document.getElementById("printPeriode").textContent = `Periode ${formatDate(data.periode.awal)} s.d. ${formatDate(data.periode.akhir)}`;
    renderServices(data.layanan || []);
    renderTable(data);
}

function renderServices(rows) {
    const body = document.getElementById("serviceList");
    let total = 0;
    body.innerHTML = rows.map(row => {
        const jumlah = Number(row.total || 0);
        total += jumlah;
        return `<tr><td>${escapeHtml(row.nama_layanan)}</td><td>${jumlah}</td></tr>`;
    }).join("");
    document.getElementById("serviceTotal").textContent = total;
}

function renderTable(data) {
    const services = data.layanan || [];
    const rows = data.harian || [];
    const head = document.getElementById("tableHead");
    const body = document.getElementById("tableBody");
    const foot = document.getElementById("tableFoot");

    head.innerHTML = `<tr><th>Tanggal</th>${services.map(s => `<th>${escapeHtml(s.nama_layanan)}</th>`).join("")}<th>Total</th></tr>`;

    if (!rows.length) {
        body.innerHTML = `<tr><td colspan="${services.length + 2}" class="empty-data">Belum ada data.</td></tr>`;
        foot.innerHTML = "";
        return;
    }

    body.innerHTML = rows.map(row => `<tr><td>${formatDate(row.tanggal)}</td>${services.map(s => `<td>${Number(row.layanan?.[s.kode] || 0)}</td>`).join("")}<td><strong>${Number(row.total || 0)}</strong></td></tr>`).join("");
    foot.innerHTML = `<tr><td>TOTAL</td>${services.map(s => `<td>${Number(s.total || 0)}</td>`).join("")}<td>${Number(data.summary?.total || 0)}</td></tr>`;
}

function exportCSV() {
    if (!reportData) return alert("Tampilkan laporan terlebih dahulu.");
    const services = reportData.layanan || [];
    const rows = [["Tanggal", ...services.map(s => s.nama_layanan), "Total"]];

    (reportData.harian || []).forEach(row => rows.push([
        formatDate(row.tanggal),
        ...services.map(s => Number(row.layanan?.[s.kode] || 0)),
        Number(row.total || 0)
    ]));

    rows.push(["TOTAL", ...services.map(s => Number(s.total || 0)), Number(reportData.summary?.total || 0)]);
    const csv = "\uFEFF" + rows.map(row => row.map(v => `"${String(v).replace(/"/g, '""')}"`).join(";")).join("\r\n");
    const url = URL.createObjectURL(new Blob([csv], { type: "text/csv;charset=utf-8;" }));
    const link = document.createElement("a");
    link.href = url;
    link.download = `laporan-antrian-${reportData.periode.awal}-${reportData.periode.akhir}.csv`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

function printReport() {
    if (!reportData) return alert("Tampilkan laporan terlebih dahulu.");
    window.print();
}

function escapeHtml(value) {
    const div = document.createElement("div");
    div.textContent = value ?? "";
    return div.innerHTML;
}

document.getElementById("btnLoad").addEventListener("click", loadReport);
document.getElementById("btnToday").addEventListener("click", () => { setToday(); loadReport(); });
document.getElementById("btnMonth").addEventListener("click", () => { setMonth(); loadReport(); });
document.getElementById("btnPrint").addEventListener("click", printReport);
document.getElementById("btnCsv").addEventListener("click", exportCSV);
document.getElementById("tanggalCetak").textContent = new Date().toLocaleDateString("id-ID", { day: "2-digit", month: "long", year: "numeric" });

setMonth();
loadReport();
