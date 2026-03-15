document.addEventListener("DOMContentLoaded", init);

function init() {
    initSearch();
    initForms();
}


function initSearch() {
    const searchInput = document.getElementById("searchInput");
    const searchForm = document.getElementById("siteSearchForm");

    if (!searchInput || !searchForm) return;

    const hasProducts = document.querySelectorAll(".product-card").length > 0;
    if (!hasProducts) return;

    searchInput.addEventListener("input", filterProducts);

    const urlParams = new URLSearchParams(window.location.search);
    const queryFromUrl = urlParams.get("search");

    if (queryFromUrl) {
        searchInput.value = queryFromUrl;
        filterProducts();
    }

    searchForm.addEventListener("submit", function (e) {
        e.preventDefault();
        filterProducts();
    });
}

function filterProducts() {
    const searchInput = document.getElementById("searchInput");
    if (!searchInput) return;

    const query = searchInput.value.trim().toLowerCase();
    const products = document.querySelectorAll(".product-card");

    products.forEach(function (product) {
        const name = (product.dataset.name || "").toLowerCase();
        const category = (product.dataset.category || "").toLowerCase();
        const matches = name.includes(query) || category.includes(query);

        const wrapper = product.closest(".col-sm-6, .col-md-4, .mb-4");
        if (wrapper) {
            wrapper.style.display = matches ? "" : "none";
        } else {
            product.style.display = matches ? "" : "none";
        }
    });
}

//Register form validation
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