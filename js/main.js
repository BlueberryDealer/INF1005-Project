document.addEventListener("DOMContentLoaded", init);

function init() {
    initMobileMenu();
    initDropdowns();
    initSearch();
    initProductFilters();
    initAdminSearch();
    initAuthForms();
    initOrderHistory();
}

function initMobileMenu() {
    const hamburgerBtn = document.getElementById("hamburgerBtn");
    const mobileMenu = document.getElementById("mobileMenu");
    const mobileOverlay = document.getElementById("mobileOverlay");

    if (!hamburgerBtn || !mobileMenu || !mobileOverlay) return;

    const focusableItems = mobileMenu.querySelectorAll("a, button");

    const setMobileMenuState = function (isOpen) {
        mobileMenu.classList.toggle("is-open", isOpen);
        mobileOverlay.classList.toggle("is-visible", isOpen);
        hamburgerBtn.classList.toggle("is-active", isOpen);
        hamburgerBtn.setAttribute("aria-expanded", isOpen ? "true" : "false");
        mobileMenu.setAttribute("aria-hidden", isOpen ? "false" : "true");
        mobileOverlay.setAttribute("aria-hidden", isOpen ? "false" : "true");

        focusableItems.forEach(function (item) {
            item.tabIndex = isOpen ? 0 : -1;
        });
    };

    setMobileMenuState(false);

    hamburgerBtn.addEventListener("click", function () {
        const isOpen = mobileMenu.classList.contains("is-open");
        setMobileMenuState(!isOpen);
    });

    mobileOverlay.addEventListener("click", function () {
        setMobileMenuState(false);
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            setMobileMenuState(false);
        }
    });

    window.addEventListener("resize", function () {
        if (window.innerWidth > 900) {
            setMobileMenuState(false);
        }
    });
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
    }

    if (hasProducts) {
        searchInput.addEventListener("input", function () {
            if (document.getElementById("categoryFilter")) {
                syncProductQueryState({ replace: true });
                return;
            }

            filterProducts(searchInput.value);
        });
    }

    searchForm.addEventListener("submit", function (e) {
        const query = searchInput.value.trim();
        const action = searchForm.getAttribute("action") || "/pages/products.php";

        if (hasProducts) {
            e.preventDefault();
            syncProductQueryState();
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
            const name = row.querySelector(".dash-product-name")?.textContent.toLowerCase() ?? "";
            const matches = query === "" || name.includes(query);
            row.style.display = matches ? "" : "none";
        });
    });
}

function initOrderHistory() {
    // Accordion toggle
    document.querySelectorAll(".oh-order-header").forEach(function (btn) {
        btn.addEventListener("click", function () {
            var expanded = this.getAttribute("aria-expanded") === "true";
            // Close all
            document.querySelectorAll(".oh-order-header").forEach(function (b) {
                b.setAttribute("aria-expanded", "false");
            });
            document.querySelectorAll(".oh-order-detail").forEach(function (d) {
                d.classList.remove("oh-open");
            });
            // Toggle current
            if (!expanded) {
                this.setAttribute("aria-expanded", "true");
                var detail = this.parentElement.querySelector(".oh-order-detail");
                if (detail) detail.classList.add("oh-open");
            }
        });
    });

    // Search filter
    var search = document.getElementById("orderSearch");
    if (search) {
        search.addEventListener("input", function () {
            var term = this.value.toLowerCase();
            document.querySelectorAll(".oh-order").forEach(function (order) {
                var data = order.getAttribute("data-search") || "";
                order.style.display = data.includes(term) ? "" : "none";
            });
        });
    }
}

function initAuthForms() {
    initPasswordToggles();

    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    if (loginForm) {
        initLoginValidation(loginForm);
    }

    if (registerForm) {
        initRegisterValidation(registerForm);
    }
}

function initPasswordToggles() {
    document.querySelectorAll(".password-toggle").forEach(function (toggle) {
        toggle.addEventListener("click", function () {
            const targetId = toggle.dataset.target;
            const input = document.getElementById(targetId);
            if (!input) return;

            const showPassword = input.type === "password";
            input.type = showPassword ? "text" : "password";
            toggle.textContent = showPassword ? "Hide" : "Show";
            toggle.setAttribute("aria-label", showPassword ? "Hide password" : "Show password");
        });
    });
}

