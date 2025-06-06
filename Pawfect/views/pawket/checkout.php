<?php require_once 'views/layout/header.php'; ?>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg rounded-4 border-0" style="background: #fff3e0;">
                <div class="card-header gradient-bg text-white rounded-top-4" style="background: linear-gradient(135deg, #FF8C00, #FFD700);">
                    <h3 class="mb-0 fw-bold" style="font-family: 'Quicksand', Nunito, sans-serif; letter-spacing: 1px;">Confirm Your Adoption</h3>
                </div>
                <form method="POST" action="<?php echo BASE_URL; ?>/pawket/process-order" id="checkoutForm">
                    <?php foreach ($pawketItems as $pet): ?>
                        <input type="hidden" name="selected_items[]" value="<?php echo $pet['id']; ?>">
                    <?php endforeach; ?>
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Delivery Address</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars(isset($delivery_address['city']) ? $delivery_address['city'] : ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="barangay" class="form-label">Barangay</label>
                                <input type="text" class="form-control" id="barangay" name="barangay" value="<?php echo htmlspecialchars(isset($delivery_address['barangay']) ? $delivery_address['barangay'] : ''); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="street" class="form-label">Street</label>
                            <input type="text" class="form-control" id="street" name="street" value="<?php echo htmlspecialchars(isset($delivery_address['street']) ? $delivery_address['street'] : ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="zipcode" class="form-label">Zipcode</label>
                            <input type="text" class="form-control" id="zipcode" name="zipcode" value="<?php echo htmlspecialchars(isset($delivery_address['zipcode']) ? $delivery_address['zipcode'] : ''); ?>" required>
                        </div>

                        <h5 class="fw-bold mb-3">Payment Method</h5>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="COD" <?php if (empty($user['payment_method']) || $user['payment_method'] === 'COD') echo 'checked'; ?> required>
                            <label class="form-check-label" for="cod">Cash on Delivery (COD)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="GCASH" <?php if (!empty($user['payment_method']) && $user['payment_method'] === 'GCASH') echo 'checked'; ?>>
                            <label class="form-check-label" for="gcash">GCASH</label>
                        </div>

                        <hr class="my-4">
                        <h5 class="fw-bold mb-3">Adoption Summary</h5>
                        <ul class="list-group mb-3">
                            <?php foreach ($pawketItems as $pet): ?>
                                <li class="list-group-item d-flex align-items-center border-0" style="background: #fff8e1;">
                                    <img src="<?php echo BASE_URL.$pet['pet_image']; ?>" alt="<?php echo $pet['name']; ?>" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #FF8C00;">
                                    <div class="flex-grow-1">
                                        <span class="fw-bold"><?php echo $pet['name']; ?></span><br>
                                        <small class="text-muted">Type: <?php echo ucfirst($pet['type']); ?></small>
                                    </div>
                                    <span class="fw-bold text-primary">₱<?php echo number_format($pet['price'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold fs-5">Total:</span>
                            <span class="fw-bold fs-4 text-primary">₱<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-lg text-white fw-bold rounded-3" style="background: linear-gradient(135deg, #FF8C00, #FFD700); font-size: 1.2rem;">
                                <i class="fas fa-paw me-2"></i> Confirm Adoption
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    
    checkoutForm.addEventListener('submit', function(e) {
        const selectedItems = document.querySelectorAll('input[name="selected_items[]"]');
        if (selectedItems.length === 0) {
            e.preventDefault();
            alert('Please select pets to checkout');
            return false;
        }
    });

    // Show error modal if there's an error
    <?php if (isset($_SESSION['error'])): ?>
    var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
    <?php endif; ?>
});
</script>

<?php require_once 'views/layout/footer.php'; ?> 