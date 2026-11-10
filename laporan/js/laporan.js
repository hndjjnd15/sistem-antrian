/* ============================================================
   CONFIG
============================================================ */

const API_REPORT =
    "../api/laporan-harian.php";


/* ============================================================
   DOM
============================================================ */

const filterTanggal =
    document.getElementById(
        "filterTanggal"
    );


const btnRefresh =
    document.getElementById(
        "btnRefresh"
    );


const totalAntrian =
    document.getElementById(
        "totalAntrian"
    );


const totalSelesai =
    document.getElementById(
        "totalSelesai"
    );


const totalMenunggu =
    document.getElementById(
        "totalMenunggu"
    );


const totalDipanggil =
    document.getElementById(
        "totalDipanggil"
    );


const serviceList =
    document.getElementById(
        "serviceList"
    );


const hourChart =
    document.getElementById(
        "hourChart"
    );


const jamTersibuk =
    document.getElementById(
        "jamTersibuk"
    );


const jumlahTersibuk =
    document.getElementById(
        "jumlahTersibuk"
    );


const callTable =
    document.getElementById(
        "callTable"
    );


/* ============================================================
   TODAY
============================================================ */

function todayString() {

    const now =
        new Date();


    const year =
        now.getFullYear();


    const month =
        String(
            now.getMonth() + 1
        ).padStart(
            2,
            "0"
        );


    const day =
        String(
            now.getDate()
        ).padStart(
            2,
            "0"
        );


    return (
        year +
        "-" +
        month +
        "-" +
        day
    );

}


filterTanggal.value =
    todayString();


/* ============================================================
   LOAD REPORT
============================================================ */

async function loadReport() {

    const tanggal =
        filterTanggal.value;


    if (
        !tanggal
    ) {

        return;

    }


    btnRefresh.disabled =
        true;


    btnRefresh.textContent =
        "MEMUAT...";


    try {

        const response =
            await fetch(

                `${API_REPORT}?tanggal=${encodeURIComponent(tanggal)}&t=${Date.now()}`,

                {
                    cache:
                        "no-store"
                }

            );


        const result =
            await response.json();


        if (
            !result.success
        ) {

            alert(
                result.message ||
                "Gagal mengambil laporan."
            );

            return;

        }


        renderReport(
            result.data
        );


    } catch (
        error
    ) {

        console.error(
            error
        );


        alert(
            "Tidak dapat terhubung ke server."
        );


    } finally {

        btnRefresh.disabled =
            false;


        btnRefresh.textContent =
            "TAMPILKAN";

    }

}


/* ============================================================
   RENDER
============================================================ */

function renderReport(
    data
) {

    /*
     * Summary
     */

    totalAntrian.textContent =
        data.summary.total;


    totalSelesai.textContent =
        data.summary.selesai;


    totalMenunggu.textContent =
        data.summary.menunggu;


    totalDipanggil.textContent =
        data.summary.dipanggil;


    /*
     * Layanan
     */

    renderServices(
        data.layanan
    );


    /*
     * Per jam
     */

    renderHours(
        data.per_jam
    );


    /*
     * Jam tersibuk
     */

    jamTersibuk.textContent =
        data.jam_tersibuk.jam ||
        "-";


    jumlahTersibuk.textContent =
        data.jam_tersibuk.total ||
        0;


    /*
     * Aktivitas
     */

    renderCalls(
        data.panggilan_terakhir
    );

}


/* ============================================================
   SERVICES
============================================================ */

function renderServices(
    rows
) {

    serviceList.innerHTML =
        "";


    if (
        !rows ||
        rows.length === 0
    ) {

        serviceList.innerHTML =
            "<p>Belum ada data.</p>";

        return;

    }


    rows.forEach(
        row => {

            const item =
                document.createElement(
                    "div"
                );


            item.className =
                "service-item";


            item.innerHTML = `

                <div class="service-name">

                    ${escapeHtml(
                        row.nama_layanan
                    )}

                </div>


                <div class="service-stat">

                    <span>
                        TOTAL
                    </span>

                    <strong>
                        ${Number(
                            row.total
                        )}
                    </strong>

                </div>


                <div class="service-stat">

                    <span>
                        MENUNGGU
                    </span>

                    <strong>
                        ${Number(
                            row.menunggu
                        )}
                    </strong>

                </div>


                <div class="service-stat">

                    <span>
                        DILAYANI
                    </span>

                    <strong>
                        ${Number(
                            row.dipanggil
                        )}
                    </strong>

                </div>


                <div class="service-stat">

                    <span>
                        SELESAI
                    </span>

                    <strong>
                        ${Number(
                            row.selesai
                        )}
                    </strong>

                </div>

            `;


            serviceList.appendChild(
                item
            );

        }
    );

}


