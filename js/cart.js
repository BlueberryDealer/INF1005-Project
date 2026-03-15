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
    const grandTotalEl = document.getElementById('cart-grand-total');
    const totalDisplayEl = document.getElementById('cart-total-display');

    document.querySelectorAll('.add-cart').forEach(button => {
        button.addEventListener('click', async (e) => {
            const card = e.target.closest('.product-card');
            if (!card) return;

            const productId = card.dataset.productId;
            const name = card.dataset.name ?? 'Item';

            if (!productId || !csrfToken) {
                alert('Unable to add item to cart.');
                return;
            }

            try {
                const data = await cartAction('add', productId, 1);
                if (data.success) {
                    updateCartBadge(data.cart_count);
                    announce(name + ' added to cart.');
                } else {
                    alert(data.message || 'Failed to add item.');
                }
            } catch {
                alert('Could not add item to cart.');
            }
        });
    });

    function announce(msg) {
        if (cartStatus) {
            cartStatus.textContent = '';
            setTimeout(() => { cartStatus.textContent = msg; }, 50);
        }
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
            return await res.json();
        } catch (err) {
            console.error('Preview fetch failed:', err);
            return { success: false, count: 0, total: 0, items: [] };
        }
    }

    async function renderCartPreview() {
        const preview = document.getElementById('cartPreview');
        if (!preview) return;

        const data = await fetchCartPreviewData();

        if (!data.success || data.count <= 0 || !data.items.length) {
            preview.innerHTML = `
                <div class="p-3 small text-muted">Your cart is empty.</div>
            `;
            return;
        }

        const itemsHtml = data.items.slice(0, 3).map(item => `
            <div class="d-flex align-items-start gap-2 mb-2 small">
                <img
                    src="${item.image}"
                    alt="${item.name}"
                    width="42"
                    height="42"
                    class="rounded object-fit-cover flex-shrink-0"
                    onerror="this.src='/assets/images/placeholder.png'"
                >
                <div class="flex-grow-1">
                    <div class="fw-semibold">${item.name}</div>
                    <div class="text-muted">Qty: ${item.quantity}</div>
                </div>
                <div>$${Number(item.subtotal).toFixed(2)}</div>
            </div>
        `).join('');

        preview.innerHTML = `
            <div class="p-3">
                <div class="fw-semibold mb-2">Cart Preview</div>
                ${itemsHtml}
                ${data.items.length > 3 ? `<div class="small text-muted mb-2">+ ${data.items.length - 3} more item(s)</div>` : ''}
                <div class="d-flex justify-content-between small border-top pt-2 mt-2">
                    <span>Subtotal</span>
                    <strong>$${Number(data.total).toFixed(2)}</strong>
                </div>
                <a href="/pages/cart.php" class="btn btn-sm btn-primary w-100 mt-2">View Cart</a>
            </div>
        `;
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
        const subtotalVal = parseFloat(subtotalEl.textContent.replace('$', '')) || 0;
        const unitPrice = qty > 0 ? subtotalVal / qty : 0;

        subtotalEl.dataset.subtotal  = subtotalVal;
        subtotalEl.dataset.unitPrice = unitPrice;
    });

    recalcTotal();
    renderCartPreview();
});