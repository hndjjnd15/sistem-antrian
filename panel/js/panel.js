const layananId=window.PANEL_CONFIG.layananId;
const loket=window.PANEL_CONFIG.loket;
const cabangId=window.PANEL_CONFIG.cabangId;

const API_STATUS="../api/panel-status.php";
const API_NEXT="../api/panggil-next.php";
const API_RECALL="../api/panggil-ulang.php";
const API_FINISH="../api/selesai.php";

let currentCall=null,sedangProses=false;

const namaLayanan=document.getElementById("namaLayanan");
const namaLoket=document.getElementById("namaLoket");
const currentNumber=document.getElementById("currentNumber");
const currentService=document.getElementById("currentService");
const waitingCount=document.getElementById("waitingCount");
const btnNext=document.getElementById("btnNext");
const btnRecall=document.getElementById("btnRecall");
const btnFinish=document.getElementById("btnFinish");
const message=document.getElementById("message");

namaLoket.textContent=`LOKET ${loket}`;

function updateClock(){
    document.getElementById("clock").textContent=new Date().toLocaleTimeString("id-ID",{hour12:false});
}
updateClock();
setInterval(updateClock,1000);

async function loadStatus(){
    try{
        const url=`${API_STATUS}?layanan_id=${layananId}&cabang_id=${cabangId}&t=${Date.now()}`;
        const response=await fetch(url,{cache:"no-store"});
        const result=await response.json();

        if(!result.success){
            showMessage(result.message,"error");
            return;
        }

        const data=result.data;
        namaLayanan.textContent=data.layanan.nama;
        waitingCount.textContent=data.menunggu;

        if(data.aktif){
            currentCall=data.aktif;
            currentNumber.textContent=data.aktif.nomor_antrian;
            currentService.textContent=data.aktif.layanan;
            btnNext.disabled=true;
            btnRecall.disabled=false;
            btnFinish.disabled=false;
        }else{
            currentCall=null;
            currentNumber.textContent="---";
            currentService.textContent="Belum ada panggilan";
            btnNext.disabled=data.menunggu<=0;
            btnRecall.disabled=true;
            btnFinish.disabled=true;
        }
    }catch(e){
        showMessage("Tidak dapat terhubung ke server.","error");
    }
}

async function panggilBerikutnya(){
    if(sedangProses)return;
    sedangProses=true;
    lockButtons(true);

    try{
        const body=new URLSearchParams();
        body.append("layanan_id",layananId);
        body.append("loket",loket);
        body.append("cabang_id",cabangId);

        const response=await fetch(API_NEXT,{
            method:"POST",
            headers:{"Content-Type":"application/x-www-form-urlencoded"},
            body:body.toString()
        });

        const result=await response.json();

        if(!result.success){
            showMessage(result.message,"error");
            return;
        }

        currentCall=result.data;
        currentNumber.textContent=result.data.nomor_antrian;
        currentService.textContent=result.data.layanan;
        showMessage(`${result.data.nomor_antrian} dipanggil ke ${result.data.loket}.`,"success");
    }catch(e){
        showMessage("Gagal memanggil nomor.","error");
    }finally{
        sedangProses=false;
        await loadStatus();
    }
}

async function panggilUlang(){
    if(!currentCall||sedangProses)return;

    sedangProses=true;
    lockButtons(true);

    try{
        const body=new URLSearchParams();
        body.append("panggilan_id",currentCall.id||currentCall.panggilan_id);
        body.append("cabang_id",cabangId);

        const response=await fetch(API_RECALL,{
            method:"POST",
            headers:{"Content-Type":"application/x-www-form-urlencoded"},
            body:body.toString()
        });

        const result=await response.json();

        if(!result.success){
            showMessage(result.message,"error");
            return;
        }

        showMessage(`${result.data.nomor_antrian} dipanggil ulang.`,"success");
    }catch(e){
        showMessage("Gagal memanggil ulang.","error");
    }finally{
        sedangProses=false;
        await loadStatus();
    }
}

async function selesai(){
    if(!currentCall||sedangProses)return;

    sedangProses=true;
    lockButtons(true);

    try{
        const body=new URLSearchParams();
        body.append("panggilan_id",currentCall.id||currentCall.panggilan_id);
        body.append("cabang_id",cabangId);

        const response=await fetch(API_FINISH,{
            method:"POST",
            headers:{"Content-Type":"application/x-www-form-urlencoded"},
            body:body.toString()
        });

        const result=await response.json();

        if(!result.success){
            showMessage(result.message,"error");
            return;
        }

        currentCall=null;
        showMessage(result.message,"success");
    }catch(e){
        showMessage("Gagal menyelesaikan pelayanan.","error");
    }finally{
        sedangProses=false;
        await loadStatus();
    }
}

function lockButtons(status){
    btnNext.disabled=status;
    btnRecall.disabled=status;
    btnFinish.disabled=status;
}

function showMessage(text,type){
    message.textContent=text;
    message.className="message "+type;
    setTimeout(()=>{
        message.textContent="";
        message.className="message";
    },4000);
}

btnNext.addEventListener("click",panggilBerikutnya);
btnRecall.addEventListener("click",panggilUlang);
btnFinish.addEventListener("click",selesai);

loadStatus();
setInterval(()=>{if(!sedangProses)loadStatus();},3000);