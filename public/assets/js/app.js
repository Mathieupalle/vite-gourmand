document.addEventListener("click", (e) => {
    const el = e.target.closest("[data-confirm]");
    if (!el) return;

    const msg = el.getAttribute("data-confirm") || "Confirmer cette action ?";
    if (!confirm(msg)) e.preventDefault();
});
