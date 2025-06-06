<?php
$page_title = 'Manage Products - ' . get_setting('site_name', 'Pawfect Pet Shop');

// Capture current filter parameters for pagination links
$filter_params = http_build_query(array_filter([
    'q' => $searchQuery,
    'is_archived' => $isArchivedFilter
]));

?>

<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Admin Sidebar -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-header gradient-bg text-white">
                    <h6 class="mb-0">Admin Menu</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?php echo BASE_URL; ?>/admin" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/pets" class="list-group-item list-group-item-action">
                        <i class="fas fa-paw"></i> Manage Pets
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/products" class="list-group-item list-group-item-action active">
                        <i class="fas fa-box"></i> Manage Products
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/orders" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-cart"></i> Manage Orders
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/users" class="list-group-item list-group-item-action">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/settings" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold">Manage Products</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i> Add New Product
                </button>
            </div>
            
            <!-- Search and Filter Form -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form method="GET" action="<?php echo BASE_URL; ?>/admin/products">
                        <div class="row g-3 align-items-center">
                            <div class="col-md">
                                <input type="text" name="q" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>">
                            </div>
                            <div class="col-md">
                                <select name="is_archived" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="0" <?php echo (isset($isArchivedFilter) && $isArchivedFilter === '0') ? 'selected' : ''; ?>>Active</option>
                                    <option value="1" <?php echo (isset($isArchivedFilter) && $isArchivedFilter === '1') ? 'selected' : ''; ?>>Archived</option>
                                </select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                                <?php if (!empty($searchQuery) || ($isArchivedFilter !== null && $isArchivedFilter !== '')): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/products" class="btn btn-outline-secondary">Clear Filters</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No products found</h5>
                                            <p class="text-muted">Try adjusting your filters or adding new products.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                    <tr class="<?php echo $product['is_archived'] ? 'text-muted' : ''; ?>">
                                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                                        <td>
                                            <img src="<?php echo BASE_URL . htmlspecialchars($product['product_image'] ?? '/assets/images/default-product.png'); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 50)) . (strlen($product['description'] ?? '') > 50 ? '...' : ''); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars(ucfirst($product['type'])); ?></span>
                                        </td>
                                        <td>₱<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                                        <td>
                                            <?php if ($product['stock_quantity'] === 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
                                                <span class="badge bg-warning"><?php echo htmlspecialchars($product['stock_quantity']); ?> (Low)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?php echo htmlspecialchars($product['stock_quantity']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($product['is_archived']): ?>
                                                <span class="badge bg-secondary">Archived</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php if ($product['is_archived']): ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to restore this product?');">
                                                        <input type="hidden" name="action" value="restore">
                                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to archive this product?');">
                                                        <input type="hidden" name="action" value="archive">
                                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                                            <i class="fas fa-archive"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Admin Product pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php
                                // Generate base URL for pagination links, keeping filters
                                $pagination_base_url = BASE_URL . '/admin/products?';
                                $current_filters = [];
                                if (!empty($searchQuery)) $current_filters['q'] = urlencode($searchQuery);
                                if ($isArchivedFilter !== null && $isArchivedFilter !== '') $current_filters['is_archived'] = urlencode($isArchivedFilter);
                                $filter_string = http_build_query($current_filters);
                            ?>
                            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $pagination_base_url . $filter_string . '&page=' . ($currentPage - 1); ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo $pagination_base_url . $filter_string . '&page=' . $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $pagination_base_url . $filter_string . '&page=' . ($currentPage + 1); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/admin/products">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="type" class="form-select" required>
                                <option value="foods">Pet Foods</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Price (₱)</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="product_image" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/admin/products">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="editProductId">
                    <input type="hidden" name="current_product_image" id="editCurrentImage">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" id="editProductName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="type" id="editProductType" class="form-select" required>
                                <option value="foods">Pet Foods</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Price (₱)</label>
                            <input type="number" name="price" id="editProductPrice" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="editProductStock" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editProductDescription" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="product_image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">Are you sure?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="confirmModalBody">
        <!-- Confirmation message will be injected here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmModalYes">Yes</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <span id="deleteProductName"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteProductForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteProductId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('editProductId').value = product.id;
    document.getElementById('editProductName').value = product.name;
    document.getElementById('editProductType').value = product.type;
    document.getElementById('editProductPrice').value = product.price;
    document.getElementById('editProductStock').value = product.stock_quantity;
    document.getElementById('editProductDescription').value = product.description;
    document.getElementById('editCurrentImage').value = product.product_image;
    
    const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
    editModal.show();
}

function confirmDelete(productId, productName) {
    document.getElementById('deleteProductId').value = productId;
    document.getElementById('deleteProductName').textContent = productName;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

// Confirmation modal logic
function showConfirmModal(message, onConfirm) {
    document.getElementById('confirmModalBody').textContent = message;
    const yesBtn = document.getElementById('confirmModalYes');
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    yesBtn.onclick = function() {
        modal.hide();
        if (typeof onConfirm === 'function') onConfirm();
    };
    modal.show();
}

// Attach confirmation modals to archive/restore forms
document.addEventListener('DOMContentLoaded', function() {
    // Archive forms
    const archiveForms = document.querySelectorAll('form input[name="action"][value="archive"]');
    archiveForms.forEach(input => {
        const form = input.closest('form');
        if (form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                showConfirmModal('Are you sure you want to archive this product?', () => form.submit());
                return false;
            };
        }
    });

    // Restore forms
    const restoreForms = document.querySelectorAll('form input[name="action"][value="restore"]');
    restoreForms.forEach(input => {
        const form = input.closest('form');
        if (form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                showConfirmModal('Are you sure you want to restore this product?', () => form.submit());
                return false;
            };
        }
    });
});
</script>

<?php require_once 'views/layout/footer.php'; ?>
