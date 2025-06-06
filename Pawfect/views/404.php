<?php require_once 'views/layout/header.php'; ?>

<div class="container py-5">
    <div class="text-center">
        <i class="fas fa-exclamation-triangle fa-5x text-muted mb-4"></i>
        <h1 class="display-4 fw-bold">404 - Page Not Found</h1>
        <p class="lead text-muted mb-4">The page you're looking for doesn't exist.</p>
        <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-home"></i> Go Home
        </a>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
