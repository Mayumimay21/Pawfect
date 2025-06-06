<?php
$page_title = 'Pets - ' . get_setting('site_name', 'Pawfect Pet Shop');

// Capture current filter parameters for pagination links
$filter_params = http_build_query(array_filter([
    'query' => $filterQuery ?? '',
    'type' => $filterType ?? ''
]));
?>

<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid py-5">
    <div class="row">
        <!-- Sidebar with Filters -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #FF8C00; color: white;">
                    <h5 class="mb-0">Filter Pets</h5>
                </div>
                <div class="card-body" style="background-color: #fff8e1;">
                    <form method="GET" action="<?php echo BASE_URL; ?>/pets">
                        <div class="mb-3">
                            <label class="form-label" style="color: #FF8C00;">Search</label>
                            <input type="text" name="query" class="form-control" placeholder="Search pets..." style="border-color: #FFD700;" value="<?php echo htmlspecialchars($filterQuery ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #FF8C00;">Type</label>
                            <select name="type" class="form-select" style="border-color: #FFD700; color: #333;">
                                <option value="">All Types</option>
                                <option value="dogs" <?php echo ($filterType === 'dogs') ? 'selected' : ''; ?>>Dogs</option>
                                <option value="cats" <?php echo ($filterType === 'cats') ? 'selected' : ''; ?>>Cats</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #FF8C00;">Gender</label>
                            <select name="gender" class="form-select" style="border-color: #FFD700; color: #333;">
                                <option value="">All Genders</option>
                                <option value="male" <?php echo ($filterGender === 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($filterGender === 'female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #FF8C00;">Age Range</label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="number" name="minAge" class="form-control" placeholder="Min Age" style="border-color: #FFD700;" value="<?php echo htmlspecialchars($filterMinAge ?? ''); ?>">
                                </div>
                                <div class="col">
                                    <input type="number" name="maxAge" class="form-control" placeholder="Max Age" style="border-color: #FFD700;" value="<?php echo htmlspecialchars($filterMaxAge ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" style="background-color: #FF8C00; border-color: #FF8C00;">Apply Filters</button>
                        <?php if (!empty($filterQuery) || !empty($filterType) || !empty($filterGender) || !empty($filterMinAge) || !empty($filterMaxAge)): ?>
                            <a href="<?php echo BASE_URL; ?>/pets" class="btn btn-outline-secondary w-100 mt-2" style="background-color: #FFD700; color: #333; border-color: #FFD700;">Reset Filters</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="text-center mb-5">
                <h1 class="fw-bold" style="color: #FF8C00; font-family: 'Quicksand', Nunito, sans-serif;">Available Pets</h1>
                <p class="lead text-muted">Find your perfect companion from our selection of adorable pets</p>
            </div>

            <!-- Pets Grid -->
            <div class="row" id="petsGrid">
                <?php if (empty($pets)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-paw fa-4x text-muted mb-3"></i>
                        <h3 class="text-muted">No pets found</h3>
                        <p class="text-muted">Try adjusting your filters</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pets as $pet): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card pet-card h-100 shadow rounded-4 border-0" style="background: #fff8e1;">
                                <img src="<?php echo BASE_URL . htmlspecialchars($pet['pet_image'] ?? '/assets/images/default-pet.png'); ?>" class="card-img-top rounded-circle mx-auto mt-3"
                                    style="height: 180px; width: 180px; object-fit: cover; border: 4px solid #FFD700; background: #fff;"
                                    alt="<?php echo htmlspecialchars($pet['name']); ?>">
                                <div class="card-body text-center">
                                    <h6 class="card-title fw-bold" style="color: #FF8C00; letter-spacing: 1px;">
                                        🐾 <?php echo htmlspecialchars($pet['name']); ?>
                                    </h6>
                                    <p class="card-text">
                                        <span class="badge bg-secondary mb-2" style="background: #FFD700; color: #fff; font-size: 0.9rem;">
                                            <?php echo htmlspecialchars(ucfirst($pet['type'])); ?>
                                        </span><br>
                                        <small class="text-muted">
                                            <i class="fas fa-birthday-cake"></i> <?php echo htmlspecialchars($pet['age']); ?> <?php echo htmlspecialchars($pet['age'] === 1 ? 'year' : 'years'); ?>
                                            <?php if (!empty($pet['breed'])): ?>
                                                <?php if (isset($pet['type']) && $pet['type'] === 'cat'): ?>
                                                    <i class="fas fa-fish ms-2"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-bone ms-2"></i>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($pet['breed']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </p>
                                    <div class="d-grid gap-2">
                                        <a href="<?php echo BASE_URL; ?>/pet/<?php echo htmlspecialchars($pet['id']); ?>" class="btn btn-outline-secondary rounded-pill fw-bold" style="background-color: #FFD700; color: #333; border-color: #FFD700;">
                                            <i class="fas fa-paw"></i> Meet <?php echo htmlspecialchars($pet['name']); ?>
                                        </a>
                                        <?php if (isset($pet['status']) && $pet['status'] === 'available'): ?>
                                            <?php if (isset($_SESSION['user_id'])): // Check if user is logged in 
                                            ?>
                                                <form method="POST" action="<?php echo BASE_URL; ?>/adopt" class="d-grid">
                                                    <input type="hidden" name="pet_id" value="<?php echo htmlspecialchars($pet['id']); ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill fw-bold" style="background-color: #FF8C00; border-color: #FF8C00;">
                                                        <i class="fas fa-heart"></i> Adopt Me!
                                                    </button>
                                                </form>
                                            <?php else: // User is not logged in 
                                            ?>
                                                <a href="<?php echo BASE_URL; ?>/login" class="btn btn-primary btn-sm rounded-pill fw-bold" style="background-color: #FF8C00; border-color: #FF8C00;">
                                                    <i class="fas fa-sign-in-alt"></i> Login to Adopt
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif (isset($pet['status'])): // Pet is not available, but status is set 
                                        ?>
                                            <button class="btn btn-secondary btn-sm rounded-pill fw-bold" disabled style="background-color: #FFD700; color: #333; border-color: #FFD700;">
                                                <?php echo htmlspecialchars(ucfirst($pet['status'])); // Display actual status if not available 
                                                ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <nav aria-label="Pet pagination" class="mt-4">
                <ul class="pagination justify-content-center" id="pagination">
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                        <?php
                        // Generate base URL for pagination links, keeping filters
                        $pagination_base_url = BASE_URL . '/pets?';
                        $current_filters = [];
                        if (!empty($filterQuery)) $current_filters['query'] = urlencode($filterQuery);
                        if (!empty($filterType)) $current_filters['type'] = urlencode($filterType);
                        $filter_string = http_build_query($current_filters);
                        ?>
                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $pagination_base_url . $filter_string . '&page=' . ($currentPage - 1); ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo (isset($currentPage) && $i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $pagination_base_url . $filter_string . '&page=' . $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo (isset($currentPage) && $currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $pagination_base_url . $filter_string . '&page=' . ($currentPage + 1); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<style>
    .pet-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .pet-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .pet-image-container {
        position: relative;
        overflow: hidden;
    }

    .pet-card:hover .card-img-top {
        transform: scale(1.05);
        transition: transform 0.3s ease;
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .pagination .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: none;
        color: #FF8C00;
    }

    .pagination .page-link:hover {
        background: #FF8C00;
        color: white;
    }

    .pagination .page-item.active .page-link {
        background: #FF8C00;
        border-color: #FF8C00;
    }
</style>

<?php require_once 'views/layout/footer.php'; ?>