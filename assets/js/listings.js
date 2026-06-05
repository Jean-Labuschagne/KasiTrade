/**
 * KasiTrade Dynamic Listings
 * AJAX loading, offline caching, cart management
 */

// Cart functionality
function addToCart(listingId) {
    let cart = JSON.parse(localStorage.getItem('kasitrade_cart') || '[]');

    if (!cart.includes(listingId)) {
        cart.push(listingId);
        localStorage.setItem('kasitrade_cart', JSON.stringify(cart));
        updateCartCount();
        showToast('Item added to cart!', 'success');
    } else {
        showToast('Item already in cart', 'info');
    }
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('kasitrade_cart') || '[]');
    const badges = document.querySelectorAll('.cart-count');
    badges.forEach(badge => {
        badge.textContent = cart.length;
        badge.classList.toggle('d-none', cart.length === 0);
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
    toast.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

// Update cart on load
document.addEventListener('DOMContentLoaded', updateCartCount);

// Image preview for uploads
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('images');
    const previewContainer = document.getElementById('imagePreview');

    if (imageInput && previewContainer) {
        imageInput.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            Array.from(this.files).slice(0, 5).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    previewContainer.innerHTML += `
                        <div class="col-4">
                            <img src="${e.target.result}" class="img-thumbnail" style="height: 80px; object-fit: cover;">
                        </div>`;
                };
                reader.readAsDataURL(file);
            });
        });
    }
});

// Confirm actions
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) e.preventDefault();
        });
    });
});
