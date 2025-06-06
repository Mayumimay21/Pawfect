<?php require_once 'views/layout/header.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Your Cart</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">Your cart is empty</h3>
            <p class="text-muted">Add some products to get started!</p>
            <a href="<?php echo BASE_URL; ?>/pawducts" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Browse Products
            </a>
        </div>
    <?php else: ?>
        <form id="checkoutForm" method="POST" action="<?php echo BASE_URL; ?>/cart/checkout">
            <div class="row">
                <div class="col-md-8">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="card mb-3 <?php echo $item['stock_quantity'] == 0 ? 'bg-light' : ''; ?>">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="<?php echo BASE_URL . $item['product_image']; ?>" class="img-fluid rounded-start h-100" style="object-fit: cover;" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <?php if ($item['stock_quantity'] == 0): ?>
                                        <div class="alert alert-warning mb-3">
                                            <i class="fas fa-exclamation-triangle"></i> Uh-oh! 🐶 '<?php echo $item['name']; ?>' has run out of stock, seems like it's a fan favorite! 
                                            <a href="<?php echo BASE_URL; ?>/pawducts" class="alert-link">You can check back later or explore other paw-some picks</a> for your furry friend
                                        </div>
                                    <?php elseif ($item['stock_quantity'] <= 5): ?>
                                        <div class="alert alert-warning mb-3">
                                            <i class="fas fa-exclamation-triangle"></i> Heads up! 🐾 '<?php echo $item['name']; ?>' is almost gone, better fetch it fast before it disappears from the shelves!
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="form-check">
                                            <input class="form-check-input product-checkbox" type="checkbox" 
                                                   name="selected_items[]" 
                                                   value="<?php echo $item['product_id']; ?>"
                                                   data-price="<?php echo $item['price'] * $item['quantity']; ?>"
                                                   <?php echo $item['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                                            <label class="form-check-label">
                                                <h5 class="card-title mb-0"><?php echo $item['name']; ?></h5>
                                            </label>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" 
                                                data-id="<?php echo $item['product_id']; ?>"
                                                data-name="<?php echo $item['name']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                    </div>
                                    <p class="card-text">
                                        <strong class="text-primary">₱<?php echo number_format($item['price'], 2); ?></strong><br>
                                        <small class="text-muted">Type: <?php echo ucfirst($item['type']); ?></small>
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <label for="quantity_<?php echo $item['product_id']; ?>" class="me-2">Quantity:</label>
                                        <input type="number" class="form-control form-control-sm quantity-input" 
                                               id="quantity_<?php echo $item['product_id']; ?>" 
                                               name="quantities[<?php echo $item['product_id']; ?>]" 
                                               value="<?php echo ($item['quantity'] > $item['stock_quantity'] && $item['stock_quantity'] > 0) ? 1 : $item['quantity']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['stock_quantity']; ?>"
                                               style="width: 80px;"
                                               <?php echo $item['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
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
                                <button type="submit" class="btn btn-primary btn-lg" id="checkoutBtn">
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

<!-- Remove Single Item Modal -->
<div class="modal fade" id="removeItemModal" tabindex="-1" aria-labelledby="removeItemModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
                <h5 class="modal-title" id="removeItemModalLabel">Remove Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
                Are you sure you want to remove <span id="productName" class="fw-bold"></span> from your cart?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?php echo BASE_URL; ?>/cart/remove" id="removeItemForm">
                    <input type="hidden" name="product_id" id="removeProductId">
                    <button type="submit" class="btn btn-danger">Remove</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Remove Selected Items Modal -->
<div class="modal fade" id="removeSelectedModal" tabindex="-1" aria-labelledby="removeSelectedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeSelectedModalLabel">Remove Selected Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to remove the selected products from your cart?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveSelected">Remove</button>
            </div>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="messageText"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="messageModalOk">OK</button>
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
    const checkoutForm = document.getElementById('checkoutForm');
    const removeItemModal = new bootstrap.Modal(document.getElementById('removeItemModal'));
    const removeSelectedModal = new bootstrap.Modal(document.getElementById('removeSelectedModal'));
    const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
    const messageText = document.getElementById('messageText');
    const messageModalOk = document.getElementById('messageModalOk');
    const quantityInputs = document.querySelectorAll('.quantity-input');

    // Function to show message modal
    function showMessage(message) {
        messageText.textContent = message;
        messageModal.show();
    }

    // Handle message modal OK button
    messageModalOk.addEventListener('click', function() {
        messageModal.hide();
    });

    // Handle message modal close button
    document.querySelector('#messageModal .btn-close').addEventListener('click', function() {
        messageModal.hide();
    });

    // Handle message modal backdrop click
    document.getElementById('messageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            messageModal.hide();
        }
    });

    // Function to update the total based on selected items
    function updateTotal() {
        let total = 0;
        let selectedCount = 0;
        
        productCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const productId = checkbox.value;
                const quantityInput = document.querySelector(`input[name="quantities[${productId}]"]`);
                const quantity = parseInt(quantityInput.value);
                const price = parseFloat(checkbox.closest('.card').querySelector('.text-primary').textContent.replace('₱', '').replace(',', ''));
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
            if (!checkbox.disabled) {  // Only check enabled checkboxes
                checkbox.checked = this.checked;
            }
        });
        updateTotal();
    });

    // Individual checkbox change
    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Update select all checkbox state
            const enabledCheckboxes = Array.from(productCheckboxes).filter(cb => !cb.disabled);
            const allChecked = enabledCheckboxes.length > 0 && enabledCheckboxes.every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            updateTotal();
        });
    });

    // Quantity input change
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.id.split('_')[1];
            const maxQuantity = parseInt(this.getAttribute('max'));
            let quantity = parseInt(this.value);
            
            // Validate quantity
            if (quantity < 1) {
                quantity = 1;
                this.value = quantity;
            } else if (quantity > maxQuantity) {
                if (maxQuantity > 0) {
                    showMessage(`Only ${maxQuantity} items available in stock. Quantity adjusted to 1.`);
                    quantity = 1;
                    this.value = quantity;
                } else {
                    showMessage('This product is out of stock');
                    this.value = this.defaultValue;
                    return;
                }
            }
            
            // Update quantity in cart
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('<?php echo BASE_URL; ?>/cart/update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showMessage(data.message || 'Failed to update quantity');
                    // Reset to previous value if update failed
                    this.value = this.defaultValue;
                } else {
                    // Update total if the item is selected
                    const checkbox = document.querySelector(`input[name="selected_items[]"][value="${productId}"]`);
                    if (checkbox && checkbox.checked) {
                        updateTotal();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while updating quantity');
                // Reset to previous value if update failed
                this.value = this.defaultValue;
            });
        });
    });

    // Remove selected items
    removeSelectedBtn.addEventListener('click', function() {
        const selectedItems = Array.from(productCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (selectedItems.length === 0) {
            showMessage('Please select items to remove');
            return;
        }

        removeSelectedModal.show();
    });

    // Confirm remove selected
    document.getElementById('confirmRemoveSelected').addEventListener('click', function() {
        const selectedItems = Array.from(productCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
            
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo BASE_URL; ?>/cart/remove-selected';
                
        selectedItems.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    });

    // Remove single item
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            const productName = this.dataset.name;
            
            document.getElementById('productName').textContent = productName;
            document.getElementById('removeProductId').value = productId;
            removeItemModal.show();
        });
    });

    // Form submission validation
    checkoutForm.addEventListener('submit', function(e) {
        const selectedItems = Array.from(productCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
            
        if (selectedItems.length === 0) {
            e.preventDefault();
            showMessage('Please select at least one item to checkout');
            return;
        }

        // Remove any existing hidden inputs to avoid duplicates
        const existingInputs = this.querySelectorAll('input[name="selected_items[]"]');
        existingInputs.forEach(input => input.remove());
        
        // Add selected items to the form
        selectedItems.forEach(itemId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_items[]';
            input.value = itemId;
            this.appendChild(input);
        });
    });

    // Initial total calculation
    updateTotal();
});
</script>

<?php require_once 'views/layout/footer.php'; ?>
