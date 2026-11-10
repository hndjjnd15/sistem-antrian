const modal = document.getElementById("userModal");
const form = document.getElementById("formTambahUser");
const toast = document.getElementById("toast");

function openModal() {
    modal.classList.add("show");
}

function closeModal() {
    modal.classList.remove("show");
    form.reset();
}

function showToast(message, type = "success") {
    toast.textContent = message;
    toast.className = `toast show ${type}`;

    setTimeout(() => {
        toast.className = "toast";
    }, 2500);
}

document.getElementById("btnTambahUser").addEventListener("click", openModal);
document.getElementById("closeModal").addEventListener("click", closeModal);
document.getElementById("btnBatal").addEventListener("click", closeModal);

modal.addEventListener("click", e => {
    if (e.target === modal) closeModal();
});

form.addEventListener("submit", async e => {
    e.preventDefault();

    const btn = document.getElementById("btnSimpan");
    btn.disabled = true;
    btn.textContent = "MENYIMPAN...";

    try {
        const response = await fetch("tambah-action.php", {
            method: "POST",
            body: new FormData(form)
        });

        const result = await response.json();

        if (!result.success) {
            showToast(result.message, "error");
            return;
        }

        closeModal();
        showToast(result.message, "success");

        setTimeout(() => {
            location.reload();
        }, 900);

    } catch (error) {
        showToast("Tidak dapat terhubung ke server.", "error");
    } finally {
        btn.disabled = false;
        btn.textContent = "SIMPAN";
    }
});