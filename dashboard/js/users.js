function initUsersPage(){

    const $=id=>document.getElementById(id);

    const modalTambah=$("modalTambah");
    const modalEdit=$("modalEdit");
    const modalReset=$("modalReset");
    const modalConfirm=$("modalConfirm");

    let confirmAction=null;

    function openModal(modal){
        if(modal)modal.classList.add("show");
    }

    function closeModal(modal){
        if(modal)modal.classList.remove("show");
    }

    function toast(message,type="success"){
        const el=$("toast");
        if(!el)return;

        el.textContent=message;
        el.className=`toast show ${type}`;

        setTimeout(()=>{
            el.classList.remove("show");
        },2500);
    }

    async function send(url,data){
        try{
            const response=await fetch(url,{
                method:"POST",
                body:data
            });

            const result=await response.json();

            if(!result.success){
                toast(
                    result.message||"Proses gagal.",
                    "error"
                );
                return false;
            }

            toast(result.message||"Berhasil.");

            setTimeout(()=>{
                loadPage("users");
            },600);

            return true;

        }catch(error){
            console.error(error);

            toast(
                "Gagal terhubung ke server.",
                "error"
            );

            return false;
        }
    }

    $("btnTambahUser")?.addEventListener(
        "click",
        ()=>openModal(modalTambah)
    );

    document.querySelectorAll(".modal-close")
        .forEach(button=>{
            button.addEventListener("click",()=>{
                closeModal(
                    button.closest(".modal")
                );
            });
        });

    $("formTambah")?.addEventListener(
        "submit",
        e=>{
            e.preventDefault();

            send(
                "../users/tambah-action.php",
                new FormData(e.target)
            );
        }
    );

    document.querySelectorAll(".edit-user")
        .forEach(button=>{

            button.addEventListener("click",()=>{

                $("editId").value=
                    button.dataset.id;

                $("editNama").value=
                    button.dataset.nama;

                $("editUsername").value=
                    button.dataset.username;

                $("editRole").value=
                    button.dataset.role;

                $("editCabang").value=
                    button.dataset.cabang;

                openModal(modalEdit);
            });

        });

    $("formEdit")?.addEventListener(
        "submit",
        e=>{
            e.preventDefault();

            send(
                "../users/edit-action.php",
                new FormData(e.target)
            );
        }
    );

    document.querySelectorAll(".reset-user")
        .forEach(button=>{

            button.addEventListener("click",()=>{

                $("resetId").value=
                    button.dataset.id;

                $("resetNama").textContent=
                    `Reset password untuk ${button.dataset.nama}`;

                openModal(modalReset);
            });

        });

    $("formReset")?.addEventListener(
        "submit",
        e=>{
            e.preventDefault();

            send(
                "../users/reset-password-action.php",
                new FormData(e.target)
            );
        }
    );

    document.querySelectorAll(".status-user")
        .forEach(button=>{

            button.addEventListener("click",()=>{

                const statusBaru=
                    button.dataset.status==="aktif"
                        ?"nonaktif"
                        :"aktif";

                $("confirmText").textContent=
                    `Ubah status user menjadi ${statusBaru}?`;

                confirmAction=()=>{

                    const data=new FormData();

                    data.append(
                        "id",
                        button.dataset.id
                    );

                    data.append(
                        "status",
                        statusBaru
                    );

                    send(
                        "../users/status-action.php",
                        data
                    );
                };

                openModal(modalConfirm);
            });

        });

    document.querySelectorAll(".delete-user")
        .forEach(button=>{

            button.addEventListener("click",()=>{

                $("confirmText").textContent=
                    `Hapus user ${button.dataset.nama}?`;

                confirmAction=()=>{

                    const data=new FormData();

                    data.append(
                        "id",
                        button.dataset.id
                    );

                    send(
                        "../users/hapus-action.php",
                        data
                    );
                };

                openModal(modalConfirm);
            });

        });

    $("btnBatalConfirm")?.addEventListener(
        "click",
        ()=>closeModal(modalConfirm)
    );

    $("btnConfirm")?.addEventListener(
        "click",
        ()=>{

            closeModal(modalConfirm);

            if(confirmAction){
                confirmAction();
                confirmAction=null;
            }
        }
    );
}