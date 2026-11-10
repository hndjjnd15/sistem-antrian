document.querySelectorAll(".btnStatus").forEach(btn => {

    btn.addEventListener("click", () => {

        const statusBaru =
            btn.dataset.status === "aktif"
            ? "nonaktif"
            : "aktif";

        const title =
            statusBaru === "aktif"
            ? "Aktifkan User?"
            : "Nonaktifkan User?";

        const message =
            statusBaru === "aktif"
            ? "User akan kembali dapat login ke sistem."
            : "User tidak dapat login sampai diaktifkan kembali.";

        showConfirm(title,message,async()=>{

            const fd = new FormData();

            fd.append("id",btn.dataset.id);
            fd.append("status",statusBaru);

            const r = await fetch("status-action.php",{
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
        });
    });
});