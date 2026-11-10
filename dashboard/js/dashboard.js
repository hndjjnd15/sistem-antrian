const content=document.getElementById("mainContent");
const title=document.getElementById("pageTitle");
const sidebar=document.getElementById("sidebar");
const overlay=document.getElementById("sidebarOverlay");
const btnSidebar=document.getElementById("btnSidebar");
const menuItems=document.querySelectorAll(".menu-item");

const titles={
    home:"Dashboard",
    "laporan-harian":"Laporan Harian",
    "laporan-periode":"Laporan Periode",
    users:"Manajemen User",
    cabang:"Master Cabang",
    layanan:"Master Layanan",
    status:"Status Antrian"
};

const allowedPages=Object.keys(titles);

// =========================================================
// LOAD PAGE
// =========================================================

async function loadPage(page){

    if(!allowedPages.includes(page)){
        page="home";
    }

    content.innerHTML=
        '<div class="loading">Memuat...</div>';

    try{

        const response=await fetch(
            `pages/${page}.php?t=${Date.now()}`,
            {
                cache:"no-store"
            }
        );

        if(!response.ok){
            throw new Error(
                "HTTP "+response.status
            );
        }

        const html=
            await response.text();

        content.innerHTML=html;

        // kembali ke atas setiap pindah halaman
        content.scrollTop=0;

        title.textContent=
            titles[page]||"Dashboard";

        menuItems.forEach(item=>{
            item.classList.toggle(
                "active",
                item.dataset.page===page
            );
        });

        history.replaceState(
            null,
            "",
            `?page=${encodeURIComponent(page)}`
        );

        initPage(page);

    }catch(error){

        console.error(
            "Dashboard error:",
            error
        );

        content.innerHTML=`
            <div class="error-box">
                Gagal memuat halaman.
            </div>
        `;
    }
}

// =========================================================
// INIT PAGE
// =========================================================

function initPage(page){

    if(
        page==="home" &&
        typeof initHomePage==="function"
    ){
        initHomePage();
    }

    if(
        page==="users" &&
        typeof initUsersPage==="function"
    ){
        initUsersPage();
    }

    if(
        page==="laporan-harian" &&
        typeof initLaporanHarian==="function"
    ){
        initLaporanHarian();
    }

    if(
        page==="laporan-periode" &&
        typeof initLaporanPeriode==="function"
    ){
        initLaporanPeriode();
    }

    if(
        page==="cabang" &&
        typeof initCabangPage==="function"
    ){
        initCabangPage();
    }

    if(
        page==="layanan" &&
        typeof initLayananPage==="function"
    ){
        initLayananPage();
    }

    if(
        page==="status" &&
        typeof initStatusPage==="function"
    ){
        initStatusPage();
    }
}

// =========================================================
// DEVICE
// =========================================================

function isMobile(){
    return window.innerWidth<=900;
}

// =========================================================
// SIDEBAR
// =========================================================

function openSidebar(){

    if(isMobile()){

        sidebar.classList.add("show");
        sidebar.classList.remove("hide");

        overlay?.classList.add("show");

    }else{

        sidebar.classList.remove("hide");
    }
}

function closeSidebar(){

    if(isMobile()){

        sidebar.classList.remove("show");
        sidebar.classList.add("hide");

        overlay?.classList.remove("show");

    }else{

        sidebar.classList.add("hide");
    }
}

// =========================================================
// TOGGLE SIDEBAR
// =========================================================

btnSidebar?.addEventListener("click",()=>{

    if(isMobile()){

        if(sidebar.classList.contains("show")){
            closeSidebar();
        }else{
            openSidebar();
        }

    }else{

        sidebar.classList.toggle("hide");
    }
});

// =========================================================
// OVERLAY
// =========================================================

overlay?.addEventListener("click",()=>{
    closeSidebar();
});

// =========================================================
// MENU SIDEBAR
// =========================================================

menuItems.forEach(item=>{

    item.addEventListener("click",e=>{

        e.preventDefault();

        const page=
            item.dataset.page||"home";

        loadPage(page);

        if(isMobile()){
            closeSidebar();
        }
    });
});

// =========================================================
// RESIZE
// =========================================================

window.addEventListener("resize",()=>{

    if(isMobile()){

        sidebar.classList.remove("show");
        sidebar.classList.add("hide");

        overlay?.classList.remove("show");

    }else{

        sidebar.classList.remove("show");
        sidebar.classList.remove("hide");

        overlay?.classList.remove("show");
    }
});

// =========================================================
// INITIAL STATE
// =========================================================

if(isMobile()){
    sidebar.classList.add("hide");
}else{
    sidebar.classList.remove("hide");
}

// =========================================================
// LOAD HALAMAN AWAL
// =========================================================

const params=
    new URLSearchParams(
        window.location.search
    );

const initialPage=
    params.get("page")||"home";

loadPage(initialPage);