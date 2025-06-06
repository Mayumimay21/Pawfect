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
                    <button class="btn btn-primary btn-lg add-to-cart" data-id="<?php echo $product['id']; ?>" data-name="<?php echo $product['name']; ?>">
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
                <a href="<?php echo BASE_URL; ?>/pawducts" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Pawducts
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Add to Cart Confirmation Modal -->
<div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addToCartModalLabel">Add to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to add <span id="productName" class="fw-bold"></span> to your cart?</p>
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity:</label>
                    <input type="number" class="form-control" id="quantity" min="1" max="<?php echo $product['stock_quantity']; ?>" value="1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddToCart">
                    <i class="fas fa-cart-plus me-2"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Added to Cart Success Modal -->
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
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const addToCartModal = document.getElementById('addToCartModal');
    const addedToCartModal = document.getElementById('addedToCartModal');
    let currentProductId = null;

    // Initialize Bootstrap modals
    const bsAddToCartModal = new bootstrap.Modal(addToCartModal);
    const bsAddedToCartModal = new bootstrap.Modal(addedToCartModal);

    // Handle Add to Cart button clicks
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            currentProductId = this.dataset.id;
            const productName = this.dataset.name;
            document.getElementById('productName').textContent = productName;
            document.getElementById('quantity').value = 1;
            bsAddToCartModal.show();
        });
    });

    // Handle modal close buttons
    document.querySelectorAll('.btn-close, [data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal === addToCartModal) {
                bsAddToCartModal.hide();
            } else if (modal === addedToCartModal) {
                bsAddedToCartModal.hide();
            }
        });
    });

    // Handle Continue Shopping button
    document.querySelector('#addedToCartModal .btn-secondary').addEventListener('click', function() {
        bsAddedToCartModal.hide();
    });

    // Handle Confirm Add to Cart button
    document.getElementById('confirmAddToCart').addEventListener('click', function() {
        if (!currentProductId) return;

        const quantity = document.getElementById('quantity').value;
        const formData = new FormData();
        formData.append('product_id', currentProductId);
        formData.append('quantity', quantity);

        fetch('<?php echo BASE_URL; ?>/cart/add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
                .then(data => {
                    if (data.success) {
                bsAddToCartModal.hide();
                bsAddedToCartModal.show();
                    } else {
                alert(data.message || 'Failed to add item to cart');
                bsAddToCartModal.hide();
                    }
                })
                .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the item to your cart');
            bsAddToCartModal.hide();
                });
         });
});
</script>

<?php require_once 'views/layout/footer.php'; ?>
