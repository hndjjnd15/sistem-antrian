document.querySelectorAll(".btnHapus").forEach(btn => {

    btn.addEventListener("click", () => {

        showConfirm(
            "Hapus User?",
            "Data user akan dihapus permanen dan tidak dapat dikembalikan.",
            async()=>{

                const fd = new FormData();
                fd.append("id",btn.dataset.id);

                const r = await fetch("hapus-action.php",{
                    method:"POST",
                    body:fd
                });

                const data = await r.json();

                showToast(
                    data.message,
                    data.success ? "success" : "error"
                );

                if(data.success){
                    setTimeout(()=>location.reload(),700);
                }
            }
        );
    });
});