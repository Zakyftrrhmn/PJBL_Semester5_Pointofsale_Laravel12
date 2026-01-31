import "./bootstrap";
import "./index"; // 🔥 INI WAJIB, jangan dihapus

import Alpine from "alpinejs";
import persist from "@alpinejs/persist"; // ← TAMBAHKAN INI

Alpine.plugin(persist); // ← TAMBAHKAN INI
window.Alpine = Alpine;
Alpine.start();

// Tunggu halaman siap sebelum load library berat
document.addEventListener("DOMContentLoaded", () => {
    // ===== DataTables hanya jika ada table =====
    if (window.$ && $.fn.dataTable && document.querySelector("#myTable")) {
        $("#myTable").DataTable();
    }

    // ===== TomSelect hanya jika ada select =====
    const selects = document.querySelectorAll(".tom-select");
    if (selects.length > 0) {
        import("tom-select").then(({ default: TomSelect }) => {
            window.TomSelect = TomSelect;
            selects.forEach((s) => new TomSelect(s));
        });
    }

    // ===== Flowbite hanya jika ada dropdown/modal =====
    if (document.querySelector("[data-dropdown], [data-modal]")) {
        import("flowbite");
    }
});
