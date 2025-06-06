<?php require_once 'views/layout/header.php'; ?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Happy Endings</h1>
        <p class="lead text-muted">Meet our successfully adopted pets and their loving families</p>
    </div>
    
    <div class="row">
        <?php if (empty($adoptedPets)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                <h3 class="text-muted">No adopted pets yet</h3>
                <p class="text-muted">Be the first to give a pet a loving home!</p>
                <a href="<?php echo BASE_URL; ?>/pets" class="btn btn-primary">
                    <i class="fas fa-heart"></i> View Available Pets
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($adoptedPets as $pet): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card pet-card h-100">
                    <img src="<?php echo BASE_URL . $pet['pet_image']; ?>" class="card-img-top" style="height: 250px; object-fit: cover;" alt="<?php echo $pet['name']; ?>">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo $pet['name']; ?></h5>
                        <p class="card-text">
                            <span class="badge bg-success mb-2">Adopted</span><br>
                            <strong>New Family:</strong> <?php echo $pet['first_name'] . ' ' . $pet['last_name']; ?><br>
                            <small class="text-muted">
                                <?php echo ucfirst($pet['type']); ?> • <?php echo ucfirst($pet['gender']); ?> • <?php echo $pet['age']; ?> years
                            </small>
                        </p>
                        <div class="text-success">
                            <i class="fas fa-heart"></i> Living happily ever after!
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
