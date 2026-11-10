(()=>{
    const API_DISPLAY="../api/display.php";
    const displayParams=new URLSearchParams(window.location.search);
    const cabangId=parseInt(displayParams.get("cabang_id")||"0");
    const isApp=displayParams.get("app")==="1";
    
    const videos=[
        "videos/video-1.mp4",
        "videos/video-2.mp4",
        "videos/video-3.mp4",
        "videos/video-4.mp4"
    ];
    
    let currentVideo=0;
    let lastCallKey="";
    let displayReady=false;
    
    document.addEventListener("DOMContentLoaded",()=>{
        startClock();
        startVideo();
        startDisplay();
    });
    
    function startClock(){
        updateClock();
        setInterval(updateClock,1000);
    }
    
    function updateClock(){
        const now=new Date();
        const jam=document.getElementById("jam");
        const tanggal=document.getElementById("tanggal");
    
        if(jam)jam.textContent=now.toLocaleTimeString("id-ID",{hour12:false});
    
        if(tanggal){
            tanggal.textContent=now.toLocaleDateString("id-ID",{
                weekday:"long",
                day:"2-digit",
                month:"long",
                year:"numeric"
            }).toUpperCase();
        }
    }
    
    function startVideo(){
        const video=document.getElementById("videoPlayer");
        if(!video)return;
    
        video.volume=1;
        video.muted=!isApp;
    
        function play(){
            video.src=videos[currentVideo];
            video.load();
    
            const p=video.play();
            if(p)p.catch(e=>console.log("Video autoplay:",e));
        }
    
        video.addEventListener("ended",()=>{
            currentVideo=(currentVideo+1)%videos.length;
            play();
        });
    
        play();
    }
    
    function startDisplay(){
        if(!cabangId){
            console.error("cabang_id tidak ditemukan.");
            return;
        }
    
        loadDisplay();
        setInterval(loadDisplay,1000);
    }
    
    async function loadDisplay(){
        try{
            const response=await fetch(
                `${API_DISPLAY}?cabang_id=${cabangId}&t=${Date.now()}`,
                {cache:"no-store"}
            );
    
            if(!response.ok){
                throw new Error("HTTP "+response.status);
            }
    
            const result=await response.json();
    
            console.log("DISPLAY:",result);
    
            if(!result.success)return;
    
            updateCabang(result.data.cabang);
            updateCurrent(result.data.aktif);
            updateHistory(result.data.riwayat);
    
        }catch(error){
            console.error("Display error:",error);
        }
    }
    
    function updateCabang(data){
        if(!data)return;
    
        const el=document.getElementById("namaCabang");
        if(el)el.textContent=data.nama.toUpperCase();
    }
    
    function updateCurrent(data){
        const nomor=document.getElementById("nomorAntrian");
        const layanan=document.getElementById("layanan");
        const loket=document.getElementById("loket");
    
        if(!nomor||!layanan||!loket){
            console.error("Elemen nomorAntrian/layanan/loket tidak ditemukan.");
            return;
        }
    
        if(!data){
            nomor.textContent="---";
            layanan.textContent="MENUNGGU PANGGILAN";
            loket.textContent="-";
            return;
        }
    
        nomor.textContent=data.nomor_antrian;
        layanan.textContent=data.layanan;
        loket.textContent=data.loket;
    
        const callKey=`${data.id}-${data.waktu_panggil}`;
    
        if(!displayReady){
            lastCallKey=callKey;
            displayReady=true;
            return;
        }
    
        if(lastCallKey!==callKey){
            lastCallKey=callKey;
            animateQueue();
    
            if(typeof window.speakQueue==="function"){
                window.speakQueue(data);
            }
        }
    }
    
    function updateHistory(rows){
        const container=document.getElementById("historyList");
        if(!container)return;
    
        container.innerHTML="";
    
        if(!Array.isArray(rows)||rows.length===0){
            container.innerHTML='<div class="history-item">Menunggu panggilan...</div>';
            return;
        }
    
        rows.forEach(row=>{
            const item=document.createElement("div");
            item.className="history-item";
    
            item.innerHTML=`
                <div class="history-number">${escapeHtml(row.nomor_antrian)}</div>
                <div class="history-counter">${escapeHtml(row.loket)}</div>
            `;
    
            container.appendChild(item);
        });
    }
    
    function animateQueue(){
        const nomor=document.getElementById("nomorAntrian");
        if(!nomor)return;
    
        nomor.classList.remove("queue-animation");
        void nomor.offsetWidth;
        nomor.classList.add("queue-animation");
    }
    
    function escapeHtml(value){
        const div=document.createElement("div");
        div.textContent=value??"";
        return div.innerHTML;
    }
    })();