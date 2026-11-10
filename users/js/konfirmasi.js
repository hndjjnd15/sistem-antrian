const confirmModal = document.getElementById("confirmModal");
const confirmTitle = document.getElementById("confirmTitle");
const confirmMessage = document.getElementById("confirmMessage");
const confirmOk = document.getElementById("confirmOk");
const confirmCancel = document.getElementById("confirmCancel");

let confirmAction = null;

function showConfirm(title,message,action){
    confirmTitle.textContent = title;
    confirmMessage.textContent = message;
    confirmAction = action;
    confirmModal.classList.add("show");
}

function closeConfirm(){
    confirmModal.classList.remove("show");
    confirmAction = null;
}

confirmCancel.onclick = closeConfirm;

confirmOk.onclick = async () => {
    if (!confirmAction) return;

    confirmOk.disabled = true;
    confirmOk.textContent = "MEMPROSES...";

    try{
        await confirmAction();
    }finally{
        confirmOk.disabled = false;
        confirmOk.textContent = "YA, LANJUTKAN";
        closeConfirm();
    }
};

confirmModal.addEventListener("click",e=>{
    if(e.target === confirmModal) closeConfirm();
});