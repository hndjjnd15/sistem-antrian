function initLaporanHarian(){

    const cabang=document.getElementById("laporanCabang");
    const tanggal=document.getElementById("laporanTanggal");
    const button=document.getElementById("btnLaporanHarian");
    const hasil=document.getElementById("laporanHasil");
    const loading=document.getElementById("laporanLoading");
    const actions=document.getElementById("harianActions");
    const btnPdf=document.getElementById("btnPdfHarian");
    const btnCsv=document.getElementById("btnCsvHarian");

    if(!cabang||!tanggal||!button)return;

    // =====================================================
    // TAMPILKAN LAPORAN
    // =====================================================

    button.addEventListener("click",loadLaporan);

    // =====================================================
    // CETAK PDF
    // Listener hanya dibuat satu kali
    // =====================================================

    btnPdf?.addEventListener("click",()=>{

        const cabangId=cabang.value;
        const tanggalValue=tanggal.value;

        if(!cabangId||!tanggalValue){
            alert("Pilih cabang dan tanggal terlebih dahulu.");
            return;
        }

        const url=
            `../laporan/cetak-harian.php?cabang_id=${encodeURIComponent(cabangId)}&tanggal=${encodeURIComponent(tanggalValue)}`;

        console.log("PDF:",url);

        window.open(url,"_blank");
    });

    // =====================================================
    // EXPORT CSV
    // Listener hanya dibuat satu kali
    // =====================================================

    btnCsv?.addEventListener("click",()=>{

        const cabangId=cabang.value;
        const tanggalValue=tanggal.value;

        if(!cabangId||!tanggalValue){
            alert("Pilih cabang dan tanggal terlebih dahulu.");
            return;
        }

        const url=
            `../export/csv-harian.php?cabang_id=${encodeURIComponent(cabangId)}&tanggal=${encodeURIComponent(tanggalValue)}`;

        console.log("CSV:",url);

        window.location.href=url;
    });

    // =====================================================
    // LOAD LAPORAN
    // =====================================================

    async function loadLaporan(){

        if(!cabang.value){
            alert("Pilih cabang terlebih dahulu.");
            return;
        }

        if(!tanggal.value){
            alert("Pilih tanggal terlebih dahulu.");
            return;
        }

        button.disabled=true;
        loading.style.display="block";
        hasil.style.display="none";

        if(actions){
            actions.style.display="none";
        }

        try{

            const url=
                `../api/laporan-harian.php?cabang_id=${encodeURIComponent(cabang.value)}&tanggal=${encodeURIComponent(tanggal.value)}&t=${Date.now()}`;

            const response=await fetch(url,{
                cache:"no-store"
            });

            if(!response.ok){
                throw new Error(
                    "HTTP "+response.status
                );
            }

            const result=
                await response.json();

            if(!result.success){
                throw new Error(
                    result.message||
                    "Gagal mengambil laporan."
                );
            }

            renderLaporan(
                result.data
            );

        }catch(error){

            console.error(
                "Laporan Harian:",
                error
            );

            alert(
                error.message||
                "Gagal mengambil laporan."
            );

        }finally{

            button.disabled=false;
            loading.style.display="none";
        }
    }

    // =====================================================
    // RENDER LAPORAN
    // =====================================================

    function renderLaporan(data){

        document.getElementById(
            "reportCabang"
        ).textContent=
            data.cabang?.nama||"-";

        document.getElementById(
            "reportTanggal"
        ).textContent=
            formatTanggal(
                data.tanggal
            );

        document.getElementById(
            "totalPengunjung"
        ).textContent=
            data.total||0;

        let teller=0;
        let cs=0;
        let kredit=0;

        (data.layanan||[])
            .forEach(item=>{

                const kode=
                    String(
                        item.kode||""
                    ).toUpperCase();

                const total=
                    parseInt(
                        item.total||0
                    );

                if(kode==="A"){
                    teller=total;
                }

                if(kode==="B"){
                    cs=total;
                }

                if(kode==="C"){
                    kredit=total;
                }
            });

        document.getElementById(
            "totalTeller"
        ).textContent=teller;

        document.getElementById(
            "totalCs"
        ).textContent=cs;

        document.getElementById(
            "totalKredit"
        ).textContent=kredit;

        renderDetail(
            data.detail||[]
        );

        hasil.style.display="block";

        if(actions){
            actions.style.display="flex";
        }
    }

    // =====================================================
    // DETAIL
    // =====================================================

    function renderDetail(rows){

        const tbody=
            document.getElementById(
                "detailLaporan"
            );

        if(!tbody)return;

        if(!rows.length){

            tbody.innerHTML=`
                <tr>
                    <td
                        colspan="7"
                        class="empty">
                        Tidak ada data antrian.
                    </td>
                </tr>
            `;

            return;
        }

        tbody.innerHTML=
            rows.map(
                (row,index)=>`
                <tr>

                    <td>
                        ${index+1}
                    </td>

                    <td>
                        <strong>
                            ${escapeHtml(row.nomor_antrian)}
                        </strong>
                    </td>

                    <td>
                        ${namaLayanan(row.kode_layanan)}
                    </td>

                    <td>
                        ${formatJam(row.jam_ambil)}
                    </td>

                    <td>
                        ${formatJam(row.jam_panggil)}
                    </td>

                    <td>
                        ${formatJam(row.jam_selesai)}
                    </td>

                    <td>
                        <span
                            class="badge ${statusClass(row.status)}">
                            ${escapeHtml(row.status).toUpperCase()}
                        </span>
                    </td>

                </tr>
            `
            ).join("");
    }

    // =====================================================
    // NAMA LAYANAN
    // =====================================================

    function namaLayanan(kode){

        switch(
            String(kode||"").toUpperCase()
        ){

            case "A":
                return "Teller";

            case "B":
                return "Customer Service";

            case "C":
                return "Kredit";

            default:
                return kode||"-";
        }
    }

    // =====================================================
    // FORMAT JAM
    // =====================================================

    function formatJam(value){

        if(!value){
            return "-";
        }

        const text=
            String(value);

        if(text.length>=8){
            return text.substring(
                0,
                8
            );
        }

        return text;
    }

    // =====================================================
    // FORMAT TANGGAL
    // =====================================================

    function formatTanggal(value){

        if(!value){
            return "-";
        }

        const parts=
            String(value).split("-");

        if(parts.length!==3){
            return value;
        }

        return `${parts[2]}-${parts[1]}-${parts[0]}`;
    }

    // =====================================================
    // STATUS
    // =====================================================

    function statusClass(status){

        switch(status){

            case "selesai":
                return "aktif";

            case "dipanggil":
                return "warning";

            case "batal":
                return "nonaktif";

            default:
                return "role";
        }
    }

    // =====================================================
    // ESCAPE HTML
    // =====================================================

    function escapeHtml(value){

        const div=
            document.createElement(
                "div"
            );

        div.textContent=
            value??"";

        return div.innerHTML;
    }
}