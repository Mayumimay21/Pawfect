<?php require_once 'views/layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo BASE_URL . $pet['pet_image']; ?>" class="img-fluid rounded shadow" alt="<?php echo $pet['name']; ?>">
        </div>
        <div class="col-md-6">
            <h1 class="fw-bold"><?php echo $pet['name']; ?></h1>
            <div class="mb-4">
                <span class="badge bg-primary fs-6 me-2"><?php echo ucfirst($pet['type']); ?></span>
                <span class="badge bg-secondary fs-6"><?php echo ucfirst($pet['gender']); ?></span>
            </div>
            
            <div class="row mb-4">
                <div class="col-6">
                    <h6><i class="fas fa-birthday-cake text-primary"></i> Age</h6>
                    <p><?php echo $pet['age']; ?> years old</p>
                </div>
                <div class="col-6">
                    <h6><i class="fas fa-tag text-primary"></i> Breed</h6>
                    <p><?php echo $pet['breed']; ?></p>
                </div>
            </div>
            
            <?php if (!empty($pet['description'])): ?>
            <div class="mb-4">
                <h6><i class="fas fa-info-circle text-primary"></i> About <?php echo $pet['name']; ?></h6>
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted" style="white-space: pre-line; line-height: 1.6;"><?php echo htmlspecialchars($pet['description']); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($pet['is_adopted']): ?>
                <div class="alert alert-info">
                    <i class="fas fa-heart"></i> This pet has been adopted and found a loving home!
                </div>
            <?php else: ?>
                <div class="d-grid gap-2">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isset($pet['in_pawket']) && $pet['in_pawket']): ?>
                            <a href="<?php echo BASE_URL; ?>/pawket" class="btn btn-success btn-lg">
                                <i class="fas fa-paw"></i> In Your Pawket
                            </a>
                        <?php else: ?>
                            <button class="btn btn-warning btn-lg adopt-pet" data-id="<?php echo $pet['id']; ?>" data-name="<?php echo $pet['name']; ?>">
                                <i class="fas fa-home"></i> Add to Pawket
                        </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login" class="btn btn-warning btn-lg">
                            Login to Adopt
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/pets" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to All Pets
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Adoption Confirmation Modal -->
<div class="modal fade" id="adoptModal" tabindex="-1" aria-labelledby="adoptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adoptModalLabel">Confirm Adoption</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to add <span id="petName" class="fw-bold"></span> to your pawket?</p>
                <p class="text-muted small">You can review your selection and complete the adoption process in your pawket.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmAdopt">
                    <i class="fas fa-home me-2"></i> Add to Pawket
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const adoptModal = new bootstrap.Modal(document.getElementById('adoptModal'));
    const adoptButtons = document.querySelectorAll('.adopt-pet');
    let currentPetId = null;

    // Function to close the modal
    function closeModal() {
        adoptModal.hide();
    }

    // Add click event listeners to all close buttons
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', closeModal);
    });

    adoptButtons.forEach(button => {
        button.addEventListener('click', function() {
            currentPetId = this.dataset.id;
            const petName = this.dataset.name;
            document.getElementById('petName').textContent = petName;
            adoptModal.show();
        });
    });

    document.getElementById('confirmAdopt').addEventListener('click', function() {
        if (!currentPetId) return;

        const formData = new FormData();
        formData.append('pet_id', currentPetId);

        fetch('<?php echo BASE_URL; ?>/pawket/add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?php echo BASE_URL; ?>/pawket';
            } else {
                alert(data.message || 'Failed to add pet to pawket');
                closeModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the pet to your pawket');
            closeModal();
        });
    });
});
</script>

<?php require_once 'views/layout/footer.php'; ?>
