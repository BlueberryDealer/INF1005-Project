// admin_script.js

document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("productSearch");
    const productRows = document.querySelectorAll(".product-row");

    searchInput.addEventListener("keyup", function(event) {
        const searchTerm = event.target.value.toLowerCase().trim();

        productRows.forEach(row => {
            // Target the name and description columns specifically
            const productName = row.querySelector(".product-name").textContent.toLowerCase();
            const productDesc = row.querySelector(".product-desc").textContent.toLowerCase();

            // If the search term is found in either name or description, display the row
            if (productName.includes(searchTerm) || productDesc.includes(searchTerm)) {
                row.style.display = ""; // Reset to default table row display
            } else {
                row.style.display = "none"; // Hide the row entirely
            }
        });
    });
});