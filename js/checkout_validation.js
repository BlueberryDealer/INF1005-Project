/**
 * checkout_validation.js
 * Client-side validation for the checkout shipping form.
 * Mirrors the server-side rules in checkout.php.
 * Runs BEFORE the form submits — server-side is the real guard.
 *
 * Role 5 note: can be merged into main.js if preferred.
 */

document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('checkout-form');
    if (!form) return;

    // -------------------------------------------------------
    // Field rules: [fieldId, validator fn, error message]
    // -------------------------------------------------------
    const rules = [
        [
            'full_name',
            v => v.trim().length >= 2,
            'Please enter your full name (min 2 characters).'
        ],
        [
            'email',
            v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()),
            'Please enter a valid email address.'
        ],
        [
            'phone',
            v => /^\+?[\d\s\-]{7,15}$/.test(v.trim()),
            'Please enter a valid phone number (7–15 digits).'
        ],
        [
            'address_line',
            v => v.trim().length > 0,
            'Please enter your street address.'
        ],
        [
            'city',
            v => v.trim().length > 0,
            'Please enter your city.'
        ],
        [
            'postal_code',
            v => /^[A-Za-z0-9\s\-]{3,10}$/.test(v.trim()),
            'Please enter a valid postal code.'
        ],
        [
            'country',
            v => v.trim().length > 0,
            'Please select a country.'
        ],
    ];

    // -------------------------------------------------------
    // Show or clear error on a field
    // -------------------------------------------------------
    function setError(fieldId, message) {
        const el      = document.getElementById(fieldId);
        const errorEl = document.getElementById(fieldId + '_error');
        if (!el) return;

        el.classList.add('is-invalid');
        el.setAttribute('aria-invalid', 'true');

        if (errorEl) {
            errorEl.textContent = message;
        } else {
            // Create the feedback div dynamically if not already in DOM
            const div = document.createElement('div');
            div.id = fieldId + '_error';
            div.className = 'invalid-feedback';
            div.textContent = message;
            el.insertAdjacentElement('afterend', div);
            el.setAttribute('aria-describedby', div.id);
        }
    }

    function clearError(fieldId) {
        const el      = document.getElementById(fieldId);
        const errorEl = document.getElementById(fieldId + '_error');
        if (!el) return;

        el.classList.remove('is-invalid');
        el.classList.add('is-valid');
        el.removeAttribute('aria-invalid');
        if (errorEl) errorEl.textContent = '';
    }

    // -------------------------------------------------------
    // Validate on submit
    // -------------------------------------------------------
    form.addEventListener('submit', (e) => {
        let hasErrors = false;

        rules.forEach(([fieldId, validate, message]) => {
            const el = document.getElementById(fieldId);
            if (!el) return;

            if (!validate(el.value)) {
                setError(fieldId, message);
                hasErrors = true;
            } else {
                clearError(fieldId);
            }
        });

        if (hasErrors) {
            e.preventDefault();
            // Focus the first invalid field
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) firstInvalid.focus();
        }
    });

    // -------------------------------------------------------
    // Inline validation on blur (better UX)
    // -------------------------------------------------------
    rules.forEach(([fieldId, validate, message]) => {
        const el = document.getElementById(fieldId);
        if (!el) return;

        el.addEventListener('blur', () => {
            if (!validate(el.value)) {
                setError(fieldId, message);
            } else {
                clearError(fieldId);
            }
        });

        // Clear error as soon as the user starts correcting input
        el.addEventListener('input', () => {
            if (validate(el.value)) {
                clearError(fieldId);
            }
        });
    });
});