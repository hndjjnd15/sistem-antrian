function initHomePage(){

    const cabang=document.getElementById("dashboardCabang");
    const loading=document.getElementById("dashboardLoading");
    const content=document.getElementById("dashboardContent");

    if(!cabang)return;

    let refreshTimer=null;
    let sedangLoad=false;

    cabang.addEventListener("change",()=>{
        loadDashboard();
    });

    loadDashboard();

    // auto refresh tiap 5 detik
    refreshTimer=setInterval(()=>{
        if(document.getElementById("dashboardCabang")){
            loadDashboard(true);
        }else{
            clearInterval(refreshTimer);
        }
    },5000);

    async function loadDashboard(silent=false){

        if(sedangLoad)return;

        sedangLoad=true;

        if(!silent){
            loading.style.display="block";
            content.style.display="none";
        }

        try{

            const url=
                `../api/dashboard.php?cabang_id=${encodeURIComponent(cabang.value)}&t=${Date.now()}`;

            const response=await fetch(url,{
                cache:"no-store"
            });

            if(!response.ok){
                throw new Error(
                    "HTTP "+response.status
                );
            }

            const result=await response.json();

            if(!result.success){
                throw new Error(
                    result.message||
                    "Gagal mengambil dashboard."
                );
            }

            renderDashboard(result.data);

        }catch(error){

            console.error(
                "Dashboard:",
                error
            );

            if(!silent){
                loading.textContent=
                    "Gagal mengambil data dashboard.";
            }

        }finally{

            sedangLoad=false;
        }
    }

    function renderDashboard(data){

        document.getElementById("dashTotal")
            .textContent=
            data.summary?.total||0;

        document.getElementById("dashMenunggu")
            .textContent=
            data.summary?.menunggu||0;

        document.getElementById("dashDipanggil")
            .textContent=
            data.summary?.dipanggil||0;

        document.getElementById("dashSelesai")
            .textContent=
            data.summary?.selesai||0;

        document.getElementById("dashTeller")
            .textContent=
            data.layanan?.teller||0;

        document.getElementById("dashCs")
            .textContent=
            data.layanan?.cs||0;

        document.getElementById("dashKredit")
            .textContent=
            data.layanan?.kredit||0;

        renderCabang(
            data.cabang||[]
        );

        loading.style.display="none";
        content.style.display="block";
    }

    function renderCabang(rows){

        const tbody=
            document.getElementById(
                "dashCabangTable"
            );

        if(!tbody)return;

        if(!rows.length){

            tbody.innerHTML=`
                <tr>
                    <td
                        colspan="4"
                        class="empty">
                        Belum ada aktivitas hari ini.
                    </td>
                </tr>
            `;

            return;
        }

        tbody.innerHTML=
            rows.map(row=>`

                <tr>

                    <td>
                        <strong>
                            ${escapeHtml(row.nama)}
                        </strong>
                    </td>

                    <td>
                        ${parseInt(row.total||0)}
                    </td>

                    <td>
                        ${parseInt(row.menunggu||0)}
                    </td>

                    <td>
                        ${parseInt(row.selesai||0)}
                    </td>

                </tr>

            `).join("");
    }

    function escapeHtml(value){

        const div=
            document.createElement("div");

        div.textContent=
            value??"";

        return div.innerHTML;
    }
}