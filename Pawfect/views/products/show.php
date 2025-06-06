<?php require_once 'views/layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo BASE_URL . $product['product_image']; ?>" class="img-fluid rounded shadow" alt="<?php echo $product['name']; ?>">
        </div>
        <div class="col-md-6">
            <h1 class="fw-bold"><?php echo $product['name']; ?></h1>
            <div class="mb-3">
                <span class="badge bg-secondary fs-6"><?php echo ucfirst($product['type']); ?></span>
            </div>
            
            <div class="row mb-4">
                <div class="col-6">
                    <h6><i class="fas fa-money-bill-wave text-primary"></i> Price</h6>
                    <h3 class="text-primary">₱<?php echo number_format($product['price'], 2); ?></h3>
                </div>
                <div class="col-6">
                    <p class="text-muted">
                        Stock: <?php echo $product['stock_quantity']; ?> available
                        <?php if ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
                            <span class="text-warning">(Low Stock)</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <div class="mb-4">
                <h5><i class="fas fa-info-circle text-primary"></i> Description</h5>
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted" style="white-space: pre-line; line-height: 1.6;"><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <?php if (isLoggedIn() && $product['stock_quantity'] > 0): ?>
                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary btn-lg">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/login" class="btn btn-primary btn-lg">
                        Login to Purchase
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>
                        Out of Stock
                    </button>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/products" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>

<!-- Added to Cart Modal -->
<div class="modal fade" id="addedToCartModal" tabindex="-1" aria-labelledby="addedToCartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addedToCartModalLabel">Item Added to Cart!</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
        <p>The product has been successfully added to your shopping cart.</p>
      </div>
      <div class="modal-footer justify-content-center">
        <a href="<?php echo BASE_URL; ?>/cart" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> View Cart</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Shopping</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality
    const addToCartButton = document.querySelector('.btn-primary.btn-lg'); // Assuming this is the Add to Cart button
    if (addToCartButton && typeof addToCartButton.onclick === 'function') {
        // Wrap the existing onclick function to trigger the modal
        const originalOnClick = addToCartButton.onclick;
        addToCartButton.onclick = function() {
            originalOnClick.apply(this, arguments);
            // Assuming the original onclick triggers the backend call and on success, you'll handle the modal there
            // For now, this is just adding the modal HTML. The JS to trigger it needs to be integrated with the backend response.
        };
    } else if (addToCartButton) {
        // If not using onclick, find the form or use event delegation
         addToCartButton.addEventListener('click', function(event) {
             // Prevent default form submission if it's a submit button within a form
             event.preventDefault(); // Prevent default form submission if it's a submit button
             
             const productId = this.dataset.productId; // Assuming product ID is stored in a data attribute
             // You would typically get the product ID from the element clicked or a surrounding form
             // For this example, I'll assume the product ID is available.
             const product_id = <?php echo $product['id']; ?>; // Get product ID from PHP

             // Call the global addToCart function from main.js
             // main.js will handle the fetch request and return a promise
             window.PawfectApp.addToCart(product_id)
                .then(data => {
                    if (data.success) {
                        // Show the specific added to cart modal
                        const addedToCartModal = new bootstrap.Modal(document.getElementById('addedToCartModal'));
                        addedToCartModal.show();
                    } else {
                        // Show generic alert modal for errors
                        window.showAlertModal(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error("Error adding to cart:", error);
                    // Show generic alert modal for fetch errors
                    window.showAlertModal("An error occurred while adding to cart.", 'danger');
                });
         });
    }
});
</script>
