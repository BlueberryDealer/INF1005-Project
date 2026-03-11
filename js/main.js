document.addEventListener("DOMContentLoaded", init);

function init() {
    initSearch();
    initCartButtons();
    updateCartCount();
    initForms();
}

function initSearch() {
    const searchInput = document.getElementById("searchInput");
    const searchForm = document.getElementById("siteSearchForm");

    if (!searchInput || !searchForm) return;

    const hasProducts = document.querySelectorAll(".product-card").length > 0;

    if (hasProducts) {
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
}

function filterProducts() {
    const searchInput = document.getElementById("searchInput");
    if (!searchInput) return;

    const query = searchInput.value.trim().toLowerCase();
    const products = document.querySelectorAll(".product-card");

    products.forEach(product => {
        const name = (product.dataset.name || "").toLowerCase();
        const category = (product.dataset.category || "").toLowerCase();

        const matches = name.includes(query) || category.includes(query);
        product.style.display = matches ? "" : "none";
    });
}

/* ADD TO CART */
function initCartButtons() {
    const buttons = document.querySelectorAll(".add-cart");
    if (!buttons.length) return;

    buttons.forEach(button => {
        button.addEventListener("click", handleAddToCart);
    });
}

function handleAddToCart(e) {
    const product = e.target.closest(".product-card");
    if (!product) return;

    const name = product.dataset.name;
    const price = parseFloat(product.dataset.price) || 0;

    let cart = getCart();

    const existing = cart.find(item => item.name === name);

    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({
            name: name,
            price: price,
            qty: 1
        });
    }

    saveCart(cart);
    updateCartCount();
}

function getCart() {
    return JSON.parse(localStorage.getItem("cart")) || [];
}

function saveCart(cart) {
    localStorage.setItem("cart", JSON.stringify(cart));
}

function updateCartCount() {
    const cartCount = document.getElementById("cartCount");
    if (!cartCount) return;

    const cart = getCart();
    const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);

    cartCount.textContent = totalItems;
}

/* FORM VALIDATION */
function initForms() {
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", validateRegister);
    }
}

function validateRegister(e) {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    if (!email.includes("@")) {
        alert("Please enter a valid email.");
        e.preventDefault();
    }

    if (password.length < 6) {
        alert("Password must be at least 6 characters.");
        e.preventDefault();
    }
}