/* ============================================================
   HOURS
============================================================ */

function renderHours(
    rows
) {

    hourChart.innerHTML =
        "";


    if (
        !rows ||
        rows.length === 0
    ) {

        return;

    }


    const maxTotal =
        Math.max(
            ...rows.map(
                row =>
                    Number(
                        row.total
                    )
            ),
            1
        );


    rows.forEach(
        row => {

            const total =
                Number(
                    row.total
                );


            const percentage =
                total === 0
                ? 0
                : (
                    total /
                    maxTotal
                ) * 100;


            const div =
                document.createElement(
                    "div"
                );


            div.className =
                "hour-row";


            div.innerHTML = `

                <div class="hour-label">

                    ${escapeHtml(
                        row.jam
                    )}

                </div>


                <div class="hour-track">

                    <div
                        class="hour-bar"
                        style="width: ${percentage}%"
                    >
                    </div>

                </div>


                <div class="hour-total">

                    ${total}

                </div>

            `;


            hourChart.appendChild(
                div
            );

        }
    );

}


/* ============================================================
   CALLS
============================================================ */

function renderCalls(
    rows
) {

    callTable.innerHTML =
        "";


    if (
        !rows ||
        rows.length === 0
    ) {

        callTable.innerHTML = `

            <tr>

                <td
                    colspan="5"
                    style="text-align:center"
                >

                    Belum ada aktivitas panggilan.

                </td>

            </tr>

        `;


        return;

    }


    rows.forEach(
        row => {

            const tr =
                document.createElement(
                    "tr"
                );


            const time =
                formatTime(
                    row.waktu_panggil
                );


            const statusClass =

                row.status ===
                "selesai"

                    ? "status-selesai"

                    : "status-dipanggil";


            tr.innerHTML = `

                <td>

                    <strong>

                        ${escapeHtml(
                            row.nomor_antrian
                        )}

                    </strong>

                </td>


                <td>

                    ${escapeHtml(
                        row.layanan
                    )}

                </td>


                <td>

                    ${escapeHtml(
                        row.loket
                    )}

                </td>


                <td>

                    ${escapeHtml(
                        time
                    )}

                </td>


                <td>

                    <span
                        class="status ${statusClass}"
                    >

                        ${escapeHtml(
                            row.status
                        )}

                    </span>

                </td>

            `;


            callTable.appendChild(
                tr
            );

        }
    );

}


/* ============================================================
   FORMAT TIME
============================================================ */

function formatTime(
    datetime
) {

    if (
        !datetime
    ) {

        return "-";

    }


    /*
     * Format MySQL:
     * 2026-09-07 10:15:30
     */

    const parts =
        datetime.split(
            " "
        );


    if (
        parts.length < 2
    ) {

        return datetime;

    }


    return parts[1];

}


/* ============================================================
   SECURITY
============================================================ */

function escapeHtml(
    value
) {

    const div =
        document.createElement(
            "div"
        );


    div.textContent =
        value ?? "";


    return div.innerHTML;

}


/* ============================================================
   EVENT
============================================================ */

btnRefresh.addEventListener(
    "click",
    loadReport
);


filterTanggal.addEventListener(
    "change",
    loadReport
);


/* ============================================================
   INITIAL
============================================================ */

loadReport();


/*
 * Kalau yang sedang dilihat adalah hari ini,
 * refresh otomatis setiap 30 detik.
 */

setInterval(
    () => {

        if (
            filterTanggal.value ===
            todayString()
        ) {

            loadReport();

        }

    },
    30000
);