<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
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
                    <a href="<?php echo BASE_URL; ?>/admin/users" class="list-group-item list-group-item-action">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/settings" class="list-group-item list-group-item-action active">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-10">
            <h1 class="fw-bold mb-4">Website Settings</h1>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Customize Website</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="site_logo" class="form-label">Site Logo</label>
                            <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
                            <div class="form-text">Upload your logo image (recommended size: 200x50px)</div>
                            <?php if (getSetting('site_logo')): ?>
                                <div class="mt-2">
                                    <small>Current logo:</small><br>
                                    <img src="<?php echo getSetting('site_logo'); ?>" alt="Current Logo" style="max-height: 50px;" class="mt-1">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="primary_color" class="form-label">Primary Color</label>
                                <input type="color" class="form-control form-control-color" id="primary_color" 
                                       name="primary_color" value="<?php echo getSetting('primary_color', '#FF8C00'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="secondary_color" class="form-label">Secondary Color</label>
                                <input type="color" class="form-control form-control-color" id="secondary_color" 
                                       name="secondary_color" value="<?php echo getSetting('secondary_color', '#FFD700'); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Preview Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Preview</h5>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3 mb-4">
                        <h6 class="mb-3">Navbar Preview</h6>
                        <nav class="navbar navbar-light bg-white">
                            <div class="container-fluid">
                                <a class="navbar-brand" href="#">
                                    <?php if (getSetting('site_logo')): ?>
                                        <img src="<?php echo getSetting('site_logo'); ?>" alt="Logo Preview" class="logo-img">
                                    <?php else: ?>
                                        <div class="text-muted">No logo uploaded</div>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </nav>
                    </div>
                    
                    <div class="text-center">
                        <div class="d-inline-block p-3 rounded" style="background: linear-gradient(135deg, <?php echo getSetting('primary_color'); ?>, <?php echo getSetting('secondary_color'); ?>);">
                            <span class="text-white fw-bold">Color Preview</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
