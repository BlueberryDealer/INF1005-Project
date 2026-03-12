/* ============================================================
   ADMIN DASHBOARD — dashboard.js
   Handles: live search, delete modal, toast auto-dismiss
   ============================================================ */

(function () {
  "use strict";

  /* ── Live Search ─────────────────────────────────────────── */
  const searchInput  = document.getElementById("searchInput");
  const tableBody    = document.getElementById("tableBody");
  const visibleCount = document.getElementById("visibleCount");

  if (searchInput && tableBody) {
    searchInput.addEventListener("input", function () {
      const query = this.value.toLowerCase().trim();
      const rows  = tableBody.querySelectorAll(".table-row");
      let count   = 0;

      rows.forEach(function (row) {
        // Search across name, description, price columns
        const name  = row.querySelector(".product-name")?.textContent.toLowerCase() ?? "";
        const desc  = row.querySelector(".product-desc")?.textContent.toLowerCase() ?? "";
        const price = row.querySelector(".price-tag")?.textContent.toLowerCase() ?? "";
        const id    = row.querySelector(".id-badge")?.textContent.toLowerCase() ?? "";

        const match = name.includes(query) || desc.includes(query) ||
                      price.includes(query) || id.includes(query);

        row.classList.toggle("hidden", !match);
        if (match) count++;
      });

      if (visibleCount) visibleCount.textContent = count;
    });
  }

  /* ── Delete Confirmation Modal ───────────────────────────── */
  const overlay         = document.getElementById("modalOverlay");
  const modalProductName = document.getElementById("modalProductName");
  const modalConfirm    = document.getElementById("modalConfirm");
  const modalCancel     = document.getElementById("modalCancel");

  // Open modal when any Delete button is clicked
  document.querySelectorAll(".btn-delete").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const id   = this.dataset.id;
      const name = this.dataset.name;

      if (modalProductName) modalProductName.textContent = '"' + name + '"';
      if (modalConfirm)     modalConfirm.href = "dashboard.php?action=delete&id=" + id;

      openModal();
    });
  });

  // Close on Cancel button
  if (modalCancel) {
    modalCancel.addEventListener("click", closeModal);
  }

  // Close on overlay click (outside modal card)
  if (overlay) {
    overlay.addEventListener("click", function (e) {
      if (e.target === overlay) closeModal();
    });
  }

  // Close on Escape key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeModal();
  });

  function openModal() {
    if (overlay) overlay.classList.add("open");
    document.body.style.overflow = "hidden";
  }

  function closeModal() {
    if (overlay) overlay.classList.remove("open");
    document.body.style.overflow = "";
  }

  /* ── Toast Auto-dismiss ──────────────────────────────────── */
  const toast = document.getElementById("toast");
  if (toast) {
    setTimeout(function () {
      toast.style.transition = "opacity .4s ease, transform .4s ease";
      toast.style.opacity    = "0";
      toast.style.transform  = "translateY(-6px)";
      setTimeout(function () { toast.remove(); }, 450);
    }, 3500);
  }

})();