function initProductFilters() {
    const productList = document.getElementById("productList");
    const skeletonGrid = document.getElementById("skeletonGrid");
    const categoryFilter = document.getElementById("categoryFilter");
    const stockFilter = document.getElementById("stockFilter");
    const sortProducts = document.getElementById("sortProducts");
    const clearButton = document.getElementById("clearProductFilters");

    if (!productList) return;

    if (skeletonGrid) {
        skeletonGrid.style.display = "none";
    }
    productList.style.display = "";

    const cards = Array.from(productList.querySelectorAll(".product-card[data-name]"));
    if (cards.length === 0) return;

    const applyFiltersAndSort = function () {
        const categoryValue = (categoryFilter?.value || "").trim().toLowerCase();
        const stockValue = stockFilter?.value || "";
        const searchValue = (document.getElementById("searchInput")?.value || "").trim().toLowerCase();
        const sortValue = sortProducts?.value || "default";

        cards.forEach(function (card) {
            const name = (card.dataset.name || "").toLowerCase();
            const category = (card.dataset.category || "").toLowerCase();
            const description = (card.querySelector(".shop-card-desc")?.textContent || "").toLowerCase();
            const stock = Number(card.dataset.stock || 0);

            const matchesSearch =
                searchValue === "" ||
                name.includes(searchValue) ||
                category.includes(searchValue) ||
                description.includes(searchValue);

            const matchesCategory =
                categoryValue === "" || category === categoryValue;

            const matchesStock =
                stockValue === "" ||
                (stockValue === "in-stock" && stock > 0) ||
                (stockValue === "out-of-stock" && stock <= 0);

            const wrapper = card.closest(".col-sm-6, .col-md-4, .col-lg-3");
            if (wrapper) {
                wrapper.style.display = matchesSearch && matchesCategory && matchesStock ? "" : "none";
            }
        });

        const sortedCards = [...cards].sort(function (a, b) {
            const nameA = (a.dataset.name || "").toLowerCase();
            const nameB = (b.dataset.name || "").toLowerCase();
            const priceA = Number(a.dataset.price || 0);
            const priceB = Number(b.dataset.price || 0);
            const orderA = Number(a.dataset.defaultOrder || 0);
            const orderB = Number(b.dataset.defaultOrder || 0);

            switch (sortValue) {
                case "name-asc":
                    return nameA.localeCompare(nameB);
                case "name-desc":
                    return nameB.localeCompare(nameA);
                case "price-asc":
                    return priceA - priceB;
                case "price-desc":
                    return priceB - priceA;
                default:
                    return orderA - orderB;
            }
        });

        sortedCards.forEach(function (card) {
            const wrapper = card.closest(".col-sm-6, .col-md-4, .col-lg-3");
            if (wrapper) {
                productList.appendChild(wrapper);
            }
        });
    };

    categoryFilter?.addEventListener("change", function () {
        applyFiltersAndSort();
        syncProductQueryState();
    });

    stockFilter?.addEventListener("change", function () {
        applyFiltersAndSort();
        syncProductQueryState();
    });

    sortProducts?.addEventListener("change", function () {
        applyFiltersAndSort();
        syncProductQueryState();
    });

    clearButton?.addEventListener("click", function () {
        const searchInput = document.getElementById("searchInput");
        if (searchInput) searchInput.value = "";
        if (categoryFilter) categoryFilter.value = "";
        if (stockFilter) stockFilter.value = "";
        if (sortProducts) sortProducts.value = "default";
        applyFiltersAndSort();
        syncProductQueryState();
    });

    applyFiltersAndSort();
}

function syncProductQueryState(options = {}) {
    const action = document.getElementById("siteSearchForm")?.getAttribute("action") || "/pages/products.php";
    const searchValue = document.getElementById("searchInput")?.value.trim() || "";
    const categoryValue = document.getElementById("categoryFilter")?.value || "";
    const stockValue = document.getElementById("stockFilter")?.value || "";
    const sortValue = document.getElementById("sortProducts")?.value || "default";
    const url = new URL(action, window.location.origin);

    if (searchValue !== "") url.searchParams.set("search", searchValue);
    if (categoryValue !== "") url.searchParams.set("category", categoryValue);
    if (stockValue !== "") url.searchParams.set("stock", stockValue);
    if (sortValue !== "default") url.searchParams.set("sort", sortValue);

    if (options.replace) {
        window.history.replaceState({}, "", url);
        return;
    }

    window.location.href = url.toString();
}

