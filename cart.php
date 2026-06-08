<?php
$pageTitle = 'Cart';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Your Cart</h4>
                    <p class="text-muted mb-0">Saved in your browser, with product details loaded from KasiTrade.</p>
                </div>
                <a href="browse.php" class="btn btn-outline-kasitrade">Continue Shopping</a>
            </div>

            <div id="cartContents" class="mb-4"></div>

            <div id="cartSummary" class="card shadow-sm d-none">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="text-muted small">Cart total</div>
                        <div class="h4 mb-0" id="cartTotal">R0.00</div>
                    </div>
                    <a href="checkout.php" class="btn btn-kasitrade btn-lg">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('cartContents');
    const summary = document.getElementById('cartSummary');
    const totalNode = document.getElementById('cartTotal');
    const currency = new Intl.NumberFormat('en-ZA', { style: 'currency', currency: 'ZAR' });

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getCartIds() {
        return JSON.parse(localStorage.getItem('kasitrade_cart') || '[]')
            .map(id => parseInt(id, 10))
            .filter(Number.isInteger);
    }

    function saveCart(ids) {
        localStorage.setItem('kasitrade_cart', JSON.stringify(ids));
        updateCartCount();
    }

    function removeFromCart(listingId) {
        const updated = getCartIds().filter(id => id !== listingId);
        saveCart(updated);
        renderCart();
    }

    function renderEmpty() {
        container.innerHTML = `
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-cart-x display-1 text-muted"></i>
                    <h5 class="mt-3 mb-2">Your cart is empty</h5>
                    <p class="text-muted mb-4">Add a few listings to compare details and move toward checkout.</p>
                    <a href="browse.php" class="btn btn-kasitrade">Browse Listings</a>
                </div>
            </div>`;
        summary.classList.add('d-none');
        totalNode.textContent = currency.format(0);
    }

    async function renderCart() {
        const cartIds = getCartIds();

        if (cartIds.length === 0) {
            renderEmpty();
            return;
        }

        container.innerHTML = `
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="spinner-border text-kasitrade" role="status" aria-hidden="true"></div>
                    <div>
                        <div class="fw-semibold">Loading cart items</div>
                        <div class="text-muted small">Fetching product details from the database...</div>
                    </div>
                </div>
            </div>`;

        try {
            const response = await fetch(`cart-data.php?ids=${encodeURIComponent(cartIds.join(','))}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error('Failed to load cart items');
            }

            const payload = await response.json();
            const itemsById = new Map((payload.items || []).map(item => [parseInt(item.listing_id, 10), item]));
            const orderedItems = cartIds.map(id => itemsById.get(id)).filter(Boolean);

            if (orderedItems.length === 0) {
                renderEmpty();
                return;
            }

            const total = orderedItems.reduce((sum, item) => sum + parseFloat(item.price || 0), 0);

            container.innerHTML = orderedItems.map(item => `
                <div class="card shadow-sm mb-3">
                    <div class="row g-0">
                        <div class="col-md-3">
                            <img src="${escapeHtml(item.image_url)}" class="img-fluid h-100 w-100 rounded-start" alt="${escapeHtml(item.title)}" style="object-fit: cover; min-height: 180px;" onerror="this.src='assets/images/no-image.jpg'">
                        </div>
                        <div class="col-md-9">
                            <div class="card-body h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="badge bg-${item.condition_status === 'new' ? 'success' : 'warning'}">${escapeHtml(item.condition_label)}</span>
                                            <span class="badge bg-light text-dark">${escapeHtml(item.category_name)}</span>
                                        </div>
                                        <h5 class="card-title mb-1">${escapeHtml(item.title)}</h5>
                                        <div class="text-muted small mb-2">
                                            <i class="bi bi-shop"></i> ${escapeHtml(item.seller_name)}
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-geo-alt"></i> ${escapeHtml(item.township)}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="h4 text-kasitrade mb-1">${currency.format(Number(item.price))}</div>
                                        <button class="btn btn-sm btn-outline-danger" type="button" data-remove-id="${item.listing_id}">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <p class="card-text text-muted small mb-0 mt-auto">${escapeHtml(item.description)}</p>
                            </div>
                        </div>
                    </div>
                </div>`).join('');

            summary.classList.remove('d-none');
            totalNode.textContent = currency.format(total);

            container.querySelectorAll('[data-remove-id]').forEach(button => {
                button.addEventListener('click', function() {
                    removeFromCart(parseInt(this.dataset.removeId, 10));
                });
            });
        } catch (error) {
            container.innerHTML = `
                <div class="alert alert-warning shadow-sm">
                    We could not load product details right now. Please refresh the page or try again.
                </div>`;
            summary.classList.add('d-none');
        }
    }

    renderCart();
});
</script>

<?php require_once 'includes/footer.php'; ?>
