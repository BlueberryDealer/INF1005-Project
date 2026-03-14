document.addEventListener("DOMContentLoaded", init);

function init() {
    initSearch();
    initCartButtons();
    updateCartCount();
    updateCartPreview();
    initForms();
    initCartPage();
}

/* SEARCH */
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

    products.forEach(function (product) {
        const name = (product.dataset.name || "").toLowerCase();
        const category = (product.dataset.category || "").toLowerCase();

        const matches = name.includes(query) || category.includes(query);
        product.style.display = matches ? "" : "none";
    });
}

/* CART */
function initCartButtons() {
    const buttons = document.querySelectorAll(".add-cart");
    if (!buttons.length) return;

    buttons.forEach(function (button) {
        button.addEventListener("click", handleAddToCart);
    });
}

function handleAddToCart(e) {
    const product = e.target.closest(".product-card");
    if (!product) return;

    const name = product.dataset.name;
    const price = parseFloat(product.dataset.price) || 0;

    let cart = getCart();
    const existing = cart.find(function (item) {
        return item.name === name;
    });

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
    updateCartPreview();
    updateCartCount();
    updateCartPreview();
    showMessage(name + " added to cart");
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
    const totalItems = cart.reduce(function (sum, item) {
        return sum + item.qty;
    }, 0);

    cartCount.textContent = totalItems;

    if (totalItems === 0) {
        cartCount.style.display = "none";
    } else {
        cartCount.style.display = "flex";
}
}


function initCartPage() { // SIMPLE CART PAGE
    const cartItems = document.getElementById("cartItems");
    const cartSubtotal = document.getElementById("cartSubtotal");

    if (!cartItems || !cartSubtotal) return;

    renderCart();
}

function renderCart() {
    const cartItems = document.getElementById("cartItems");
    const cartSubtotal = document.getElementById("cartSubtotal");

    if (!cartItems || !cartSubtotal) return;

    let cart = getCart();

    if (cart.length === 0) {
        cartItems.innerHTML = "<p>Your cart is empty.</p>";
        cartSubtotal.textContent = "0.00";
        return;
    }

    let html = "";
    let total = 0;

    cart.forEach(function (item, index) {
        const subtotal = item.price * item.qty;
        total += subtotal;

        html += `
            <div class="cart-item-row" data-index="${index}">
                <span>${item.name}</span>
                <button type="button" class="decrease">-</button>
                <span>${item.qty}</span>
                <button type="button" class="increase">+</button>
                <span>$${subtotal.toFixed(2)}</span>
            </div>
        `;
    });

    cartItems.innerHTML = html;
    cartSubtotal.textContent = total.toFixed(2);

    addCartQuantityEvents();
}
function updateCartPreview() { // cart preview feature (try first)
    const preview = document.getElementById("cartPreview");
    if (!preview) return;

    const cart = getCart();

    if (cart.length === 0) {
        preview.innerHTML = "<p class='empty-cart'>Cart is empty</p>";
        return;
    }

    let html = "";

    cart.forEach(function(item) {
        html += `
            <div class="cart-preview-item">
                ${item.name} x ${item.qty}
            </div>
        `;
    });

    preview.innerHTML = html;
}

function addCartQuantityEvents() {
    const increaseButtons = document.querySelectorAll(".increase");
    const decreaseButtons = document.querySelectorAll(".decrease");

    increaseButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const row = button.closest(".cart-item-row");
            const index = row.dataset.index;

            let cart = getCart();
            cart[index].qty += 1;
            saveCart(cart);
            updateCartCount();
            renderCart();
        });
    });

    decreaseButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const row = button.closest(".cart-item-row");
            const index = row.dataset.index;

            let cart = getCart();
            cart[index].qty -= 1;

            if (cart[index].qty <= 0) {
                cart.splice(index, 1);
            }

            saveCart(cart);
            updateCartCount();
            renderCart();
        });
    });
}

/* FORM VALIDATION */
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
function showMessage(message) { //message box 
    let messageBox = document.getElementById("messageBox");

    if (!messageBox) {
        messageBox = document.createElement("div");
        messageBox.id = "messageBox";
        messageBox.className = "message-box";
        document.body.appendChild(messageBox);
    }

    messageBox.textContent = message;
    messageBox.style.display = "block";

    setTimeout(function () {
        messageBox.style.display = "none";
    }, 1500);
}

