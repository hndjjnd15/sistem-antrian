function initLaporanPeriode(){

    const cabang=document.getElementById("periodeCabang");
    const awal=document.getElementById("tanggalAwal");
    const akhir=document.getElementById("tanggalAkhir");
    const button=document.getElementById("btnLaporanPeriode");
    const loading=document.getElementById("periodeLoading");
    const hasil=document.getElementById("periodeHasil");

    if(!cabang||!awal||!akhir||!button)return;

    button.addEventListener("click",loadLaporan);

    async function loadLaporan(){

        if(!cabang.value){
            alert("Pilih cabang terlebih dahulu.");
            return;
        }

        if(!awal.value||!akhir.value){
            alert("Lengkapi periode tanggal.");
            return;
        }

        if(awal.value>akhir.value){
            alert("Tanggal awal tidak boleh lebih besar dari tanggal akhir.");
            return;
        }

        button.disabled=true;
        loading.style.display="block";
        hasil.style.display="none";

        try{

            const url=
                `../api/laporan-periode.php?cabang_id=${encodeURIComponent(cabang.value)}&tanggal_awal=${encodeURIComponent(awal.value)}&tanggal_akhir=${encodeURIComponent(akhir.value)}&t=${Date.now()}`;

            const response=await fetch(url,{
                cache:"no-store"
            });

            const result=await response.json();

            if(!result.success){
                throw new Error(
                    result.message||"Gagal mengambil laporan."
                );
            }

            renderLaporan(result.data);

        }catch(error){

            console.error(error);

            alert(
                error.message||
                "Gagal mengambil laporan."
            );

        }finally{

            button.disabled=false;
            loading.style.display="none";
        }
    }

    function renderLaporan(data){

        document.getElementById("periodeNamaCabang").textContent=
            data.cabang?.nama||"-";

        const tanggalAwal=
            data.periode?.tanggal_awal||"";

        const tanggalAkhir=
            data.periode?.tanggal_akhir||"";

        document.getElementById("periodeTanggal").textContent=
            `${formatTanggal(tanggalAwal)} s/d ${formatTanggal(tanggalAkhir)}`;

        document.getElementById("periodeTotal").textContent=
            data.ringkasan?.total||0;

        document.getElementById("periodeHari").textContent=
            data.ringkasan?.hari_operasional||0;

        document.getElementById("periodeRata").textContent=
            data.ringkasan?.rata_rata||0;

        document.getElementById("periodeActions")
            .style.display="flex";
        document.getElementById("btnPdfPeriode")
            ?.addEventListener("click",()=>{

                if(
                    !cabang.value ||
                    !awal.value ||
                    !akhir.value
                )return;

                const url=
                    `../laporan/cetak-periode.php?cabang_id=${encodeURIComponent(cabang.value)}&tanggal_awal=${encodeURIComponent(awal.value)}&tanggal_akhir=${encodeURIComponent(akhir.value)}`;

                window.open(
                    url,
                    "_blank"
                );
            });

        document.getElementById("btnCsvPeriode")
            ?.addEventListener("click",()=>{

                if(
                    !cabang.value ||
                    !awal.value ||
                    !akhir.value
                )return;

                window.location.href=
                    `../export/csv-periode.php?cabang_id=${encodeURIComponent(cabang.value)}&tanggal_awal=${encodeURIComponent(awal.value)}&tanggal_akhir=${encodeURIComponent(akhir.value)}`;
            });
        let teller=0;
        let cs=0;
        let kredit=0;

        (data.layanan||[]).forEach(item=>{

            const kode=
                String(item.kode||"").toUpperCase();

            const total=
                parseInt(item.total||0);

            if(kode==="A")teller=total;
            if(kode==="B")cs=total;
            if(kode==="C")kredit=total;
        });

        document.getElementById("periodeTeller").textContent=teller;
        document.getElementById("periodeCs").textContent=cs;
        document.getElementById("periodeKredit").textContent=kredit;

        renderRekap(data.rekap||[]);
        renderDetail(data.detail||[]);

        hasil.style.display="block";
    }

    function renderRekap(rows){

        const tbody=
            document.getElementById("rekapPeriode");

        if(!rows.length){

            tbody.innerHTML=`
                <tr>
                    <td colspan="6" class="empty">
                        Tidak ada data pada periode tersebut.
                    </td>
                </tr>
            `;

            return;
        }

        tbody.innerHTML=
            rows.map((row,index)=>`

                <tr>
                    <td>${index+1}</td>

                    <td>
                        ${formatTanggal(row.tanggal)}
                    </td>

                    <td>
                        ${parseInt(row.teller||0)}
                    </td>

                    <td>
                        ${parseInt(row.cs||0)}
                    </td>

                    <td>
                        ${parseInt(row.kredit||0)}
                    </td>

                    <td>
                        <strong>
                            ${parseInt(row.total||0)}
                        </strong>
                    </td>
                </tr>

            `).join("");
    }

    function renderDetail(rows){

        const tbody=
            document.getElementById("detailPeriode");

        if(!rows.length){

            tbody.innerHTML=`
                <tr>
                    <td colspan="8" class="empty">
                        Tidak ada data antrian.
                    </td>
                </tr>
            `;

            return;
        }

        tbody.innerHTML=
            rows.map((row,index)=>`

                <tr>

                    <td>
                        ${index+1}
                    </td>

                    <td>
                        ${formatTanggal(row.tanggal)}
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
                        <span class="badge ${statusClass(row.status)}">
                            ${escapeHtml(row.status).toUpperCase()}
                        </span>
                    </td>

                </tr>

            `).join("");
    }

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

    function formatTanggal(value){

        if(!value)return "-";

        const parts=
            String(value).split("-");

        if(parts.length!==3){
            return value;
        }

        return `${parts[2]}-${parts[1]}-${parts[0]}`;
    }

    function formatJam(value){

        if(!value)return "-";

        const text=String(value);

        if(text.length>=8){
            return text.substring(0,8);
        }

        return text;
    }

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

    function escapeHtml(value){

        const div=
            document.createElement("div");

        div.textContent=
            value??"";

        return div.innerHTML;
    }
}