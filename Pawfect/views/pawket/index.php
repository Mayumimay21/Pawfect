<?php require_once 'views/layout/header.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Your Pawket</h1>
    
    <?php if (empty($pawketItems)): ?>
        <div class="text-center py-5">
            <i class="fas fa-paw fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">Your pawket is empty</h3>
            <p class="text-muted">Add some pets to get started!</p>
            <a href="<?php echo BASE_URL; ?>/pets" class="btn btn-primary">
                <i class="fas fa-paw"></i> Browse Pets
            </a>
        </div>
    <?php else: ?>
        <form id="checkoutForm" method="POST" action="<?php echo BASE_URL; ?>/pawket/checkout">
            <div class="row">
                <div class="col-md-8">
                    <?php foreach ($pawketItems as $item): ?>
                    <div class="card mb-3">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="<?php echo BASE_URL . $item['pet_image']; ?>" class="img-fluid rounded-start h-100" style="object-fit: cover;" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <?php if (isset($item['is_adopted']) && $item['is_adopted']): ?>
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-home"></i> Looks like <?php echo $item['name']; ?> found their furever family! 🏡 
                                            <a href="<?php echo BASE_URL; ?>/adopted-pets" class="alert-link">Check out Adopted Pets Page</a> 
                                            or <a href="<?php echo BASE_URL; ?>/pets" class="alert-link">sniff out a new buddy</a> who's pawfect for you!
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="form-check">
                                            <input class="form-check-input product-checkbox" type="checkbox" 
                                                   name="selected_items[]" 
                                                   value="<?php echo $item['id']; ?>"
                                                   data-price="<?php echo $item['price']; ?>"
                                                   <?php echo (isset($item['is_adopted']) && $item['is_adopted']) ? 'disabled' : ''; ?>
                                                   required>
                                            <label class="form-check-label <?php echo (isset($item['is_adopted']) && $item['is_adopted']) ? 'text-muted' : ''; ?>">
                                                <h5 class="card-title mb-0"><?php echo $item['name']; ?></h5>
                                            </label>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" 
                                                data-id="<?php echo $item['id']; ?>"
                                                data-name="<?php echo $item['name']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <p class="card-text">
                                        <strong class="text-primary">₱<?php echo number_format($item['price'], 2); ?></strong><br>
                                        <small class="text-muted">Type: <?php echo ucfirst($item['type']); ?></small>
                                    </p>
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

<!-- Remove Single Item Modal -->
<div class="modal fade" id="removeItemModal" tabindex="-1" aria-labelledby="removeItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeItemModalLabel">Remove Pet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to remove <span id="petName" class="fw-bold"></span> from your pawket?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?php echo BASE_URL; ?>/pawket/remove" id="removeItemForm">
                    <input type="hidden" name="pet_id" id="removePetId">
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
                <h5 class="modal-title" id="removeSelectedModalLabel">Remove Selected Pets</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to remove the selected pets from your pawket?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveSelected">Remove</button>
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

    // Function to update the total based on selected items
    function updateTotal() {
        let total = 0;
        let selectedCount = 0;
        
        productCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const price = parseFloat(checkbox.dataset.price);
                total += price;
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

    // Form submission validation
    checkoutForm.addEventListener('submit', function(e) {
        const selectedItems = Array.from(productCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (selectedItems.length === 0) {
            e.preventDefault();
            alert('Please select at least one pet to checkout');
            return false;
        }

        // Ensure all selected items are included in the form
        selectedItems.forEach(itemId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_items[]';
            input.value = itemId;
            this.appendChild(input);
        });
    });

    // Remove single item
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const petId = this.dataset.id;
            const petName = this.dataset.name;
            document.getElementById('removePetId').value = petId;
            document.getElementById('petName').textContent = petName;
            removeItemModal.show();
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

        removeSelectedModal.show();
    });

    // Confirm remove selected
    document.getElementById('confirmRemoveSelected').addEventListener('click', function() {
        const selectedItems = Array.from(productCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        selectedItems.forEach(petId => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo BASE_URL; ?>/pawket/remove';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'pet_id';
            input.value = petId;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });
    });
});
</script>

<?php require_once 'views/layout/footer.php'; ?> 