// =========================================================
// GLOBAL TOAST
// =========================================================

let globalToastTimer=null;

function showToast(
    message,
    type="success",
    title=null
){

    const toast=
        document.getElementById(
            "globalToast"
        );

    const icon=
        document.getElementById(
            "globalToastIcon"
        );

    const titleElement=
        document.getElementById(
            "globalToastTitle"
        );

    const messageElement=
        document.getElementById(
            "globalToastMessage"
        );

    if(!toast)return;

    toast.classList.remove(
        "success",
        "error",
        "warning"
    );

    toast.classList.add(type);

    if(type==="success"){

        icon.textContent="✓";

        titleElement.textContent=
            title||"Berhasil";

    }else if(type==="error"){

        icon.textContent="!";

        titleElement.textContent=
            title||"Gagal";

    }else{

        icon.textContent="!";

        titleElement.textContent=
            title||"Perhatian";
    }

    messageElement.textContent=
        message;

    toast.classList.add("show");

    clearTimeout(
        globalToastTimer
    );

    globalToastTimer=setTimeout(()=>{

        toast.classList.remove(
            "show"
        );

    },3000);
}


// =========================================================
// GLOBAL CONFIRM
// =========================================================

function showConfirm(
    message,
    title="Konfirmasi"
){

    return new Promise(resolve=>{

        const modal=
            document.getElementById(
                "globalConfirmModal"
            );

        const titleElement=
            document.getElementById(
                "globalConfirmTitle"
            );

        const messageElement=
            document.getElementById(
                "globalConfirmMessage"
            );

        const btnOk=
            document.getElementById(
                "globalConfirmOk"
            );

        const btnCancel=
            document.getElementById(
                "globalConfirmCancel"
            );

        const btnClose=
            document.getElementById(
                "globalConfirmClose"
            );

        if(!modal){

            resolve(false);
            return;
        }

        titleElement.textContent=
            title;

        messageElement.textContent=
            message;

        modal.classList.add(
            "show"
        );

        function cleanup(result){

            modal.classList.remove(
                "show"
            );

            btnOk.removeEventListener(
                "click",
                onOk
            );

            btnCancel.removeEventListener(
                "click",
                onCancel
            );

            btnClose.removeEventListener(
                "click",
                onCancel
            );

            modal.removeEventListener(
                "click",
                onOutside
            );

            resolve(result);
        }

        function onOk(){
            cleanup(true);
        }

        function onCancel(){
            cleanup(false);
        }

        function onOutside(e){

            if(e.target===modal){
                cleanup(false);
            }
        }

        btnOk.addEventListener(
            "click",
            onOk
        );

        btnCancel.addEventListener(
            "click",
            onCancel
        );

        btnClose.addEventListener(
            "click",
            onCancel
        );

        modal.addEventListener(
            "click",
            onOutside
        );
    });
}