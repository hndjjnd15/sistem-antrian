function initCabangPage(){

    const modal=
        document.getElementById("modalCabang");

    const form=
        document.getElementById("formCabang");

    const btnTambah=
        document.getElementById("btnTambahCabang");

    const btnClose=
        document.getElementById("closeModalCabang");

    const btnBatal=
        document.getElementById("btnBatalCabang");

    if(!modal||!form)return;

    const idInput=
        document.getElementById("cabangId");

    const kodeInput=
        document.getElementById("cabangKode");

    const namaInput=
        document.getElementById("cabangNama");

    const statusInput=
        document.getElementById("cabangStatus");

    const modalTitle=
        document.getElementById("modalCabangTitle");


    // =========================================
    // TAMBAH CABANG
    // =========================================

    btnTambah?.addEventListener(
        "click",
        ()=>{

            form.reset();

            idInput.value="";
            kodeInput.value="";
            namaInput.value="";
            statusInput.value="aktif";

            modalTitle.textContent=
                "Tambah Cabang";

            openModal();
        }
    );


    // =========================================
    // EDIT CABANG
    // =========================================

    document
        .querySelectorAll(".btn-edit-cabang")
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

                    modalTitle.textContent=
                        "Edit Cabang";

                    openModal();
                }
            );
        });


    // =========================================
    // AKTIF / NONAKTIF CABANG
    // =========================================

    document
        .querySelectorAll(".btn-status-cabang")
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
                        ?"Nonaktifkan cabang ini?"
                        :"Aktifkan kembali cabang ini?";

                    const yakin=
                        await showConfirm(
                            pesan,
                            "Konfirmasi Cabang"
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
                                "../api/cabang-status.php",
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
                                "Gagal mengubah status cabang."
                            );
                        }

                        showToast(
                            result.message,
                            "success"
                        );

                        loadPage(
                            "cabang"
                        );

                    }catch(error){

                        console.error(
                            "Status Cabang:",
                            error
                        );

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
    // SIMPAN CABANG
    // =========================================

    form.addEventListener(
        "submit",
        async e=>{

            e.preventDefault();

            if(!kodeInput.value.trim()){

                showToast(
                    "Kode cabang wajib diisi.",
                    "warning"
                );

                kodeInput.focus();

                return;
            }

            if(!namaInput.value.trim()){

                showToast(
                    "Nama cabang wajib diisi.",
                    "warning"
                );

                namaInput.focus();

                return;
            }

            try{

                const response=
                    await fetch(
                        "../api/cabang-simpan.php",
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
                        "Gagal menyimpan cabang."
                    );
                }

                closeModal();

                showToast(
                    result.message,
                    "success"
                );

                loadPage(
                    "cabang"
                );

            }catch(error){

                console.error(
                    "Simpan Cabang:",
                    error
                );

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


    // =========================================
    // FUNCTION MODAL
    // =========================================

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