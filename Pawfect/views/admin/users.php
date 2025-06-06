<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Admin Sidebar Navigation -->
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
                    <a href="<?php echo BASE_URL; ?>/admin/products" class="list-group-item list-group-item-action">
                        <i class="fas fa-box"></i> Manage Products
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/orders" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-cart"></i> Manage Orders
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/users" class="list-group-item list-group-item-action active">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/settings" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-md-10">
            <h1 class="fw-bold mb-4">Manage Users</h1>

            <!-- Search and Filter Form -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form method="GET" action="<?php echo BASE_URL; ?>/admin/users">
                        <div class="row g-3">
                            <div class="col-md">
                                <input type="text" name="q" class="form-control" placeholder="Search users by Name or Email..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                            </div>
                            <div class="col-md">
                                <select name="role" class="form-select">
                                    <option value="">All Roles</option>
                                    <option value="user" <?php echo (isset($_GET['role']) && $_GET['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                                <?php if (isset($_GET['q']) || isset($_GET['role'])): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/users" class="btn btn-outline-secondary">Clear Filters</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['phone'] ?: 'N/A'; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_banned']): ?>
                                        <span class="badge bg-danger">Banned</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="role">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="role" value="<?php echo $user['role'] === 'admin' ? 'user' : 'admin'; ?>">
                                                        <button type="submit" class="dropdown-item">
                                                            Make <?php echo $user['role'] === 'admin' ? 'User' : 'Admin'; ?>
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <?php if ($user['is_banned']): ?>
                                                    <li>
                                                        <button type="button" class="dropdown-item text-success" onclick="confirmUnban(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                            <i class="fas fa-check"></i> Unban User
                                                        </button>
                                                    </li>
                                                <?php else: ?>
                                                    <li>
                                                        <button type="button" class="dropdown-item text-danger" onclick="confirmBan(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                            <i class="fas fa-ban"></i> Ban User
                                                        </button>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Admin user pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>/admin/users?page=<?php echo $currentPage - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo BASE_URL; ?>/admin/users?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>/admin/users?page=<?php echo $currentPage + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
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

<!-- Ban/Unban Confirmation Modal -->
<div class="modal fade" id="userActionModal" tabindex="-1" aria-labelledby="userActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userActionModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="userActionMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="userActionForm" style="display: inline;">
                    <input type="hidden" name="action" id="userActionType">
                    <input type="hidden" name="user_id" id="userActionId">
                    <button type="submit" class="btn" id="userActionButton">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Modal Functionality -->
<script>
/**
 * Shows confirmation modal for banning a user
 * @param {Object} user - User object containing user details
 */
function confirmBan(user) {
    document.getElementById('userActionModalLabel').textContent = 'Confirm Ban';
    document.getElementById('userActionMessage').innerHTML = `Are you sure you want to ban <strong>${user.first_name} ${user.last_name}</strong>?`;
    document.getElementById('userActionType').value = 'ban';
    document.getElementById('userActionId').value = user.id;
    document.getElementById('userActionButton').className = 'btn btn-danger';
    document.getElementById('userActionButton').textContent = 'Ban User';
    
    const modal = new bootstrap.Modal(document.getElementById('userActionModal'));
    modal.show();
}

/**
 * Shows confirmation modal for unbanning a user
 * @param {Object} user - User object containing user details
 */
function confirmUnban(user) {
    document.getElementById('userActionModalLabel').textContent = 'Confirm Unban';
    document.getElementById('userActionMessage').innerHTML = `Are you sure you want to unban <strong>${user.first_name} ${user.last_name}</strong>?`;
    document.getElementById('userActionType').value = 'unban';
    document.getElementById('userActionId').value = user.id;
    document.getElementById('userActionButton').className = 'btn btn-success';
    document.getElementById('userActionButton').textContent = 'Unban User';
    
    const modal = new bootstrap.Modal(document.getElementById('userActionModal'));
    modal.show();
}
</script>

<?php require_once 'views/layout/footer.php'; ?>
