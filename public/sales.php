<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/config/Helper.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Sale.php';
require_once __DIR__ . '/../app/models/Product.php';

$auth = new AuthController();
$auth->checkSessionTimeout();
$auth->requireLogin();

$saleModel = new Sale();
$productModel = new Product();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $customerData = [
            'name' => Helper::sanitize($_POST['customer_name'] ?? ''),
            'phone' => Helper::sanitize($_POST['customer_phone'] ?? ''),
            'email' => Helper::sanitize($_POST['customer_email'] ?? '')
        ];
        $saleDate = Helper::sanitize($_POST['sale_date'] ?? date('Y-m-d'));
        $amount = floatval($_POST['amount'] ?? 0);

        $result = $saleModel->create($customerData, $saleDate, $amount);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'additem' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $saleId = intval($_POST['sale_id'] ?? 0);
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);
        $unitPrice = floatval($_POST['unit_price'] ?? 0);

        $result = $saleModel->addItem($saleId, $productId, $quantity, $unitPrice);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'deliver' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $saleId = intval($_POST['sale_id'] ?? 0);
        $deliveryDate = Helper::sanitize($_POST['delivery_date'] ?? date('Y-m-d'));

        $result = $saleModel->deliverSale($saleId, $deliveryDate);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'update_payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $saleId = intval($_POST['sale_id'] ?? 0);
        $status = Helper::sanitize($_POST['payment_status'] ?? 'Unpaid');

        $result = $saleModel->updatePaymentStatus($saleId, $status);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'update_amount' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $saleId = intval($_POST['sale_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);

        $result = $saleModel->updateAmount($saleId, $amount);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'cancel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $saleId = intval($_POST['sale_id'] ?? 0);
        $result = $saleModel->cancelSale($saleId);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'getitems' && isset($_GET['sale_id'])) {
        $saleId = intval($_GET['sale_id']);
        $items = $saleModel->getItems($saleId);
        echo json_encode($items);
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

$sales = $saleModel->getAll($limit, $offset, $filters);
$products = $productModel->getAll();

$pageTitle = 'Sales';
?>

<?php include __DIR__ . '/../views/layouts/header.php'; ?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h2"><i class="fas fa-shopping-cart"></i> Sales Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSaleModal">
            <i class="fas fa-plus"></i> New Sale
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
                    <option value="Delivered" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
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

<!-- Sales Table -->
<div class="card shadow">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Sale Date</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($sales) > 0): ?>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><strong><?php echo $sale['invoice_number']; ?></strong></td>
                            <td><?php echo $sale['customer_name']; ?></td>
                            <td><?php echo Helper::formatDate($sale['sale_date']); ?></td>
                            <td class="update-amount" 
                                style="cursor: pointer;" 
                                data-id="<?php echo $sale['id']; ?>" 
                                data-amount="<?php echo $sale['total_amount']; ?>"
                                title="Click to edit base amount">
                                <?php echo Helper::formatCurrency($sale['net_amount']); ?>
                                <i class="fas fa-edit fa-xs text-muted ms-1"></i>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $sale['payment_status'] === 'Paid' ? 'success' : 
                                         ($sale['payment_status'] === 'Unpaid' ? 'danger' : 'warning'); 
                                ?> update-payment" 
                                      style="cursor: pointer;"
                                      data-id="<?php echo $sale['id']; ?>"
                                      data-status="<?php echo $sale['payment_status']; ?>"
                                      title="Click to toggle payment status">
                                    <?php echo $sale['payment_status']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $sale['status'] === 'Pending' ? 'warning' : 
                                         ($sale['status'] === 'Delivered' ? 'success' : 'danger'); 
                                ?>">
                                    <?php echo $sale['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info view-sale" 
                                        data-id="<?php echo $sale['id']; ?>"
                                        data-bs-toggle="modal" data-bs-target="#viewSaleModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($sale['status'] === 'Pending'): ?>
                                    <button class="btn btn-sm btn-success deliver-sale" 
                                            data-id="<?php echo $sale['id']; ?>">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger cancel-sale" 
                                            data-id="<?php echo $sale['id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox text-muted"></i> No sales found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Sale Modal -->
<div class="modal fade" id="addSaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Create Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSaleForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sale Date *</label>
                                <input type="date" class="form-control" name="sale_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="customer_email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="customer_phone">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Sale Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" name="amount" required>
                                </div>
                                <small class="text-muted">Enter the base amount. 10% tax will be added automatically.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Sale Modal -->
<div class="modal fade" id="viewSaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sale Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="saleDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('addSaleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo APP_URL; ?>/public/sales.php?action=create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Sale created. Invoice: ' + data.invoice_number);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
});

document.querySelectorAll('.deliver-sale').forEach(btn => {
    btn.addEventListener('click', function() {
        const saleId = this.dataset.id;
        const deliveryDate = prompt('Enter delivery date (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
        if (deliveryDate) {
            fetch('<?php echo APP_URL; ?>/public/sales.php?action=deliver', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'sale_id=' + saleId + '&delivery_date=' + deliveryDate
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sale marked as delivered');
                    location.reload();
                }
            });
        }
    });
});

document.querySelectorAll('.cancel-sale').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Are you sure? Stock will be restored.')) {
            const saleId = this.dataset.id;
            fetch('<?php echo APP_URL; ?>/public/sales.php?action=cancel', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'sale_id=' + saleId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                }
            });
        }
    });
});

document.querySelectorAll('.update-payment').forEach(badge => {
    badge.addEventListener('click', function() {
        const saleId = this.dataset.id;
        const currentStatus = this.dataset.status;
        const newStatus = currentStatus === 'Paid' ? 'Unpaid' : 'Paid';
        
        if (confirm('Change payment status to ' + newStatus + '?')) {
            fetch('<?php echo APP_URL; ?>/public/sales.php?action=update_payment', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'sale_id=' + saleId + '&payment_status=' + newStatus
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating payment status');
                }
            });
        }
    });
});

document.querySelectorAll('.update-amount').forEach(cell => {
    cell.addEventListener('click', function() {
        const saleId = this.dataset.id;
        const currentAmount = this.dataset.amount;
        const newAmount = prompt('Enter new base amount (tax will be added):', currentAmount);
        
        if (newAmount !== null && newAmount !== '') {
            fetch('<?php echo APP_URL; ?>/public/sales.php?action=update_amount', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'sale_id=' + saleId + '&amount=' + newAmount
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating amount');
                }
            });
        }
    });
});
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
