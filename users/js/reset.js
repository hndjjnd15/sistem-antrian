document.querySelectorAll(".btnReset").forEach(btn => {
    btn.onclick = () => {
        resetId.value = btn.dataset.id;
        resetModal.classList.add("show");
    };
});

closeReset.onclick = batalReset.onclick = () => resetModal.classList.remove("show");

formResetPassword.addEventListener("submit", async e => {
    e.preventDefault();

    const r = await fetch("reset-password-action.php",{
        method:"POST",
        body:new FormData(formResetPassword)
    });

    const data = await r.json();

    if (!data.success) {
        showToast(data.message,"error");
        return;
    }

    resetModal.classList.remove("show");
    formResetPassword.reset();
    showToast(data.message);
});