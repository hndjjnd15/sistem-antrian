function initLayananPage(){

    const modal=
        document.getElementById("modalLayanan");

    const form=
        document.getElementById("formLayanan");

    if(!modal||!form)return;

    const idInput=
        document.getElementById("layananId");

    const kodeInput=
        document.getElementById("layananKode");

    const namaInput=
        document.getElementById("layananNama");

    const statusInput=
        document.getElementById("layananStatus");

    const btnClose=
        document.getElementById("closeModalLayanan");

    const btnBatal=
        document.getElementById("btnBatalLayanan");

    // =========================================
    // EDIT
    // =========================================

    document
        .querySelectorAll(".btn-edit-layanan")
        .forEach(button=>{

            button.addEventListener(
                "click",
                ()=>{

                    idInput.value=
                        button.dataset.id||"";

                    kodeInput.value=
                        button.dataset.kode||"";

                    namaInput.value=
                        button.dataset.nama||"";

                    statusInput.value=
                        button.dataset.status||"aktif";

                    openModal();
                }
            );
        });

    // =========================================
    // STATUS
    // =========================================

    document
        .querySelectorAll(".btn-status-layanan")
        .forEach(button=>{

            button.addEventListener(
                "click",
                async ()=>{

                    const id=
                        button.dataset.id;

                    const status=
                        button.dataset.status;

                    const pesan=
                        status==="aktif"
                        ?"Nonaktifkan layanan ini?"
                        :"Aktifkan kembali layanan ini?";

                    const yakin=
                        await showConfirm(
                            pesan,
                            "Konfirmasi Layanan"
                        );

                    if(!yakin){
                        return;
                    }

                    const formData=
                        new FormData();

                    formData.append(
                        "id",
                        id
                    );

                    try{

                        const response=
                            await fetch(
                                "../api/layanan-status.php",
                                {
                                    method:"POST",
                                    body:formData
                                }
                            );

                        const result=
                            await response.json();

                        if(!result.success){

                            throw new Error(
                                result.message||
                                "Gagal mengubah status."
                            );
                        }

                        showToast(
                            result.message,
                            "success"
                        );

                        loadPage(
                            "layanan"
                        );

                    }catch(error){

                        showToast(
                            error.message||
                            "Terjadi kesalahan.",
                            "error"
                        );
                    }
                }
            );
        });

    // =========================================
    // SIMPAN
    // =========================================

    form.addEventListener(
        "submit",
        async e=>{

            e.preventDefault();

            if(!namaInput.value.trim()){

                showToast(
                    "Nama layanan wajib diisi.",
                    "warning"
                );
                
                namaInput.focus();

                return;
            }

            try{

                const response=
                    await fetch(
                        "../api/layanan-simpan.php",
                        {
                            method:"POST",
                            body:new FormData(form)
                        }
                    );

                const result=
                    await response.json();

                if(!result.success){

                    throw new Error(
                        result.message||
                        "Gagal menyimpan layanan."
                    );
                }

                closeModal();

                showToast(
                    result.message,
                    "success"
                );

                loadPage(
                    "layanan"
                );

            }catch(error){

                showToast(
                    error.message||
                    "Terjadi kesalahan.",
                    "error"
                );
            }
        }
    );

    // =========================================
    // CLOSE MODAL
    // =========================================

    btnClose?.addEventListener(
        "click",
        closeModal
    );

    btnBatal?.addEventListener(
        "click",
        closeModal
    );

    modal.addEventListener(
        "click",
        e=>{

            if(e.target===modal){
                closeModal();
            }
        }
    );

    function openModal(){

        modal.classList.add(
            "show"
        );
    }

    function closeModal(){

        modal.classList.remove(
            "show"
        );
    }
}