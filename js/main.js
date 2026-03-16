document.addEventListener("DOMContentLoaded", init);

function init() {
    initDropdowns();
    initSearch();
    initAdminSearch();
    initForms();
}

function initDropdowns() {
    const dropdowns = document.querySelectorAll(".dropdown");

    dropdowns.forEach(function (dropdown) {
        const trigger = dropdown.querySelector(".nav-trigger");
        if (!trigger) return;

        trigger.addEventListener("click", function (e) {
            e.preventDefault();
            const shouldOpen = !dropdown.classList.contains("is-open");

            dropdowns.forEach(function (item) {
                item.classList.remove("is-open");
                item.querySelector(".nav-trigger")?.setAttribute("aria-expanded", "false");
            });

            if (shouldOpen) {
                dropdown.classList.add("is-open");
                trigger.setAttribute("aria-expanded", "true");
            }
        });
    });

    document.addEventListener("click", function (e) {
        dropdowns.forEach(function (dropdown) {
            if (dropdown.contains(e.target)) return;
            dropdown.classList.remove("is-open");
            dropdown.querySelector(".nav-trigger")?.setAttribute("aria-expanded", "false");
        });
    });
}

function initSearch() {
    const searchInput = document.getElementById("searchInput");
    const searchForm = document.getElementById("siteSearchForm");

    if (!searchInput || !searchForm) return;

    const products = document.querySelectorAll(".product-card[data-name]");
    const hasProducts = products.length > 0;
    const urlParams = new URLSearchParams(window.location.search);
    const queryFromUrl = (urlParams.get("search") || "").trim();

    if (queryFromUrl !== "") {
        searchInput.value = queryFromUrl;

        if (hasProducts) {
            filterProducts(queryFromUrl);
        }
    }

    if (hasProducts) {
        searchInput.addEventListener("input", function () {
            filterProducts(searchInput.value);
        });
    }

    searchForm.addEventListener("submit", function (e) {
        const query = searchInput.value.trim();
        const action = searchForm.getAttribute("action") || "/pages/products.php";

        if (hasProducts) {
            e.preventDefault();
            const targetUrl = query === ""
                ? action
                : `${action}?search=${encodeURIComponent(query)}`;

            window.location.href = targetUrl;
            return;
        }

        if (query === "") {
            return;
        }
    });
}

function filterProducts(rawQuery) {
    const query = (rawQuery || "").trim().toLowerCase();
    const products = document.querySelectorAll(".product-card[data-name]");

    products.forEach(function (product) {
        const name = (product.dataset.name || "").toLowerCase();
        const category = (product.dataset.category || "").toLowerCase();
        const description = (
            product.querySelector(".card-text")?.textContent || ""
        ).toLowerCase();

        const matches =
            query === "" ||
            name.includes(query) ||
            category.includes(query) ||
            description.includes(query);

        const wrapper = product.closest(".col-sm-6, .col-md-4, .mb-4");
        if (wrapper) {
            wrapper.style.display = matches ? "" : "none";
        } else {
            product.style.display = matches ? "" : "none";
        }
    });
}

function initAdminSearch() {
    const searchInput = document.getElementById("productSearch");
    const rows = document.querySelectorAll("#productsTable .product-row");

    if (!searchInput || rows.length === 0) return;

    searchInput.addEventListener("input", function () {
        const query = searchInput.value.trim().toLowerCase();

        rows.forEach(function (row) {
            const name = row.querySelector(".product-name")?.textContent.toLowerCase() ?? "";
            const desc = row.querySelector(".product-desc")?.textContent.toLowerCase() ?? "";
            const matches = query === "" || name.includes(query) || desc.includes(query);
            row.style.display = matches ? "" : "none";
        });
    });
}

function initForms() {
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", validateRegister);
    }
}

function validateRegister(e) {
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");

    if (!emailInput || !passwordInput) return;

    let isValid = true;

    if (emailError) emailError.textContent = "";
    if (passwordError) passwordError.textContent = "";

    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();

    if (email === "" || !email.includes("@")) {
        if (emailError) {
            emailError.textContent = "Please enter a valid email.";
        }
        isValid = false;
    }

    if (password.length < 6) {
        if (passwordError) {
            passwordError.textContent = "Password must be at least 6 characters.";
        }
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }
}
