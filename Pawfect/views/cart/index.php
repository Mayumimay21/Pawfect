<?php require_once 'views/layout/header.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Shopping Cart</h1>
    
    <?php if (empty($items)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">Your cart is empty</h3>
            <p class="text-muted">Add some products to get started!</p>
            <a href="<?php echo BASE_URL; ?>/products" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Shop Now
            </a>
        </div>
    <?php else: ?>
        <form id="checkoutForm" method="POST" action="<?php echo BASE_URL; ?>/cart/checkout">
            <div class="row">
                <div class="col-md-8">
                    <?php foreach ($items as $item): ?>
                    <div class="card mb-3">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="<?php echo BASE_URL . $item['product_image']; ?>" class="img-fluid rounded-start h-100" style="object-fit: cover;" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="form-check">
                                            <input class="form-check-input product-checkbox" type="checkbox" 
                                                   name="selected_items[]" 
                                                   value="<?php echo $item['product_id']; ?>"
                                                   data-price="<?php echo $item['price']; ?>"
                                                   data-quantity="<?php echo $item['quantity']; ?>">
                                            <label class="form-check-label">
                                                <h5 class="card-title mb-0"><?php echo $item['name']; ?></h5>
                                            </label>
                                        </div>
                                        <form method="POST" action="<?php echo BASE_URL; ?>/cart/remove" class="d-inline remove-item-form">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <p class="card-text">
                                        <strong class="text-primary">₱<?php echo number_format($item['price'], 2); ?></strong> each<br>
                                        <small class="text-muted">Stock: <?php echo $item['stock_quantity']; ?> available</small>
                                    </p>
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <form method="POST" action="<?php echo BASE_URL; ?>/cart/update" class="d-flex align-items-center">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <label class="me-2">Qty:</label>
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                                       class="form-control me-2" style="width: 80px;"
                                                       onchange="updateItemTotal(this)">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                            </form>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <strong>Subtotal: ₱<span class="item-total"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></span></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">
                                Select All Items
                            </label>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="removeSelected">
                            <i class="fas fa-trash"></i> Remove Selected
                        </button>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Selected Items Total:</span>
                                <strong class="text-primary fs-4">₱<span id="selectedTotal">0.00</span></strong>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="checkoutBtn" disabled>
                                    <i class="fas fa-credit-card"></i> Checkout Selected Items
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- Remove Item Confirmation Modal -->
<div class="modal fade" id="removeItemModal" tabindex="-1" aria-labelledby="removeItemModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="removeItemModalLabel">Confirm Removal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to remove this item from your cart?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmRemoveBtn">Remove</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const selectedTotal = document.getElementById('selectedTotal');
    const removeSelectedBtn = document.getElementById('removeSelected');

    // Function to update the total based on selected items
    function updateTotal() {
        let total = 0;
        let selectedCount = 0;
        
        productCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const price = parseFloat(checkbox.dataset.price);
                const quantity = parseInt(checkbox.dataset.quantity);
                total += price * quantity;
                selectedCount++;
            }
        });

        selectedTotal.textContent = total.toFixed(2);
        checkoutBtn.disabled = selectedCount === 0;
    }

    // Select All functionality
    selectAllCheckbox.addEventListener('change', function() {
        productCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateTotal();
    });

    // Individual checkbox change
    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Update select all checkbox state
            const allChecked = Array.from(productCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            updateTotal();
        });
    });

    // Remove selected items
    removeSelectedBtn.addEventListener('click', function() {
        const selectedItems = Array.from(productCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (selectedItems.length === 0) {
            alert('Please select items to remove');
            return;
        }

        if (confirm('Are you sure you want to remove the selected items?')) {
            selectedItems.forEach(productId => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo BASE_URL; ?>/cart/remove';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'product_id';
                input.value = productId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            });
        }
    });

    // Update item total when quantity changes
    window.updateItemTotal = function(input) {
        const card = input.closest('.card-body');
        const price = parseFloat(card.querySelector('.text-primary').textContent.replace('₱', '').replace(',', ''));
        const quantity = parseInt(input.value);
        const totalElement = card.querySelector('.item-total');
        totalElement.textContent = (price * quantity).toFixed(2);
        
        // Update the quantity in the checkbox dataset
        const checkbox = card.querySelector('.product-checkbox');
        checkbox.dataset.quantity = quantity;
        
        // Update the total if this item is selected
        if (checkbox.checked) {
            updateTotal();
        }
    };

    // Initial total update
    updateTotal();
});
</script>

<?php require_once 'views/layout/footer.php'; ?>
