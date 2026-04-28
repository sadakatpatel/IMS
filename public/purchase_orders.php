<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/config/Helper.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/PurchaseOrder.php';
require_once __DIR__ . '/../app/models/Supplier.php';
require_once __DIR__ . '/../app/models/Product.php';

$auth = new AuthController();
$auth->checkSessionTimeout();
$auth->requireLogin();

$poModel = new PurchaseOrder();
$supplierModel = new Supplier();
$productModel = new Product();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $supplierId = intval($_POST['supplier_id'] ?? 0);
        $orderDate = Helper::sanitize($_POST['order_date'] ?? date('Y-m-d'));
        $notes = Helper::sanitize($_POST['notes'] ?? '');

        $result = $poModel->create($supplierId, $orderDate, $notes);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'additem' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $orderId = intval($_POST['order_id'] ?? 0);
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);
        $unitPrice = floatval($_POST['unit_price'] ?? 0);

        $result = $poModel->addItem($orderId, $productId, $quantity, $unitPrice);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'complete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $orderId = intval($_POST['order_id'] ?? 0);
        $deliveryDate = Helper::sanitize($_POST['delivery_date'] ?? date('Y-m-d'));

        $result = $poModel->completeOrder($orderId, $deliveryDate);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'cancel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $orderId = intval($_POST['order_id'] ?? 0);
        $result = $poModel->cancelOrder($orderId);
        echo json_encode($result);
        exit;
    }
}

// Get pagination
$page = intval($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Get filters
$filters = [];
if (isset($_GET['status'])) {
    $filters['status'] = Helper::sanitize($_GET['status']);
}

$orders = $poModel->getAll($limit, $offset, $filters);
$suppliers = $supplierModel->getAll();
$products = $productModel->getAll();

$pageTitle = 'Purchase Orders';
?>

<?php include __DIR__ . '/../views/layouts/header.php'; ?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h2"><i class="fas fa-receipt"></i> Purchase Orders</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPOModal">
            <i class="fas fa-plus"></i> New Purchase Order
        </button>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4 shadow">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Completed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
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

<!-- Purchase Orders Table -->
<div class="card shadow">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order Number</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?php echo $order['order_number']; ?></strong></td>
                            <td><?php echo $order['supplier_name']; ?></td>
                            <td><?php echo Helper::formatDate($order['order_date']); ?></td>
                            <td><?php echo Helper::formatCurrency($order['total_amount']); ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo $order['item_count']; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $order['status'] === 'Pending' ? 'warning' : 
                                         ($order['status'] === 'Completed' ? 'success' : 'danger'); 
                                ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info view-po" 
                                        data-id="<?php echo $order['id']; ?>"
                                        data-bs-toggle="modal" data-bs-target="#viewPOModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($order['status'] === 'Pending'): ?>
                                    <button class="btn btn-sm btn-success complete-po" 
                                            data-id="<?php echo $order['id']; ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger cancel-po" 
                                            data-id="<?php echo $order['id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox text-muted"></i> No purchase orders found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add PO Modal -->
<div class="modal fade" id="addPOModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Create Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPOForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Supplier *</label>
                                <select class="form-select" name="supplier_id" required>
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?php echo $supplier['id']; ?>"><?php echo $supplier['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Order Date *</label>
                                <input type="date" class="form-control" name="order_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View PO Modal -->
<div class="modal fade" id="viewPOModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Purchase Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="poDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('addPOForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo APP_URL; ?>/public/purchase_orders.php?action=create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Purchase order created. Order ID: ' + data.id);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
});

document.querySelectorAll('.complete-po').forEach(btn => {
    btn.addEventListener('click', function() {
        const orderId = this.dataset.id;
        const deliveryDate = prompt('Enter delivery date (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
        if (deliveryDate) {
            fetch('<?php echo APP_URL; ?>/public/purchase_orders.php?action=complete', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'order_id=' + orderId + '&delivery_date=' + deliveryDate
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    });
});

document.querySelectorAll('.cancel-po').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Are you sure you want to cancel this order?')) {
            const orderId = this.dataset.id;
            fetch('<?php echo APP_URL; ?>/public/purchase_orders.php?action=cancel', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'order_id=' + orderId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order cancelled');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    });
});
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
