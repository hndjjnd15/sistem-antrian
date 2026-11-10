const API = "../api/dashboard.php";

async function loadDashboard() {

    try {

        const response = await fetch(
            `${API}?t=${Date.now()}`,
            { cache: "no-store" }
        );

        const result = await response.json();

        if (!result.success) return;

        const data = result.data;

        document.getElementById("total").textContent =
            data.summary.total;

        document.getElementById("menunggu").textContent =
            data.summary.menunggu;

        document.getElementById("dipanggil").textContent =
            data.summary.dipanggil;

        document.getElementById("selesai").textContent =
            data.summary.selesai;

        renderServices(data.layanan);
        renderCalls(data.panggilan);
        renderPrint(data.print);

    } catch (error) {
        console.error("Dashboard error:", error);
    }
}

function renderServices(rows) {

    const container =
        document.getElementById("layananList");

    container.innerHTML = "";

    const names = {
        A: "Teller",
        B: "Customer Service",
        C: "Kredit"
    };

    ["A","B","C"].forEach(kode => {

        const row =
            rows.find(
                item =>
                    item.kode_layanan === kode
            );

        container.innerHTML += `
            <div class="service-item">
                <span>${names[kode]}</span>
                <strong>${Number(row?.total || 0)}</strong>
            </div>
        `;

    });
}

function renderCalls(rows) {

    const body =
        document.getElementById("callTable");

    body.innerHTML = "";

    if (!rows.length) {

        body.innerHTML = `
            <tr>
                <td colspan="5">
                    Belum ada panggilan hari ini.
                </td>
            </tr>
        `;

        return;
    }

    rows.forEach(row => {

        const tr =
            document.createElement("tr");

        tr.innerHTML = `
            <td><strong>${escapeHtml(row.nomor_antrian)}</strong></td>
            <td>${escapeHtml(row.layanan)}</td>
            <td>${escapeHtml(row.loket)}</td>
            <td>${escapeHtml(formatTime(row.waktu_panggil))}</td>
            <td>${escapeHtml(row.status)}</td>
        `;

        body.appendChild(tr);
    });
}

function renderPrint(data) {

    const box =
        document.getElementById("printStatus");

    box.className =
        "status-box";

    if (!data) {

        box.textContent =
            "Belum ada proses cetak";

        return;
    }

    box.textContent =
        data.status.toUpperCase();

    if (data.status === "berhasil") {
        box.classList.add("status-success");
    }

    if (data.status === "gagal") {
        box.classList.add("status-failed");
    }
}

function formatTime(datetime) {

    if (!datetime) return "-";

    const parts =
        datetime.split(" ");

    return parts[1] || datetime;
}

function escapeHtml(value) {

    const div =
        document.createElement("div");

    div.textContent =
        value ?? "";

    return div.innerHTML;
}

document.getElementById("tanggal").textContent =
    new Date().toLocaleDateString(
        "id-ID",
        {
            weekday: "long",
            day: "2-digit",
            month: "long",
            year: "numeric"
        }
    );

loadDashboard();

setInterval(
    loadDashboard,
    5000
);