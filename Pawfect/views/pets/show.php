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
                        <button onclick="adoptPet(<?php echo $pet['id']; ?>, '<?php echo $pet['name']; ?>')" class="btn btn-warning btn-lg">
                            <i class="fas fa-home"></i> Adopt <?php echo $pet['name']; ?>
                        </button>
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

<?php require_once 'views/layout/footer.php'; ?>
