const editModal = document.getElementById("editModal");

document.querySelectorAll(".btnEdit").forEach(btn => {
    btn.addEventListener("click", () => {
        editId.value = btn.dataset.id;
        editNama.value = btn.dataset.nama;
        editUsername.value = btn.dataset.username;
        editCabang.value = btn.dataset.cabang;
        editRole.value = btn.dataset.role;
        editModal.classList.add("show");
    });
});

closeEdit.onclick = batalEdit.onclick = () => editModal.classList.remove("show");

formEditUser.addEventListener("submit", async e => {
    e.preventDefault();

    const r = await fetch("edit-action.php", {
        method:"POST",
        body:new FormData(formEditUser)
    });

    const data = await r.json();

    if (!data.success) {
        showToast(data.message,"error");
        return;
    }

    editModal.classList.remove("show");
    showToast(data.message);

    setTimeout(() => location.reload(),800);
});