/**
 * cart.js
 * Handles all client-side cart interactions:
 *  - Quantity +/- buttons
 *  - Manual quantity input
 *  - Remove item
 *  - Live subtotal + grand total recalculation
 *  - AJAX calls to cart_actions.php
 *
 * Role 5 note: this file can be merged into main.js.
 * Just ensure the DOMContentLoaded listener wraps everything.
 */

document.addEventListener('DOMContentLoaded', () => {

    const csrfToken   = document.getElementById('csrf-token')?.value ?? '';
    const cartStatus  = document.getElementById('cart-status');
    const messageBox = document.getElementById('messageBox');
    const grandTotalEl = document.getElementById('cart-grand-total');
    const totalDisplayEl = document.getElementById('cart-total-display');
    const preview = document.getElementById('cartPreview');
    const cartContainer = document.querySelector('.cart-container');
    let messageTimeout;

    document.querySelectorAll('.add-cart').forEach(button => {
        button.addEventListener('click', async (e) => {
            const card = e.target.closest('.product-card');

            const productId = card ? card.dataset.productId : button.dataset.productId;
            const name = card ? (card.dataset.name ?? 'Item') : (button.dataset.name ?? 'Item');

            if (!productId || !csrfToken) {
                showMessage('Unable to add item to cart.', 'error');
                return;
            }

            try {
                const data = await cartAction('add', productId, 1);
                if (data.success) {
                    updateCartBadge(data.cart_count);
                    announce(name + ' added to cart.');
                    showMessage(name + ' added to cart.', 'success');
                } else {
                    showMessage(data.message || 'Failed to add item.', 'error');
                }
            } catch {
                showMessage('Could not add item to cart.', 'error');
            }
        });
    });

    function announce(msg) {
        if (cartStatus) {
            cartStatus.textContent = '';
            setTimeout(() => { cartStatus.textContent = msg; }, 50);
        }
    }

    function showMessage(msg, type = 'success') {
        if (!messageBox) return;

        messageBox.textContent = msg;
        messageBox.className = `message-box message-box--${type}`;
        messageBox.style.display = 'block';

        if (messageTimeout) {
            clearTimeout(messageTimeout);
        }

        messageTimeout = setTimeout(() => {
            messageBox.style.display = 'none';
        }, 2200);
    }

    async function cartAction(action, productId = 0, quantity = 1) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', action);
        formData.append('product_id', productId);
        formData.append('quantity', quantity);

        const res  = await fetch('/pages/cart_actions.php', { method: 'POST', body: formData });
        const data = await res.json();
        return data;
    }

    function recalcTotal() {
        let total = 0;
        document.querySelectorAll('.item-subtotal').forEach(el => {
            total += parseFloat(el.dataset.subtotal ?? 0);
        });
        const formatted = '$' + total.toFixed(2);
        if (grandTotalEl) grandTotalEl.textContent = formatted;
        if (totalDisplayEl) totalDisplayEl.textContent = formatted;
    }

    function updateRowSubtotal(row, unitPrice, qty) {
        const subtotalEl = row.querySelector('.item-subtotal');
        if (!subtotalEl) return;
        const subtotal = unitPrice * qty;
        subtotalEl.dataset.subtotal = subtotal;
        subtotalEl.textContent = '$' + subtotal.toFixed(2);
    }

    function updateCartBadge(count) {
        const badge = document.getElementById('cartCount');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }

        renderCartPreview();
    }

    async function fetchCartPreviewData() {
        try {
            const res = await fetch('/pages/cart_preview.php');
            if (!res.ok) {
                throw new Error('Preview request failed with status ' + res.status);
            }
            return await res.json();
        } catch (err) {
            console.error('Preview fetch failed:', err);
            return { success: false, count: 0, total: 0, items: [] };
        }
    }

    async function renderCartPreview() {
        if (!preview) return;

        const data = await fetchCartPreviewData();

        if (!data.success || data.count <= 0 || !data.items.length) {
            preview.innerHTML = `
                <div class="cart-preview-empty">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p>Your cart is empty</p>
                    <a href="/pages/products.php" class="cart-preview-shop-link">Start Shopping</a>
                </div>
            `;
            return;
        }

        const itemsHtml = data.items.slice(0, 3).map(item => {
            const unitPrice = Number(item.subtotal) / Number(item.quantity);
            return `
                <div class="cart-preview-item">
                    <img
                        src="${item.image}"
                        alt="${item.name}"
                        class="cart-preview-img"
                        loading="lazy"
                        onerror="this.src='/images/placeholder.png'"
                    >
                    <div class="cart-preview-info">
                        <span class="cart-preview-name">${item.name}</span>
                        <span class="cart-preview-qty">${item.quantity} &times; $${unitPrice.toFixed(2)}</span>
                    </div>
                    <span class="cart-preview-price">$${Number(item.subtotal).toFixed(2)}</span>
                </div>
            `;
        }).join('');

        const moreCount = data.items.length - 3;

        preview.innerHTML = `
            <div class="cart-preview-header">
                <span class="cart-preview-title">Your Cart</span>
                <span class="cart-preview-count">${data.count} item${data.count > 1 ? 's' : ''}</span>
            </div>
            <div class="cart-preview-items">
                ${itemsHtml}
                ${moreCount > 0 ? `<div class="cart-preview-more">+ ${moreCount} more item${moreCount > 1 ? 's' : ''}</div>` : ''}
            </div>
            <div class="cart-preview-footer">
                <div class="cart-preview-subtotal">
                    <span>Subtotal</span>
                    <strong>$${Number(data.total).toFixed(2)}</strong>
                </div>
                <div class="cart-preview-actions">
                    <a href="/pages/cart.php" class="cart-preview-btn cart-preview-btn--primary">View Cart</a>
                    <a href="/pages/checkout.php" class="cart-preview-btn cart-preview-btn--outline">Checkout</a>
                </div>
            </div>
        `;
    }

    if (cartContainer && preview) {
        const ensurePreviewLoaded = async () => {
            await renderCartPreview();
        };

        cartContainer.addEventListener('mouseenter', ensurePreviewLoaded);
        cartContainer.addEventListener('focusin', ensurePreviewLoaded);
    }

    const clearCartBtn = document.getElementById('clearCartBtn');

    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', async () => {
            if (!confirm('Clear all items from cart?')) return;

            try {
                const data = await cartAction('clear', 0, 0);

                if (data.success) {
                    updateCartBadge(0);
                    await renderCartPreview();
                    location.reload();
                } else {
                    alert(data.message || 'Failed to clear cart.');
                }
            } catch (err) {
                console.error(err);
                alert('Could not clear cart.');
            }
        });
    }

    const cartTable = document.getElementById('cart-table');
    if (!cartTable) return;

    cartTable.addEventListener('click', async (e) => {
        const qtyBtn = e.target.closest('.qty-btn');
        if (qtyBtn) {
            const row       = qtyBtn.closest('tr');
            const productId = row.dataset.productId;
            const input     = row.querySelector('.qty-input');
            const unitPrice = parseFloat(
                row.querySelector('.item-subtotal').dataset.unitPrice
                ?? row.querySelector('[data-unit-price]')?.dataset.unitPrice
                ?? 0
            );

            let qty = parseInt(input.value, 10);

            if (qtyBtn.dataset.action === 'increase') {
                qty++;
            } else {
                if (qty <= 1) return;
                qty--;
            }

            input.value = qty;

            try {
                const data = await cartAction('update', productId, qty);
                if (data.success) {
                    updateRowSubtotal(row, unitPrice, qty);
                    recalcTotal();
                    updateCartBadge(data.cart_count);
                    announce('Quantity updated.');
                }
            } catch {
                announce('Could not update quantity. Please try again.');
            }
            return;
        }

        const removeBtn = e.target.closest('.remove-btn');
        if (removeBtn) {
            const row       = removeBtn.closest('tr');
            const productId = row.dataset.productId;
            const name      = row.querySelector('span.fw-semibold')?.textContent ?? 'Item';

            try {
                const data = await cartAction('remove', productId);
                if (data.success) {
                    row.remove();
                    recalcTotal();
                    updateCartBadge(data.cart_count);
                    announce(name + ' removed from cart.');

                    const remainingRows = cartTable.querySelectorAll('tbody tr');
                    if (remainingRows.length === 0) {
                        location.reload();
                    }
                }
            } catch {
                announce('Could not remove item. Please try again.');
            }
            return;
        }
    });

    cartTable.addEventListener('change', async (e) => {
        const input = e.target.closest('.qty-input');
        if (!input) return;

        const row       = input.closest('tr');
        const productId = row.dataset.productId;
        const unitPrice = parseFloat(
            row.querySelector('.item-subtotal')?.dataset.unitPrice ?? 0
        );

        let qty = parseInt(input.value, 10);

        if (isNaN(qty) || qty < 1) qty = 1;
        input.value = qty;

        try {
            const data = await cartAction('update', productId, qty);
            if (data.success) {
                updateRowSubtotal(row, unitPrice, qty);
                recalcTotal();
                updateCartBadge(data.cart_count);
            }
        } catch {
            announce('Could not update quantity. Please try again.');
        }
    });

    cartTable.querySelectorAll('tbody tr').forEach(row => {
        const subtotalEl = row.querySelector('.item-subtotal');
        const input      = row.querySelector('.qty-input');
        if (!subtotalEl || !input) return;

        const qty = parseInt(input.value, 10);
        const subtotalVal = subtotalEl.dataset.subtotal
        ? parseFloat(subtotalEl.dataset.subtotal)
        : parseFloat(subtotalEl.textContent.replace('$', '').replace(/,/g, '')) || 0;
        const unitPrice = subtotalEl.dataset.unitPrice
        ? parseFloat(subtotalEl.dataset.unitPrice)
        : (qty > 0 ? subtotalVal / qty : 0);

        subtotalEl.dataset.subtotal  = subtotalVal;
        subtotalEl.dataset.unitPrice = unitPrice;
    });
    recalcTotal();
});