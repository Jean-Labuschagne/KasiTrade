<?php require_once 'includes/header.php'; ?>
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h4>Your Cart</h4>
            <p class="text-muted">Items are stored in your browser. Checkout is coming soon.</p>
            <div id="cartContents" class="mb-4"></div>
            <a href="checkout.php" class="btn btn-kasitrade">Proceed to Checkout</a>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const cart = JSON.parse(localStorage.getItem('kasitrade_cart') || '[]');
    const container = document.getElementById('cartContents');
    if(cart.length === 0) {
        container.innerHTML = '<p class="text-muted">Your cart is empty.</p>';
        return;
    }
    const list = document.createElement('ul');
    list.className = 'list-group mb-3';
    cart.forEach(id => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.textContent = 'Listing ID: ' + id + ' (details page coming soon)';
        list.appendChild(li);
    });
    container.appendChild(list);
});
</script>
<?php require_once 'includes/footer.php'; ?>