function initLoginValidation(form) {
    const emailInput = form.querySelector("#email");
    const passwordInput = form.querySelector("#password");
    const emailError = form.querySelector("#emailError");
    const passwordError = form.querySelector("#passwordError");
    const formAlert = form.querySelector("#loginFormAlert");

    if (!emailInput || !passwordInput || !emailError || !passwordError) return;

    const validate = function () {
        let isValid = true;
        let firstError = "";

        if (!isValidEmail(emailInput.value)) {
            const message = "Please enter a valid email address.";
            setFieldError(emailInput, emailError, message);
            firstError = firstError || message;
            isValid = false;
        } else {
            clearFieldError(emailInput, emailError);
        }

        if (passwordInput.value.trim().length < 8) {
            const message = "Password must be at least 8 characters.";
            setFieldError(passwordInput, passwordError, message);
            firstError = firstError || message;
            isValid = false;
        } else {
            clearFieldError(passwordInput, passwordError);
        }

        updateFormAlert(formAlert, isValid ? "" : firstError);
        return isValid;
    };

    emailInput.addEventListener("input", validate);
    passwordInput.addEventListener("input", validate);

    form.addEventListener("submit", function (e) {
        if (!validate()) {
            e.preventDefault();
        }
    });
}

function initRegisterValidation(form) {
    const formAlert = form.querySelector("#registerFormAlert");
    const fields = {
        fname: {
            input: form.querySelector("#fname"),
            error: form.querySelector("#fnameError"),
            validate: function (value) {
                if (value.trim() === "") return "";
                return value.trim().length > 50 ? "First name must be 50 characters or fewer." : "";
            }
        },
        lname: {
            input: form.querySelector("#lname"),
            error: form.querySelector("#lnameError"),
            validate: function (value) {
                if (value.trim() === "") return "Last name is required.";
                return value.trim().length > 50 ? "Last name must be 50 characters or fewer." : "";
            }
        },
        email: {
            input: form.querySelector("#email"),
            error: form.querySelector("#emailError"),
            validate: function (value) {
                return isValidEmail(value) ? "" : "Please enter a valid email address.";
            }
        },
        password: {
            input: form.querySelector("#password"),
            error: form.querySelector("#passwordError"),
            validate: function (value) {
                const trimmedValue = value.trim();
                if (trimmedValue.length < 8) {
                    return "Password must be at least 8 characters.";
                }

                const hasUppercase = /[A-Z]/.test(trimmedValue);
                const hasLowercase = /[a-z]/.test(trimmedValue);
                const hasNumber = /[0-9]/.test(trimmedValue);
                const hasSpecial = /[!@#$%^&*()]/.test(trimmedValue);

                if (!hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
                    return "Weak password, please include capital letters, numbers, and a special character like @.";
                }

                return "";
            }
        },
        passwordConfirm: {
            input: form.querySelector("#password_confirm"),
            error: form.querySelector("#passwordConfirmError"),
            validate: function (value) {
                if (value.trim() === "") return "Please confirm your password.";
                return value !== fields.password.input.value ? "Passwords do not match." : "";
            }
        }
    };

    const validateAll = function () {
        let isValid = true;
        let firstError = "";

        Object.values(fields).forEach(function (field) {
            if (!field.input || !field.error) return;

            const message = field.validate(field.input.value);
            if (message) {
                setFieldError(field.input, field.error, message);
                firstError = firstError || message;
                isValid = false;
            } else {
                clearFieldError(field.input, field.error);
            }
        });

        updateFormAlert(formAlert, isValid ? "" : firstError);
        return isValid;
    };

    Object.values(fields).forEach(function (field) {
        if (!field.input) return;
        field.input.addEventListener("input", validateAll);
    });

    form.addEventListener("submit", function (e) {
        if (!validateAll()) {
            e.preventDefault();
        }
    });
}

function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}

function setFieldError(input, errorElement, message) {
    input.classList.add("is-invalid");
    errorElement.textContent = message;
}

function clearFieldError(input, errorElement) {
    input.classList.remove("is-invalid");
    errorElement.textContent = "";
}

function updateFormAlert(alertElement, message) {
    if (!alertElement) return;

    if (!message) {
        alertElement.hidden = true;
        alertElement.textContent = "";
        return;
    }

    alertElement.hidden = false;
    alertElement.textContent = message;
}
