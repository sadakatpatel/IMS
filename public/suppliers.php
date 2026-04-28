<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/config/Helper.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Supplier.php';

$auth = new AuthController();
$auth->checkSessionTimeout();
$auth->requireLogin();

$supplierModel = new Supplier();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'delete' && isset($_POST['id'])) {
        $result = $supplierModel->delete($_POST['id']);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'name' => Helper::sanitize($_POST['name'] ?? ''),
            'phone' => Helper::sanitize($_POST['phone'] ?? ''),
            'email' => Helper::sanitize($_POST['email'] ?? ''),
            'address' => Helper::sanitize($_POST['address'] ?? ''),
            'city' => Helper::sanitize($_POST['city'] ?? ''),
            'state' => Helper::sanitize($_POST['state'] ?? ''),
            'zip_code' => Helper::sanitize($_POST['zip_code'] ?? ''),
            'contact_person' => Helper::sanitize($_POST['contact_person'] ?? ''),
            'payment_terms' => Helper::sanitize($_POST['payment_terms'] ?? '')
        ];

        $result = $supplierModel->create($data);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'name' => Helper::sanitize($_POST['name'] ?? ''),
            'phone' => Helper::sanitize($_POST['phone'] ?? ''),
            'email' => Helper::sanitize($_POST['email'] ?? ''),
            'address' => Helper::sanitize($_POST['address'] ?? ''),
            'city' => Helper::sanitize($_POST['city'] ?? ''),
            'state' => Helper::sanitize($_POST['state'] ?? ''),
            'zip_code' => Helper::sanitize($_POST['zip_code'] ?? ''),
            'contact_person' => Helper::sanitize($_POST['contact_person'] ?? ''),
            'payment_terms' => Helper::sanitize($_POST['payment_terms'] ?? '')
        ];

        $result = $supplierModel->update($_POST['id'], $data);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'get' && isset($_GET['id'])) {
        $supplier = $supplierModel->getById($_GET['id']);
        echo json_encode($supplier);
        exit;
    }
}

// Get pagination
$page = intval($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$suppliers = $supplierModel->getAll($limit, $offset);

$pageTitle = 'Suppliers';
?>

<?php include __DIR__ . '/../views/layouts/header.php'; ?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h2"><i class="fas fa-industry"></i> Suppliers Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="fas fa-plus"></i> Add Supplier
        </button>
    </div>
</div>

<!-- Suppliers Table -->
<div class="card shadow">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Supplier Name</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($suppliers) > 0): ?>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><strong><?php echo $supplier['name']; ?></strong></td>
                            <td><?php echo $supplier['contact_person']; ?></td>
                            <td><a href="mailto:<?php echo $supplier['email']; ?>"><?php echo $supplier['email']; ?></a></td>
                            <td><a href="tel:<?php echo $supplier['phone']; ?>"><?php echo $supplier['phone']; ?></a></td>
                            <td><?php echo $supplier['city']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $supplier['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $supplier['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info edit-supplier" 
                                        data-id="<?php echo $supplier['id']; ?>"
                                        data-bs-toggle="modal" data-bs-target="#editSupplierModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-supplier" 
                                        data-id="<?php echo $supplier['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox text-muted"></i> No suppliers found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSupplierForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Supplier Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" name="contact_person">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone *</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address *</label>
                        <input type="text" class="form-control" name="address" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="state">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Zip Code</label>
                                <input type="text" class="form-control" name="zip_code">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Terms</label>
                        <input type="text" class="form-control" name="payment_terms" placeholder="e.g., Net 30">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSupplierForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editSupplierId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Supplier Name *</label>
                                <input type="text" class="form-control" name="name" id="editName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" name="contact_person" id="editContactPerson">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" id="editEmail" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone *</label>
                                <input type="tel" class="form-control" name="phone" id="editPhone" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address *</label>
                        <input type="text" class="form-control" name="address" id="editAddress" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" id="editCity">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="state" id="editState">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Zip Code</label>
                                <input type="text" class="form-control" name="zip_code" id="editZipCode">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Terms</label>
                        <input type="text" class="form-control" name="payment_terms" id="editPaymentTerms" placeholder="e.g., Net 30">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('addSupplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo APP_URL; ?>/public/suppliers.php?action=create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Supplier added successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});

document.querySelectorAll('.delete-supplier').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this supplier?')) {
            const id = this.dataset.id;
            fetch('<?php echo APP_URL; ?>/public/suppliers.php?action=delete', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Supplier deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    });
});

document.querySelectorAll('.edit-supplier').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        document.getElementById('editSupplierId').value = id;
        
        fetch('<?php echo APP_URL; ?>/public/suppliers.php?action=get&id=' + id)
        .then(response => response.json())
        .then(supplier => {
            document.getElementById('editName').value = supplier.name || '';
            document.getElementById('editContactPerson').value = supplier.contact_person || '';
            document.getElementById('editEmail').value = supplier.email || '';
            document.getElementById('editPhone').value = supplier.phone || '';
            document.getElementById('editAddress').value = supplier.address || '';
            document.getElementById('editCity').value = supplier.city || '';
            document.getElementById('editState').value = supplier.state || '';
            document.getElementById('editZipCode').value = supplier.zip_code || '';
            document.getElementById('editPaymentTerms').value = supplier.payment_terms || '';
        });
    });
});

document.getElementById('editSupplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('id', document.getElementById('editSupplierId').value);
    
    fetch('<?php echo APP_URL; ?>/public/suppliers.php?action=update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Supplier updated successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
