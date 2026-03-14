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
    const cartStatus  = document.getElementById('cart-status');   // WCAG live region
    const grandTotalEl = document.getElementById('cart-grand-total');
    const totalDisplayEl = document.getElementById('cart-total-display');

    // -------------------------------------------------------
    // Utility: announce message to screen readers
    // -------------------------------------------------------
    function announce(msg) {
        if (cartStatus) {
            cartStatus.textContent = '';
            // Small timeout forces screen readers to re-read
            setTimeout(() => { cartStatus.textContent = msg; }, 50);
        }
    }

    // -------------------------------------------------------
    // Utility: POST to cart_actions.php
    // -------------------------------------------------------
    async function cartAction(action, productId, quantity = 1) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', action);
        formData.append('product_id', productId);
        formData.append('quantity', quantity);

        const res  = await fetch('/pages/cart_actions.php', { method: 'POST', body: formData });
        const data = await res.json();
        return data;
    }

    // -------------------------------------------------------
    // Recalculate grand total from visible row subtotals
    // -------------------------------------------------------
    function recalcTotal() {
        let total = 0;
        document.querySelectorAll('.item-subtotal').forEach(el => {
            total += parseFloat(el.dataset.subtotal ?? 0);
        });
        const formatted = '$' + total.toFixed(2);
        if (grandTotalEl)  grandTotalEl.textContent  = formatted;
        if (totalDisplayEl) totalDisplayEl.textContent = formatted;
    }

    // -------------------------------------------------------
    // Update a single row's subtotal display
    // -------------------------------------------------------
    function updateRowSubtotal(row, unitPrice, qty) {
        const subtotalEl = row.querySelector('.item-subtotal');
        if (!subtotalEl) return;
        const subtotal = unitPrice * qty;
        subtotalEl.dataset.subtotal = subtotal;
        subtotalEl.textContent = '$' + subtotal.toFixed(2);
    }

    // -------------------------------------------------------
    // Update navbar cart badge count (if Role 1/5 adds one)
    // -------------------------------------------------------
    function updateCartBadge(count) {
        const badge = document.getElementById('cart-badge');
        if (badge) badge.textContent = count;
    }

    // -------------------------------------------------------
    // Event delegation on the cart table
    // -------------------------------------------------------
    const cartTable = document.getElementById('cart-table');
    if (!cartTable) return; // No cart table = empty cart page, nothing to do

    cartTable.addEventListener('click', async (e) => {

        // ------ +/- quantity button ------
        const qtyBtn = e.target.closest('.qty-btn');
        if (qtyBtn) {
            const row       = qtyBtn.closest('tr');
            const productId = row.dataset.productId;
            const input     = row.querySelector('.qty-input');
            const unitPrice = parseFloat(row.querySelector('.item-subtotal').dataset.unitPrice
                              ?? row.querySelector('[data-unit-price]')?.dataset.unitPrice
                              ?? 0);

            let qty = parseInt(input.value, 10);

            if (qtyBtn.dataset.action === 'increase') {
                qty++;
            } else {
                if (qty <= 1) {
                    // Let the user remove via the trash button; just floor at 1
                    return;
                }
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

        // ------ Remove button ------
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

                    // Show empty state if no rows remain
                    const remainingRows = cartTable.querySelectorAll('tbody tr');
                    if (remainingRows.length === 0) {
                        location.reload(); // Reload to show the empty cart UI
                    }
                }
            } catch {
                announce('Could not remove item. Please try again.');
            }
            return;
        }
    });

    // -------------------------------------------------------
    // Manual input change (typing a number directly)
    // -------------------------------------------------------
    cartTable.addEventListener('change', async (e) => {
        const input = e.target.closest('.qty-input');
        if (!input) return;

        const row       = input.closest('tr');
        const productId = row.dataset.productId;
        const unitPrice = parseFloat(
            row.querySelector('.item-subtotal')?.dataset.unitPrice ?? 0
        );

        let qty = parseInt(input.value, 10);

        // Floor at 1
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

    // -------------------------------------------------------
    // Init: set data-unit-price and data-subtotal on each row
    // so recalcTotal() works without page reload
    // -------------------------------------------------------
    cartTable.querySelectorAll('tbody tr').forEach(row => {
        const subtotalEl = row.querySelector('.item-subtotal');
        const input      = row.querySelector('.qty-input');
        if (!subtotalEl || !input) return;

        const qty        = parseInt(input.value, 10);
        // Parse subtotal text "$X.XX" -> number
        const subtotalVal = parseFloat(subtotalEl.textContent.replace('$', '')) || 0;
        const unitPrice  = qty > 0 ? subtotalVal / qty : 0;

        subtotalEl.dataset.subtotal  = subtotalVal;
        subtotalEl.dataset.unitPrice = unitPrice;
    });

    recalcTotal();
});