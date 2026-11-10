function initStatusPage(){

    const cabang=
        document.getElementById("statusCabang");

    if(!cabang)return;

    let timer=null;
    let loading=false;

    cabang.addEventListener("change",()=>{
        loadStatus();
    });

    loadStatus();

    timer=setInterval(()=>{

        if(
            document.getElementById("statusCabang")
        ){
            loadStatus();
        }else{
            clearInterval(timer);
        }

    },5000);

    async function loadStatus(){

        if(loading)return;

        loading=true;

        try{

            const url=
                `../api/status-antrian.php?cabang_id=${encodeURIComponent(cabang.value)}&t=${Date.now()}`;

            const response=
                await fetch(
                    url,
                    {cache:"no-store"}
                );

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
                    "Gagal mengambil status."
                );
            }

            renderStatus(
                result.data
            );

        }catch(error){

            console.error(
                "Status Antrian:",
                error
            );

        }finally{

            loading=false;
        }
    }

    function renderStatus(data){

        document.getElementById(
            "statusMenunggu"
        ).textContent=
            data.summary?.menunggu||0;

        document.getElementById(
            "statusDipanggil"
        ).textContent=
            data.summary?.dipanggil||0;

        document.getElementById(
            "statusSelesai"
        ).textContent=
            data.summary?.selesai||0;

        document.getElementById(
            "statusBatal"
        ).textContent=
            data.summary?.batal||0;

        renderTable(
            data.antrian||[]
        );
    }

    function renderTable(rows){

        const tbody=
            document.getElementById(
                "statusTable"
            );

        if(!tbody)return;

        if(!rows.length){

            tbody.innerHTML=`
                <tr>
                    <td
                        colspan="8"
                        class="empty">
                        Belum ada antrian hari ini.
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
                        ${escapeHtml(row.nama_cabang)}
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

    function formatJam(value){

        if(!value){
            return "-";
        }

        return String(value)
            .substring(0,8);
    }

    function statusClass(status){

        switch(status){

            case "selesai":
                return "aktif";

            case "dipanggil":
                return "warning";

            case "batal":
                return "nonaktif";

            case "menunggu":
                return "role";

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