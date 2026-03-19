/**
 * product_details.js
 * UI-feedback layer for the product details page.
 *
 * WHY NO AJAX HERE:
 * cart.js is loaded globally on every page and already handles the
 * add-to-cart AJAX call for both the products grid AND this page.
 * Making a second AJAX call here would:
 *   1. Double-add the item on every click.
 *   2. Potentially exhaust a single-use CSRF token, causing the second
 *      request to fail silently.
 *
 * Instead, cart.js dispatches a CustomEvent("cartItemAdded") on the
 * button after a successful add.  This script listens for that event
 * and handles the details-page-specific UI: the green button flash.
 *
 * The #pd-cart-status text is written by cart.js's announce() function
 * which already targets that element when it exists on the page.
 */

document.addEventListener('DOMContentLoaded', () => {

    const addBtn = document.querySelector('.add-cart');

    if (!addBtn) return;

    // ── React to cart.js's success event — flash the button green ────────────
    addBtn.addEventListener('cartItemAdded', () => {
        flashButton();
    });

    // ── Visual confirmation flash ─────────────────────────────────────────────

    /**
     * Briefly apply the "added" CSS class for a green confirmation flash.
     * The .pd-btn--added class is defined in styles.css.
     */
    function flashButton() {
        addBtn.classList.add('pd-btn--added');
        setTimeout(() => addBtn.classList.remove('pd-btn--added'), 1200);
    }

});