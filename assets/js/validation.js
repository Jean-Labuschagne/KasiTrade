/**
 * KasiTrade Form Validation
 * Client-side validation with SA ID Luhn check
 */

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() { validateField(input); });
            input.addEventListener('input', function() {
                if (input.classList.contains('is-invalid')) validateField(input);
            });
        });
    });
});

function validateField(input) {
    const value = input.value.trim();
    let isValid = true;
    let message = '';

    if (input.required && !value) {
        isValid = false;
        message = 'This field is required';
    }

    if (input.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) { isValid = false; message = 'Enter valid email'; }
    }

    if (input.name === 'phone' && value) {
        const phoneRegex = /^0[6-8][0-9]{8}$/;
        if (!phoneRegex.test(value)) { isValid = false; message = 'Enter valid SA mobile (e.g. 0821234567)'; }
    }

    if (input.name === 'sa_id' && value) {
        if (!validateSAID(value)) { isValid = false; message = 'Invalid SA ID number'; }
    }

    if (input.name === 'price' && value) {
        if (parseFloat(value) <= 0) { isValid = false; message = 'Price must be > 0'; }
    }

    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) feedback.textContent = message;
    }

    return isValid;
}

// SA ID Luhn validation
function validateSAID(id) {
    if (id.length !== 13) return false;
    if (!/^\d{13}$/.test(id)) return false;

    let sum = 0;
    for (let i = 0; i < 12; i++) {
        let digit = parseInt(id[i]);
        if (i % 2 === 1) {
            digit *= 2;
            if (digit > 9) digit -= 9;
        }
        sum += digit;
    }
    return (10 - (sum % 10)) % 10 === parseInt(id[12]);
}

// Character counter
document.addEventListener('DOMContentLoaded', function() {
    const descField = document.getElementById('description');
    const charCount = document.getElementById('charCount');

    if (descField && charCount) {
        descField.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            charCount.classList.toggle('text-warning', count > 1800);
            charCount.classList.toggle('text-danger', count > 2000);
        });
    }
});
