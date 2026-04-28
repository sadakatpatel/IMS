<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/config/Helper.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Category.php';

$auth = new AuthController();
$auth->checkSessionTimeout();
$auth->requireLogin();

$productModel = new Product();
$categoryModel = new Category();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'delete' && isset($_POST['id'])) {
        $result = $productModel->delete($_POST['id']);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'name' => Helper::sanitize($_POST['name'] ?? ''),
            'category_id' => intval($_POST['category_id'] ?? 1),
            'sku' => Helper::sanitize($_POST['sku'] ?? ''),
            'description' => Helper::sanitize($_POST['description'] ?? ''),
            'purchase_price' => floatval($_POST['purchase_price'] ?? 0),
            'selling_price' => floatval($_POST['selling_price'] ?? 0),
            'quantity' => intval($_POST['quantity'] ?? 0),
            'reorder_level' => intval($_POST['reorder_level'] ?? 10),
            'barcode' => Helper::sanitize($_POST['barcode'] ?? '')
        ];

        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = Helper::uploadFile($_FILES['image']);
            if ($uploadResult['success']) {
                $data['image_path'] = $uploadResult['filename'];
            }
        }

        $result = $productModel->create($data);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'name' => Helper::sanitize($_POST['name'] ?? ''),
            'category_id' => intval($_POST['category_id'] ?? 1),
            'description' => Helper::sanitize($_POST['description'] ?? ''),
            'purchase_price' => floatval($_POST['purchase_price'] ?? 0),
            'selling_price' => floatval($_POST['selling_price'] ?? 0),
            'reorder_level' => intval($_POST['reorder_level'] ?? 10),
            'barcode' => Helper::sanitize($_POST['barcode'] ?? '')
        ];

        $result = $productModel->update($_POST['id'], $data);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'get' && isset($_GET['id'])) {
        $product = $productModel->getById($_GET['id']);
        echo json_encode($product);
        exit;
    }
}

// Get pagination
$page = intval($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Get filters
$filters = [];
if (isset($_GET['category'])) {
    $filters['category_id'] = intval($_GET['category']);
}
if (isset($_GET['search'])) {
    $filters['search'] = Helper::sanitize($_GET['search']);
}

$products = $productModel->getAll($limit, $offset, $filters);
$categories = $categoryModel->getAll();

$pageTitle = 'Products';
?>

<?php include __DIR__ . '/../views/layouts/header.php'; ?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h2"><i class="fas fa-box"></i> Products Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus"></i> Add Product
        </button>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4 shadow">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-6">
                <input type="text" class="form-control" name="search" 
                       placeholder="Search by product name or SKU..." 
                       value="<?php echo $_GET['search'] ?? ''; ?>">
            </div>
            <div class="col-md-4">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card shadow">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <strong><?php echo $product['name']; ?></strong>
                            </td>
                            <td><code><?php echo $product['sku']; ?></code></td>
                            <td><?php echo $product['category_name']; ?></td>
                            <td><?php echo Helper::formatCurrency($product['selling_price']); ?></td>
                            <td>
                                <?php 
                                $stockClass = 'success';
                                if ($product['quantity'] <= $product['reorder_level']) {
                                    $stockClass = 'danger';
                                } elseif ($product['quantity'] <= $product['reorder_level'] * 1.5) {
                                    $stockClass = 'warning';
                                }
                                ?>
                                <span class="badge bg-<?php echo $stockClass; ?>">
                                    <?php echo $product['quantity']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $product['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $product['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info edit-product" 
                                        data-id="<?php echo $product['id']; ?>"
                                        data-bs-toggle="modal" data-bs-target="#editProductModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-product" 
                                        data-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox text-muted"></i> No products found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category_id" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">SKU *</label>
                                <input type="text" class="form-control" name="sku" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Barcode</label>
                                <input type="text" class="form-control" name="barcode">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Purchase Price *</label>
                                <input type="number" class="form-control" name="purchase_price" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Selling Price *</label>
                                <input type="number" class="form-control" name="selling_price" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Initial Quantity *</label>
                                <input type="number" class="form-control" name="quantity" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Reorder Level *</label>
                                <input type="number" class="form-control" name="reorder_level" value="10" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Product
                    </button>
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
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProductForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editProductId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" class="form-control" name="name" id="editName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category_id" id="editCategoryId" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Barcode</label>
                                <input type="text" class="form-control" name="barcode" id="editBarcode">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Purchase Price *</label>
                                <input type="number" class="form-control" name="purchase_price" id="editPurchasePrice" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Selling Price *</label>
                                <input type="number" class="form-control" name="selling_price" id="editSellingPrice" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Reorder Level *</label>
                                <input type="number" class="form-control" name="reorder_level" id="editReorderLevel" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo APP_URL; ?>/public/products.php?action=create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});

document.querySelectorAll('.delete-product').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this product?')) {
            const id = this.dataset.id;
            fetch('<?php echo APP_URL; ?>/public/products.php?action=delete', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    });
});

document.querySelectorAll('.edit-product').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        document.getElementById('editProductId').value = id;
        
        fetch('<?php echo APP_URL; ?>/public/products.php?action=get&id=' + id)
        .then(response => response.json())
        .then(product => {
            document.getElementById('editName').value = product.name || '';
            document.getElementById('editCategoryId').value = product.category_id || 1;
            document.getElementById('editBarcode').value = product.barcode || '';
            document.getElementById('editDescription').value = product.description || '';
            document.getElementById('editPurchasePrice').value = product.purchase_price || 0;
            document.getElementById('editSellingPrice').value = product.selling_price || 0;
            document.getElementById('editReorderLevel').value = product.reorder_level || 10;
        });
    });
});

document.getElementById('editProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('id', document.getElementById('editProductId').value);
    
    fetch('<?php echo APP_URL; ?>/public/products.php?action=update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product updated successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
